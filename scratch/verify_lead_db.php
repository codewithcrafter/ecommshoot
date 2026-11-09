<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

$lead = $db->query("SELECT * FROM leads ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Latest Lead ID: {$lead['id']}\n";
echo "Lead Name: {$lead['full_name']}\n";
echo "Lead Email: {$lead['email']}\n";
echo "Lead Mobile: {$lead['mobile']}\n";
echo "Source Page: {$lead['source_page_title']} ({$lead['source_page_url']})\n";
echo "Lead Status: {$lead['status']}\n";

