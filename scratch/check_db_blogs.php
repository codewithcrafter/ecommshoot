<?php
require_once __DIR__ . '/../config/config.php';
$rows = Database::getInstance()->getConnection()->query("SELECT id, title, content_format, LENGTH(content) as content_len, LENGTH(markdown_content) as md_len FROM blogs")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

