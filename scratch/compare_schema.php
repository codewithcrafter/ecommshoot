<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

$localTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$sqlContent = file_get_contents(__DIR__ . '/../database/database.sql');

echo "=== TABLES COMPARISON ===\n";
echo "Local tables (" . count($localTables) . "):\n";
foreach ($localTables as $t) {
    $inSql = preg_match('/CREATE TABLE `?' . preg_quote($t, '/') . '`?/i', $sqlContent);
    echo "- $t: " . ($inSql ? "Present in database.sql" : "MISSING from database.sql !!!") . "\n";
}

echo "\n=== COLUMNS COMPARISON FOR EACH TABLE ===\n";
foreach ($localTables as $t) {
    $cols = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    
    // Find create table block in database.sql
    if (preg_match('/CREATE TABLE `?' . preg_quote($t, '/') . '`?\s*\((.*?)\)\s*ENGINE/is', $sqlContent, $matches)) {
        $block = $matches[1];
        $missingInSql = [];
        foreach ($colNames as $col) {
            if (!preg_match('/`?' . preg_quote($col, '/') . '`?/i', $block)) {
                $missingInSql[] = $col;
            }
        }
        if (!empty($missingInSql)) {
            echo "Table `$t` has columns missing in database.sql: " . implode(', ', $missingInSql) . "\n";
        }
    } else {
        echo "Table `$t`: CREATE statement not found in database.sql\n";
    }
}

