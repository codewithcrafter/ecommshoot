<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT slug, badge, h1_heading, short_description, introduction, benefits_json, features_json, process_json, industries_json FROM services WHERE slug = ?');
$stmt->execute(['ecommerce-product-photography']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
