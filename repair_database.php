<?php
/**
 * ECOMSHOOT - Safe Production Database Repair & Migration Tool
 * Non-Destructive: Zero DROP, Zero TRUNCATE, Pure Additive (IF NOT EXISTS & INSERT IGNORE)
 */

require_once __DIR__ . '/config/config.php';

$isCli = (php_sapi_name() === 'cli');
$action = $_GET['action'] ?? ($_POST['action'] ?? ($isCli ? 'run' : 'preview'));

// Initialize results tracking
$results = [
    'env' => $isLocalDev ? 'Local Development' : 'Hostinger Production',
    'db_name' => DB_NAME,
    'db_host' => DB_HOST,
    'db_user' => DB_USER,
    'existing_tables' => [],
    'backup_file' => null,
    'tables_created' => [],
    'columns_verified' => [],
    'services_restored' => 0,
    'total_services' => 0,
    'test_queries' => [],
    'errors' => []
];

try {
    $db = Database::getInstance()->getConnection();
    $results['existing_tables'] = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $results['errors'][] = "Database Connection Failed: " . $e->getMessage();
}

// Handler for executing repair
if ($action === 'run' && empty($results['errors'])) {
    try {
        // 1. Take Pre-Migration Backup if any tables exist
        if (!empty($results['existing_tables'])) {
            $backupDir = UPLOADS_PATH . '/backups';
            if (!is_dir($backupDir)) {
                @mkdir($backupDir, 0755, true);
            }
            $backupFile = $backupDir . '/db_backup_pre_repair_' . date('Ymd_His') . '.sql';
            $dumpContent = "-- ECOMSHOOT PRE-REPAIR DATABASE BACKUP\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Database: " . DB_NAME . "\n\n";
            $dumpContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($results['existing_tables'] as $tbl) {
                $createTbl = $db->query("SHOW CREATE TABLE `$tbl`")->fetch(PDO::FETCH_ASSOC)['Create Table'] ?? '';
                $dumpContent .= "DROP TABLE IF EXISTS `$tbl`;\n" . $createTbl . ";\n\n";
                
                $rows = $db->query("SELECT * FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $cols = array_keys($rows[0]);
                    $quotedCols = array_map(function($c) { return "`$c`"; }, $cols);
                    $dumpContent .= "INSERT INTO `$tbl` (" . implode(', ', $quotedCols) . ") VALUES\n";
                    $valLines = [];
                    foreach ($rows as $r) {
                        $vArr = [];
                        foreach ($r as $val) {
                            $vArr[] = ($val === null) ? 'NULL' : $db->quote($val);
                        }
                        $valLines[] = "(" . implode(', ', $vArr) . ")";
                    }
                    $dumpContent .= implode(",\n", $valLines) . ";\n\n";
                }
            }
            $dumpContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            file_put_contents($backupFile, $dumpContent);
            $results['backup_file'] = $backupFile;
        }

        // 2. Load and Execute Safe Migration SQL with Robust SQL Tokenizer
        $sqlPath = __DIR__ . '/database/production_safe_migration.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("Migration file not found at: database/production_safe_migration.sql");
        }

        $sql = file_get_contents($sqlPath);
        
        // Accurate string-aware statement splitter
        $queries = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);
        
        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            
            // Skip comments outside strings
            if (!$inString) {
                if ($char === '-' && ($sql[$i + 1] ?? '') === '-') {
                    while ($i < $len && $sql[$i] !== "\n") { $i++; }
                    continue;
                }
                if ($char === '/' && ($sql[$i + 1] ?? '') === '*') {
                    $i += 2;
                    while ($i < $len && !($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) { $i++; }
                    $i++;
                    continue;
                }
            }
            
            if ($inString) {
                if ($char === '\\') {
                    $current .= $char . ($sql[++$i] ?? '');
                    continue;
                }
                if ($char === $stringChar) {
                    if (($sql[$i + 1] ?? '') === $stringChar) {
                        $current .= $char . $sql[++$i];
                        continue;
                    }
                    $inString = false;
                }
                $current .= $char;
            } else {
                if ($char === "'" || $char === '"') {
                    $inString = true;
                    $stringChar = $char;
                    $current .= $char;
                } elseif ($char === ';') {
                    $trimmed = trim($current);
                    if (!empty($trimmed)) {
                        $queries[] = $trimmed;
                    }
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
        }
        $trimmed = trim($current);
        if (!empty($trimmed)) {
            $queries[] = $trimmed;
        }
        
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($queries as $stmt) {
            if (!empty($stmt)) {
                try {
                    $db->exec($stmt);
                } catch (PDOException $se) {
                    if (!str_contains($se->getMessage(), 'Duplicate column') && 
                        !str_contains($se->getMessage(), 'already exists')) {
                        $results['errors'][] = "Notice: " . substr($stmt, 0, 60) . "... (" . $se->getMessage() . ")";
                    }
                }
            }
        }
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");

        // 3. Verify Tables After Migration
        $tablesAfter = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $results['tables_created'] = $tablesAfter;

        // 4. Verify Services Table
        $srvStmt = $db->query("SELECT * FROM services WHERE status = 'published' ORDER BY display_order ASC");
        $services = $srvStmt->fetchAll(PDO::FETCH_ASSOC);
        $results['total_services'] = count($services);

        // 5. Verification Test Queries
        $results['test_queries']['services_query'] = [
            'name' => 'Home & Services query (SELECT * FROM services WHERE status = \'published\' ORDER BY display_order ASC)',
            'status' => count($services) >= 9 ? 'PASS' : 'WARN',
            'details' => count($services) . ' published services retrieved'
        ];

        $blogsCount = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
        $results['test_queries']['blogs_query'] = [
            'name' => 'Blogs query (SELECT COUNT(*) FROM blogs)',
            'status' => 'PASS',
            'details' => $blogsCount . ' published blogs found'
        ];

        $pagesCount = $db->query("SELECT COUNT(*) FROM pages")->fetchColumn();
        $results['test_queries']['pages_query'] = [
            'name' => 'Pages query (SELECT COUNT(*) FROM pages)',
            'status' => 'PASS',
            'details' => $pagesCount . ' static pages registered'
        ];

        $leadsCount = $db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
        $results['test_queries']['leads_query'] = [
            'name' => 'Leads query (SELECT COUNT(*) FROM leads)',
            'status' => 'PASS',
            'details' => $leadsCount . ' leads records tracked'
        ];

        $contactCount = $db->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
        $results['test_queries']['contact_query'] = [
            'name' => 'Contact submissions query (SELECT COUNT(*) FROM contact_submissions)',
            'status' => 'PASS',
            'details' => $contactCount . ' contact inquiries stored'
        ];

        $settingsCount = $db->query("SELECT COUNT(*) FROM website_settings")->fetchColumn();
        $results['test_queries']['settings_query'] = [
            'name' => 'Settings query (SELECT COUNT(*) FROM website_settings)',
            'status' => 'PASS',
            'details' => $settingsCount . ' configuration settings loaded'
        ];

        $adminsCount = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        $results['test_queries']['admins_query'] = [
            'name' => 'Admin users query (SELECT COUNT(*) FROM admins)',
            'status' => 'PASS',
            'details' => $adminsCount . ' admin accounts verified'
        ];

    } catch (Exception $e) {
        $results['errors'][] = "Migration Error: " . $e->getMessage();
    }
}

// If running in CLI, print readable plain text report and exit
if ($isCli) {
    echo "====================================================================\n";
    echo "ECOMSHOOT - DATABASE REPAIR & MIGRATION RESULT\n";
    echo "====================================================================\n";
    echo "Environment: " . $results['env'] . "\n";
    echo "Database:    " . $results['db_name'] . " on " . $results['db_host'] . "\n";
    echo "User:        " . $results['db_user'] . "\n\n";

    if (!empty($results['backup_file'])) {
        echo "[BACKUP] Created safe pre-migration backup:\n  " . $results['backup_file'] . "\n\n";
    }

    echo "--- TABLES VERIFIED (" . count($results['tables_created']) . ") ---\n";
    foreach ($results['tables_created'] as $t) {
        echo "  [OK] Table: $t\n";
    }

    echo "\n--- TEST QUERIES ---\n";
    foreach ($results['test_queries'] as $tq) {
        echo "  [" . $tq['status'] . "] " . $tq['name'] . "\n      -> " . $tq['details'] . "\n";
    }

    if (!empty($results['errors'])) {
        echo "\n--- NOTICES / WARNINGS ---\n";
        foreach ($results['errors'] as $err) {
            echo "  ! " . $err . "\n";
        }
    }
    echo "\nTotal Published Services: " . $results['total_services'] . " / 9\n";
    echo "====================================================================\n";
    exit(empty($results['errors']) ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ecomshoot - Production Database Repair & Migration</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0D1117;
      --card-bg: #161B22;
      --border: #30363D;
      --text: #C9D1D9;
      --heading: #FFFFFF;
      --accent: #E85D04;
      --success: #2EA043;
      --warning: #D29922;
      --danger: #F85149;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', -apple-system, sans-serif;
      line-height: 1.6;
      padding: 2.5rem 1rem;
    }
    .container {
      max-width: 880px;
      margin: 0 auto;
    }
    .header-box {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
    }
    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      margin-bottom: 0.75rem;
    }
    .badge-env {
      background: rgba(232, 93, 4, 0.15);
      color: var(--accent);
      border: 1px solid rgba(232, 93, 4, 0.3);
    }
    .badge-success {
      background: rgba(46, 160, 67, 0.15);
      color: var(--success);
      border: 1px solid rgba(46, 160, 67, 0.3);
    }
    h1 {
      font-family: 'Space Grotesk', sans-serif;
      color: var(--heading);
      font-size: 1.85rem;
      margin-bottom: 0.5rem;
    }
    .meta-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border);
    }
    .meta-item {
      font-size: 0.85rem;
    }
    .meta-label {
      color: #8B949E;
      text-transform: uppercase;
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.05em;
    }
    .meta-val {
      color: var(--heading);
      font-weight: 600;
      font-family: monospace;
      font-size: 0.95rem;
    }
    .card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.75rem;
      margin-bottom: 1.5rem;
    }
    .card h2 {
      font-family: 'Space Grotesk', sans-serif;
      color: var(--heading);
      font-size: 1.25rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .table-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 0.75rem;
    }
    .table-pill {
      background: #21262D;
      border: 1px solid var(--border);
      padding: 0.35rem 0.85rem;
      border-radius: 6px;
      font-size: 0.85rem;
      font-family: monospace;
      color: #7EE787;
    }
    .query-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid rgba(48, 54, 61, 0.5);
      font-size: 0.9rem;
    }
    .query-item:last-child { border-bottom: none; }
    .status-badge {
      padding: 0.2rem 0.6rem;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 700;
    }
    .status-pass { background: rgba(46, 160, 67, 0.2); color: #7EE787; }
    .status-warn { background: rgba(210, 153, 34, 0.2); color: #E3B341; }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.85rem 1.75rem;
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      border-radius: 8px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-primary {
      background: var(--accent);
      color: #FFFFFF;
    }
    .btn-primary:hover {
      background: #D04F00;
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: #21262D;
      color: #FFFFFF;
      border: 1px solid var(--border);
    }
    .btn-secondary:hover {
      background: #30363D;
    }
    .actions-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    .notice-box {
      background: rgba(232, 93, 4, 0.1);
      border: 1px solid rgba(232, 93, 4, 0.3);
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-box">
      <span class="badge badge-env"><?= htmlspecialchars($results['env']) ?></span>
      <h1>Production Database Repair &amp; Migration</h1>
      <p>Additive, non-destructive migration system for Ecomshoot. Automatically creates missing tables, preserves existing records, and verifies all 9 commercial services.</p>
      
      <div class="meta-grid">
        <div class="meta-item">
          <div class="meta-label">Target Database</div>
          <div class="meta-val"><?= htmlspecialchars($results['db_name']) ?></div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Database Host</div>
          <div class="meta-val"><?= htmlspecialchars($results['db_host']) ?></div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Database User</div>
          <div class="meta-val"><?= htmlspecialchars($results['db_user']) ?></div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Existing Tables</div>
          <div class="meta-val"><?= count($results['existing_tables']) ?> found</div>
        </div>
      </div>
    </div>

    <?php if ($action !== 'run'): ?>
      <div class="card">
        <h2>⚠️ Ready to Execute Safe Repair</h2>
        <div class="notice-box">
          <strong>Safe &amp; Additive Operation:</strong>
          <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
            <li>Zero <code>DROP</code> statements &bull; Zero <code>TRUNCATE</code> statements</li>
            <li>All tables use <code>CREATE TABLE IF NOT EXISTS</code></li>
            <li>All data imports use <code>INSERT IGNORE</code> &ndash; existing blog, admin, and lead records will NEVER be overwritten</li>
            <li>An automatic SQL backup file will be created in <code>uploads/backups/</code> before any changes</li>
          </ul>
        </div>
        <form method="POST" action="repair_database.php?action=run">
          <button type="submit" class="btn btn-primary">
            🚀 Execute Safe Database Repair Now
          </button>
        </form>
      </div>
    <?php else: ?>
      <?php if (!empty($results['backup_file'])): ?>
        <div class="card">
          <h2>💾 Pre-Migration Backup Created</h2>
          <p style="font-size: 0.9rem; color: #8B949E; margin-bottom: 0.5rem;">A full SQL dump of all pre-existing tables was taken before applying any changes:</p>
          <code style="background: #0D1117; padding: 0.5rem 0.75rem; border-radius: 6px; display: block; border: 1px solid var(--border); color: #7EE787; font-size: 0.85rem;">
            <?= htmlspecialchars($results['backup_file']) ?>
          </code>
        </div>
      <?php endif; ?>

      <div class="card">
        <h2>✅ Tables Verified &amp; Restored (<?= count($results['tables_created']) ?> Total)</h2>
        <div class="table-list">
          <?php foreach ($results['tables_created'] as $t): ?>
            <span class="table-pill">✓ <?= htmlspecialchars($t) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card">
        <h2>🔍 Production Verification Queries</h2>
        <div>
          <?php foreach ($results['test_queries'] as $tq): ?>
            <div class="query-item">
              <div>
                <strong style="color: var(--heading);"><?= htmlspecialchars($tq['name']) ?></strong>
                <div style="font-size: 0.8rem; color: #8B949E;"><?= htmlspecialchars($tq['details']) ?></div>
              </div>
              <span class="status-badge status-<?= strtolower($tq['status']) ?>"><?= $tq['status'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="actions-bar">
        <a href="index.php" class="btn btn-primary" target="_blank">🌐 Open Website Homepage &rarr;</a>
        <a href="services.php" class="btn btn-secondary" target="_blank">📋 Open Services Directory</a>
        <a href="admin/services.php" class="btn btn-secondary" target="_blank">⚙️ Admin Services CMS</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
