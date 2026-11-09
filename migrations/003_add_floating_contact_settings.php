<?php
/**
 * Migration 003: Initialize Floating Contact (WhatsApp & Call) Settings
 */
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance()->getConnection();

    $defaults = [
        'whatsapp_number' => '+91 98765 43210',
        'call_number' => '+91 98765 43210',
        'whatsapp_default_message' => 'Hello Ecomshoot, I would like to discuss my product photography requirements.',
        'enable_whatsapp_button' => '1',
        'enable_call_button' => '1'
    ];

    $stmt = $db->prepare("INSERT INTO website_settings (setting_key, setting_value, setting_group, created_at, updated_at) 
                          VALUES (:k, :v, 'contact', NOW(), NOW()) 
                          ON DUPLICATE KEY UPDATE setting_value = IF(setting_value IS NULL OR setting_value = '', :v2, setting_value)");

    foreach ($defaults as $k => $v) {
        $stmt->execute(['k' => $k, 'v' => $v, 'v2' => $v]);
    }

    echo "Migration 003 completed successfully.\n";
} catch (Exception $e) {
    echo "Migration 003 error: " . $e->getMessage() . "\n";
    exit(1);
}

