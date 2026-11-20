<?php
$ch = curl_init('http://127.0.0.1:8085/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/test_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/test_cookie.txt');
$body = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $body, $m);
$token = $m[1] ?? '';
echo "CSRF Token: $token\n";

$ch = curl_init('http://127.0.0.1:8085/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $token,
    'email' => 'admin@ecomshoot.com',
    'password' => 'Admin@Ecomshoot2026!'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/test_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/test_cookie.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Post-login HTTP Code: $code\n";
if (strpos($res, 'Dashboard Overview') !== false) {
    echo "SUCCESS: Logged in and reached Dashboard Overview!\n";
} else {
    echo "Output snippet: " . substr(strip_tags($res), 0, 200) . "\n";
}

