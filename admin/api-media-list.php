<?php
/**
 * ECOMSHOOT - Admin API: Media Library Asset List
 * Provides JSON list of uploaded assets for the Rich Text Media Modal.
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, file_name, file_path, alt_text, caption, created_at FROM media ORDER BY id DESC LIMIT 50");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $item['full_url'] = asset_url($item['file_path']);
    }

    echo json_encode(['success' => true, 'items' => $items]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database query failed']);
}
