<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$users = $db->query("SELECT id, name, email, password_hash, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo $u['id'] . " | " . $u['email'] . " | " . $u['role'] . "\n";
}

