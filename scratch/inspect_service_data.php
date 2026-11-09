<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

$services = $db->query("SELECT * FROM services ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Total services in local DB: " . count($services) . "\n";
foreach ($services as $s) {
    echo "- #{$s['id']} [{$s['slug']}] {$s['title']} (status: {$s['status']}, order: {$s['display_order']}, file: {$s['file_name']})\n";
    echo "  Short desc: " . substr($s['short_description'], 0, 60) . "...\n";
    echo "  Has benefits: " . (!empty($s['benefits_json']) ? 'YES' : 'NO') . " | features: " . (!empty($s['features_json']) ? 'YES' : 'NO') . "\n";
}

