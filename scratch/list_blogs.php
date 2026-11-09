<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$rows = $db->query("SELECT id, title, slug, status, category_id FROM blogs")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . " | " . $r['title'] . " | " . $r['slug'] . " | " . $r['status'] . "\n";
}

