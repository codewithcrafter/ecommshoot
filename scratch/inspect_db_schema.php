<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

echo "=== TABLES IN LOCAL DATABASE (ecomshoot_db) ===\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "- $t ($count rows)\n";
}

echo "\n=== SCHEMA OF `services` TABLE ===\n";
if (in_array('services', $tables)) {
    $create = $db->query("SHOW CREATE TABLE `services`")->fetch(PDO::FETCH_ASSOC);
    echo $create['Create Table'] . "\n\n";
    
    echo "Sample data in `services`:\n";
    $rows = $db->query("SELECT id, name, slug, status, display_order FROM `services` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} else {
    echo "NO `services` TABLE FOUND LOCALLY!\n";
}

