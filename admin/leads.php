<?php
/**
 * ECOMSHOOT - Admin Lead Management Dashboard
 * Tracks all automatic popup lead acquisitions with source page attribution
 */

$adminTitle = 'Popup Lead Management';
$activeNav = 'leads';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

// 1. Export Leads to CSV
if ((isset($_GET['action']) && $_GET['action'] === 'export') || isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ecomshoot_leads_' . date('Y-m-d_His') . '.csv');
    
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Full Name', 'Email', 'Mobile', 'Source Page Title', 'Source Page URL', 'Source Page Type', 'Lead Source', 'Status', 'Admin Notes', 'IP Address', 'Created At', 'Message']);
    
    $leadsExport = $db->query("SELECT * FROM `leads` ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($leadsExport as $row) {
        fputcsv($out, [
            $row['id'],
            $row['full_name'],
            $row['email'],
            $row['mobile'],
            $row['source_page_title'],
            $row['source_page_url'],
            $row['source_page_type'],
            $row['lead_source'],
            $row['status'],
            $row['admin_notes'],
            $row['ip_address'],
            $row['created_at'],
            $row['message']
        ]);
    }
    fclose($out);
    exit;
}

// 2. Handle Status Update / Admin Notes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_lead') {
    CSRF::check();
    $leadId = (int) ($_POST['lead_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'new');
    $notes = trim($_POST['admin_notes'] ?? '');

    $allowedStatuses = ['new', 'contacted', 'converted', 'closed'];
    if (!in_array($status, $allowedStatuses)) {
        $status = 'new';
    }

    $stmt = $db->prepare("UPDATE `leads` SET `status` = :st, `admin_notes` = :notes WHERE `id` = :id");
    $stmt->execute(['st' => $status, 'notes' => $notes, 'id' => $leadId]);

    set_flash('success', 'Lead #' . $leadId . ' updated successfully.');
    redirect('admin/leads.php' . (isset($_POST['view_id']) ? '?id=' . (int)$_POST['view_id'] : ''));
}

// 3. Handle Delete Lead
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    CSRF::check();
    $delId = (int) ($_GET['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM `leads` WHERE `id` = :id");
    $stmt->execute(['id' => $delId]);
    set_flash('success', 'Lead #' . $delId . ' deleted successfully.');
    redirect('admin/leads.php');
}

// 4. View Single Lead
$viewId = (int) ($_GET['id'] ?? 0);
$selectedLead = null;
if ($viewId > 0) {
    $vStmt = $db->prepare("SELECT * FROM `leads` WHERE `id` = :id LIMIT 1");
    $vStmt->execute(['id' => $viewId]);
    $selectedLead = $vStmt->fetch(PDO::FETCH_ASSOC);
}

// 5. Compute Metrics
$metrics = [
    'total' => (int) $db->query("SELECT COUNT(*) FROM `leads`")->fetchColumn(),
    'new' => (int) $db->query("SELECT COUNT(*) FROM `leads` WHERE status = 'new'")->fetchColumn(),
    'contacted' => (int) $db->query("SELECT COUNT(*) FROM `leads` WHERE status = 'contacted'")->fetchColumn(),
    'converted' => (int) $db->query("SELECT COUNT(*) FROM `leads` WHERE status = 'converted'")->fetchColumn(),
];

// 6. Build Filter Query
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');
$sourceFilter = trim($_GET['source_page'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$sql = "SELECT * FROM `leads` WHERE 1=1";
$params = [];

if ($statusFilter !== '' && $statusFilter !== 'all') {
    $sql .= " AND status = :st";
    $params['st'] = $statusFilter;
}

if ($search !== '') {
    $sql .= " AND (full_name LIKE :s1 OR email LIKE :s2 OR mobile LIKE :s3 OR message LIKE :s4)";
    $params['s1'] = "%{$search}%";
    $params['s2'] = "%{$search}%";
    $params['s3'] = "%{$search}%";
    $params['s4'] = "%{$search}%";
}

if ($sourceFilter !== '') {
    $sql .= " AND (source_page_title = :src OR source_page_url LIKE :src2)";
    $params['src'] = $sourceFilter;
    $params['src2'] = "%{$sourceFilter}%";
}

if ($dateFrom !== '') {
    $sql .= " AND created_at >= :dfrom";
    $params['dfrom'] = $dateFrom . ' 00:00:00';
}

if ($dateTo !== '') {
    $sql .= " AND created_at <= :dto";
    $params['dto'] = $dateTo . ' 23:59:59';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Distinct sources for dropdown filter
$distinctSources = $db->query("SELECT DISTINCT source_page_title FROM `leads` WHERE source_page_title IS NOT NULL AND source_page_title != '' ORDER BY source_page_title ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h2>Popup Lead Generation Inquiries</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Inquiries captured automatically by the timed popup modal across public pages.</div>
  </div>
  <div style="display: flex; gap: 0.75rem;">
    <a href="<?= url('admin/leads.php?action=export') ?>" class="btn-admin btn-admin-primary">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Export Leads to CSV
    </a>
  </div>
</div>

<!-- Metrics Dashboard -->
<div class="admin-metrics-grid">
  <div class="metric-card">
    <div class="metric-title">Total Leads</div>
    <div class="metric-value"><?= number_format($metrics['total']) ?></div>
  </div>
  <div class="metric-card" style="border-left: 4px solid var(--admin-coral);">
    <div class="metric-title" style="color: var(--admin-coral);">New Leads</div>
    <div class="metric-value" style="color: var(--admin-coral);"><?= number_format($metrics['new']) ?></div>
  </div>
  <div class="metric-card" style="border-left: 4px solid var(--admin-primary);">
    <div class="metric-title" style="color: var(--admin-primary);">Contacted Leads</div>
    <div class="metric-value" style="color: var(--admin-primary);"><?= number_format($metrics['contacted']) ?></div>
  </div>
  <div class="metric-card" style="border-left: 4px solid var(--admin-success);">
    <div class="metric-title" style="color: var(--admin-success);">Converted Shoots</div>
    <div class="metric-value" style="color: var(--admin-success);"><?= number_format($metrics['converted']) ?></div>
  </div>
</div>

<?php if ($selectedLead): ?>
  <!-- Lead Detail & Quick Actions Card -->
  <div class="admin-card" style="border: 2px solid var(--admin-primary); background-color: #FFFDF9;">
    <div class="admin-card-header">
      <div class="admin-card-title">
        Lead #<?= $selectedLead['id'] ?>: <?= e($selectedLead['full_name']) ?>
        <span class="status-pill status-<?= e($selectedLead['status']) ?>" style="margin-left: 0.75rem; vertical-align: middle;">
          <?= strtoupper(e($selectedLead['status'])) ?>
        </span>
      </div>
      <a href="<?= url('admin/leads.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">Close Details</a>
    </div>
    <div class="admin-card-body">
      
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; background: #FFFFFF; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--admin-border);">
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Email Address</div>
          <div style="font-weight: 600;"><a href="mailto:<?= e($selectedLead['email']) ?>"><?= e($selectedLead['email']) ?></a></div>
        </div>
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Mobile Number</div>
          <div style="font-weight: 600;"><a href="tel:<?= e($selectedLead['mobile']) ?>"><?= e($selectedLead['mobile']) ?></a></div>
        </div>
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Source Page</div>
          <div style="font-weight: 600;">
            <a href="<?= e($selectedLead['source_page_url'] ?: '#') ?>" target="_blank" style="color: var(--admin-primary);">
              <?= e($selectedLead['source_page_title'] ?: 'Landing Page') ?> &nearr;
            </a>
          </div>
          <div style="font-size: 0.7rem; color: var(--admin-text-muted);"><?= e($selectedLead['lead_source']) ?></div>
        </div>
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Capture Timestamp</div>
          <div style="font-weight: 600;"><?= date('M j, Y g:ia', strtotime($selectedLead['created_at'])) ?></div>
          <div style="font-size: 0.7rem; color: var(--admin-text-muted);">IP: <?= e($selectedLead['ip_address']) ?></div>
        </div>
      </div>

      <div style="margin-bottom: 1.5rem;">
        <label class="admin-form-label">Client Message &amp; Photography Scope:</label>
        <div style="background: #FFFFFF; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--admin-border); font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap;"><?= e($selectedLead['message']) ?></div>
      </div>

      <!-- Update Status & Internal Admin Notes Form -->
      <form action="<?= url('admin/leads.php') ?>" method="POST" style="background: #FFFFFF; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--admin-border);">
        <?= CSRF::inputField() ?>
        <input type="hidden" name="action" value="update_lead">
        <input type="hidden" name="lead_id" value="<?= $selectedLead['id'] ?>">
        <input type="hidden" name="view_id" value="<?= $selectedLead['id'] ?>">

        <div style="display: grid; grid-template-columns: 200px 1fr auto; gap: 1rem; align-items: end;">
          <div>
            <label class="admin-form-label">Lead Status</label>
            <select name="status" class="admin-form-control">
              <option value="new" <?= ($selectedLead['status'] === 'new') ? 'selected' : '' ?>>New / Uncontacted</option>
              <option value="contacted" <?= ($selectedLead['status'] === 'contacted') ? 'selected' : '' ?>>Contacted</option>
              <option value="converted" <?= ($selectedLead['status'] === 'converted') ? 'selected' : '' ?>>Converted (Shoot Booked)</option>
              <option value="closed" <?= ($selectedLead['status'] === 'closed') ? 'selected' : '' ?>>Closed / Archived</option>
            </select>
          </div>

          <div>
            <label class="admin-form-label">Internal Admin Notes</label>
            <input type="text" name="admin_notes" value="<?= e($selectedLead['admin_notes'] ?? '') ?>" class="admin-form-control" placeholder="e.g. Sent rate card on WhatsApp, client shipping 20 SKUs on Monday...">
          </div>

          <div>
            <button type="submit" class="btn-admin btn-admin-primary">
              Save Status &amp; Notes
            </button>
          </div>
        </div>
      </form>

    </div>
  </div>
<?php endif; ?>

<!-- Search & Filtering Bar -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
  <div class="admin-card-body" style="padding: 1.25rem;">
    <form action="<?= url('admin/leads.php') ?>" method="GET" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr auto; gap: 0.75rem; align-items: end;">
      <div>
        <label class="admin-form-label" style="font-size: 0.75rem;">Search Inquiries</label>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Name, email, mobile, message..." class="admin-form-control" style="font-size: 0.85rem;">
      </div>

      <div>
        <label class="admin-form-label" style="font-size: 0.75rem;">Filter by Status</label>
        <select name="status" class="admin-form-control" style="font-size: 0.85rem;">
          <option value="all">All Statuses</option>
          <option value="new" <?= ($statusFilter === 'new') ? 'selected' : '' ?>>New (<?= $metrics['new'] ?>)</option>
          <option value="contacted" <?= ($statusFilter === 'contacted') ? 'selected' : '' ?>>Contacted (<?= $metrics['contacted'] ?>)</option>
          <option value="converted" <?= ($statusFilter === 'converted') ? 'selected' : '' ?>>Converted (<?= $metrics['converted'] ?>)</option>
          <option value="closed" <?= ($statusFilter === 'closed') ? 'selected' : '' ?>>Closed</option>
        </select>
      </div>

      <div>
        <label class="admin-form-label" style="font-size: 0.75rem;">Source Page</label>
        <select name="source_page" class="admin-form-control" style="font-size: 0.85rem;">
          <option value="">All Source Pages</option>
          <?php foreach ($distinctSources as $src): ?>
            <option value="<?= e($src) ?>" <?= ($sourceFilter === $src) ? 'selected' : '' ?>><?= e(mb_strimwidth($src, 0, 30, '...')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="admin-form-label" style="font-size: 0.75rem;">Date From</label>
        <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="admin-form-control" style="font-size: 0.85rem;">
      </div>

      <div>
        <label class="admin-form-label" style="font-size: 0.75rem;">Date To</label>
        <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="admin-form-control" style="font-size: 0.85rem;">
      </div>

      <div style="display: flex; gap: 0.35rem;">
        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 0.65rem 1rem;">Filter</button>
        <?php if ($search || ($statusFilter && $statusFilter !== 'all') || $sourceFilter || $dateFrom || $dateTo): ?>
          <a href="<?= url('admin/leads.php') ?>" class="btn-admin btn-admin-outline" style="padding: 0.65rem 0.75rem;" title="Reset filters">✕</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- Leads Data Table -->
<div class="admin-card">
  <div class="admin-card-body" style="padding: 0;">
    <?php if (empty($leads)): ?>
      <div style="text-align: center; padding: 3.5rem 1rem; color: var(--admin-text-muted);">
        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom: 0.75rem; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <div style="font-weight: 600;">No leads matched your criteria.</div>
        <div style="font-size: 0.82rem; margin-top: 0.25rem;">Try clearing your search or status filter.</div>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 60px;">ID</th>
            <th>Lead Contact</th>
            <th>Source Page Attribution</th>
            <th>Scope Preview</th>
            <th>Status</th>
            <th>Received</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $l): ?>
            <tr style="<?= ($l['status'] === 'new') ? 'background-color: #FEF9F6;' : '' ?>">
              <td>
                <span style="font-weight: 700; color: var(--admin-text-muted);">#<?= $l['id'] ?></span>
              </td>
              <td>
                <div style="font-weight: 700; color: var(--admin-text-main); font-size: 0.95rem;">
                  <?= e($l['full_name']) ?>
                </div>
                <div style="font-size: 0.8rem;">
                  <a href="mailto:<?= e($l['email']) ?>" style="color: var(--admin-primary);"><?= e($l['email']) ?></a>
                </div>
                <div style="font-size: 0.8rem; color: var(--admin-text-muted);">
                  <a href="tel:<?= e($l['mobile']) ?>" style="color: inherit;"><?= e($l['mobile']) ?></a>
                </div>
              </td>
              <td>
                <div style="font-weight: 600; font-size: 0.88rem; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?php if (!empty($l['source_page_url'])): ?>
                    <a href="<?= e($l['source_page_url']) ?>" target="_blank" title="<?= e($l['source_page_title'] ?: $l['source_page_url']) ?>">
                      <?= e($l['source_page_title'] ?: $l['source_page_url']) ?> &nearr;
                    </a>
                  <?php else: ?>
                    <?= e($l['source_page_title'] ?: 'Direct Public Page') ?>
                  <?php endif; ?>
                </div>
                <div style="font-size: 0.72rem; color: var(--admin-coral); font-weight: 700; text-transform: uppercase;">
                  <?= e($l['lead_source'] ?: 'Timed Popup') ?>
                </div>
              </td>
              <td style="max-width: 260px;">
                <div style="font-size: 0.85rem; color: var(--admin-text-muted); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= e($l['message']) ?>
                </div>
                <?php if (!empty($l['admin_notes'])): ?>
                  <div style="font-size: 0.75rem; color: #0284C7; margin-top: 0.25rem;">
                    <strong>Note:</strong> <?= e($l['admin_notes']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <span class="status-pill status-<?= e($l['status']) ?>">
                  <?= strtoupper(e($l['status'])) ?>
                </span>
              </td>
              <td style="font-size: 0.82rem; color: var(--admin-text-muted); white-space: nowrap;">
                <?= date('M j, Y', strtotime($l['created_at'])) ?><br>
                <span style="font-size: 0.74rem;"><?= date('g:ia', strtotime($l['created_at'])) ?></span>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <a href="<?= url('admin/leads.php?id=' . $l['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">
                  View &amp; Edit
                </a>
                <a href="<?= url('admin/leads.php?action=delete&id=' . $l['id'] . '&csrf=' . CSRF::token()) ?>" 
                   class="btn-admin btn-admin-danger" 
                   style="font-size: 0.75rem;" 
                   onclick="return confirm('Are you sure you want to permanently delete lead #<?= $l['id'] ?>?');">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
