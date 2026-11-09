<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "=== ALL LOCAL DATABASE SCHEMAS ===\n\n";
foreach ($tables as $table) {
    $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    echo "/* Table: $table */\n";
    echo $create['Create Table'] . ";\n\n";
}

