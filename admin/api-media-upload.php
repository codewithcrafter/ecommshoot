<?php
/**
 * ECOMSHOOT - Admin API: Rich Text Editor Image Uploader
 * Securely handles inline image uploads from WYSIWYG editor.
 */

require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/Uploader.php';

header('Content-Type: application/json; charset=UTF-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check CSRF via header or post body
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
if (!CSRF::validate($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF validation failed']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No image file uploaded']);
    exit;
}

$alt = trim($_POST['alt_text'] ?? '');
$caption = trim($_POST['caption'] ?? '');

$result = Uploader::uploadImage($_FILES['image'], $alt, $caption);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'url' => asset_url($result['file_path']),
        'path' => $result['file_path'],
        'alt' => $result['alt_text'] ?? $alt,
        'caption' => $result['caption'] ?? $caption
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Image upload failed'
    ]);
}
