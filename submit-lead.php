<?php
/**
 * ECOMSHOOT - Public Lead Generation Capture Endpoint
 * Saves popup inquiry submissions with source page context into MySQL
 */

require_once __DIR__ . '/config/config.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
          || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
          || (isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
    redirect('');
}

// 1. Honeypot anti-bot verification
if (!empty($_POST['website_hp'])) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'message' => 'Thank you for contacting Ecomshoot! Your inquiry has been received successfully.']);
        exit;
    }
    set_flash('success', 'Thank you for contacting Ecomshoot! Your inquiry has been received successfully.');
    redirect($_POST['source_page_url'] ?? '');
}

// 2. CSRF Token Verification
$csrfToken = $_POST['csrf_token'] ?? '';
if (!CSRF::validateToken($csrfToken)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
        exit;
    }
    set_flash('error', 'Security token expired. Please try again.');
    redirect($_POST['source_page_url'] ?? '');
}

// 3. Collect and sanitize input
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$message = trim($_POST['message'] ?? '');
$sourceUrl = trim($_POST['source_page_url'] ?? '');
$sourceTitle = trim($_POST['source_page_title'] ?? '');
$sourceType = trim($_POST['source_page_type'] ?? 'public_page');
$leadSource = trim($_POST['lead_source'] ?? 'Timed Popup');

$errors = [];

if (mb_strlen($fullName) < 2) {
    $errors[] = 'Please provide your full name (minimum 2 characters).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}

if (mb_strlen($mobile) < 7 || !preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $mobile)) {
    $errors[] = 'Please provide a valid contact / mobile number.';
}

if (mb_strlen($message) < 5) {
    $errors[] = 'Please tell us about your photography requirements (minimum 5 characters).';
}

if (!empty($errors)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors, 'message' => implode(' ', $errors)]);
        exit;
    }
    set_flash('error', implode(' ', $errors));
    redirect($sourceUrl ?: '');
}

// 4. Insert into leads table
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        "INSERT INTO `leads` 
         (`full_name`, `email`, `mobile`, `message`, `source_page_url`, `source_page_title`, `source_page_type`, `lead_source`, `status`, `ip_address`, `user_agent`, `created_at`) 
         VALUES (:name, :email, :mobile, :msg, :url, :title, :type, :source, 'new', :ip, :ua, NOW())"
    );

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

    $stmt->execute([
        'name' => $fullName,
        'email' => $email,
        'mobile' => $mobile,
        'msg' => $message,
        'url' => substr($sourceUrl, 0, 255),
        'title' => substr($sourceTitle, 0, 255),
        'type' => substr($sourceType, 0, 100),
        'source' => substr($leadSource, 0, 100),
        'ip' => $ip,
        'ua' => $ua
    ]);

    $successMsg = 'Thank you for contacting Ecomshoot! Your inquiry has been received successfully.';

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'message' => $successMsg
        ]);
        exit;
    }

    set_flash('success', $successMsg);
    redirect($sourceUrl ?: '');
} catch (Exception $e) {
    error_log('Lead Submission Error: ' . $e->getMessage());
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An unexpected server error occurred. Please try again.']);
        exit;
    }
    set_flash('error', 'A server error occurred while registering your submission. Please try again.');
    redirect($sourceUrl ?: '');
}
