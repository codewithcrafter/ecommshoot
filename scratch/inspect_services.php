<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$rows = $db->query('SELECT id, title, slug, file_name, badge, display_order, status FROM services')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);

