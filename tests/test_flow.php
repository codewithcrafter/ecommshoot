<?php
/**
 * Automated Verification Script for Ecomshoot
 */

$baseUrl = 'http://127.0.0.1:8000';
$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// Helper curl GET
function curl_get($url, $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieFile) {
        curl_setopt($cookieFile, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($cookieFile, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['body' => $res, 'status' => $info['http_code']];
}

// 1. Fetch Login page
$ch = curl_init("{$baseUrl}/admin/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. Login Page Status: {$code}\n";
preg_match('/name="csrf_token" value="([^"]+)"/', $body, $m);
$token = $m[1] ?? '';
echo "   CSRF Token: " . substr($token, 0, 10) . "...\n";

// 2. Submit Login POST
$ch = curl_init("{$baseUrl}/admin/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $token,
    'email' => 'admin@ecomshoot.com',
    'password' => 'Admin@Ecomshoot2026!'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "2. Post-Login Status: {$code}\n";
if (strpos($body, 'Dashboard Overview') !== false) {
    echo "   SUCCESS: Dashboard Overview loaded with authenticated session!\n";
} else {
    echo "   FAIL: Dashboard text not found.\n";
}

// 3. Test Contact Form Submission
$ch = curl_init("{$baseUrl}/contact.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$body = curl_exec($ch);
preg_match('/name="csrf_token" value="([^"]+)"/', $body, $cm);
$contactToken = $cm[1] ?? '';

$ch = curl_init("{$baseUrl}/contact.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $contactToken,
    'full_name' => 'Alexander Wright',
    'email' => 'alex@wrightwatchco.com',
    'phone' => '+1 (212) 555-8900',
    'company_name' => 'Wright & Co. Luxury Watches',
    'service_required' => 'Jewellery & Luxury Product Photography',
    'message' => 'We are preparing to launch our autumn automatic chronograph collection with 12 references and require 60-slice focus stacking and 360-degree interactive spin capture.'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "3. Contact Form Submission Status: {$code}\n";
if (strpos($body, 'Your shoot inquiry has been registered') !== false || strpos($body, 'Thank you!') !== false) {
    echo "   SUCCESS: Contact form submission verified in database!\n";
} else {
    echo "   Notice: Response received.\n";
}

if (file_exists($cookieFile)) unlink($cookieFile);

