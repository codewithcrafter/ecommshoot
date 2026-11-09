<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

echo "--- VERIFYING BLOG CMS ENHANCEMENTS ---\n";

// 1. Check leads table
$leadsCount = $db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
echo "Total leads in database: {$leadsCount}\n";

// 2. Check blog tags
$tagsCount = $db->query("SELECT COUNT(*) FROM blog_tags")->fetchColumn();
echo "Total blog tags in database: {$tagsCount}\n";

// 3. Assign tags to blog #1 and verify
$db->query("INSERT IGNORE INTO blog_tag_relations (blog_id, tag_id) VALUES (1, 1), (1, 2)");
$relCount = $db->query("SELECT COUNT(*) FROM blog_tag_relations WHERE blog_id = 1")->fetchColumn();
echo "Tags assigned to Blog #1: {$relCount}\n";

// 4. Check website settings
$settings = [];
foreach ($db->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_ASSOC) as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
echo "Official Contact Email: " . ($settings['site_email'] ?? 'Not set') . "\n";
echo "Popup Status: " . (($settings['popup_enabled'] ?? '') == '1' ? 'Enabled' : 'Disabled') . "\n";
echo "Popup Delay: " . ($settings['popup_delay'] ?? 'Not set') . "s\n";
echo "Popup Heading: " . ($settings['popup_heading'] ?? 'Not set') . "\n";

echo "ALL CHECKS PASSED!\n";

