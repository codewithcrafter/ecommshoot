<?php
$baseUrl = 'http://127.0.0.1:8000';
$cookieJar = __DIR__ . '/auth_cookie_leads.txt';
if (file_exists($cookieJar)) unlink($cookieJar);

// 1. Login
$ch = curl_init("{$baseUrl}/admin/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$loginHtml = curl_exec($ch);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $lm);
$csrf = $lm[1] ?? '';

$ch = curl_init("{$baseUrl}/admin/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $csrf,
    'email' => 'admin@ecomshoot.com',
    'password' => 'Admin@Ecomshoot2026!'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
curl_close($ch);

// 2. Fetch leads dashboard
$ch = curl_init("{$baseUrl}/admin/leads.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$dashboardHtml = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Admin Leads Page HTTP Code: {$code}\n";
echo "Contains Alexander Hayes: " . (strpos($dashboardHtml, 'Alexander Hayes') !== false ? 'YES' : 'NO') . "\n";
echo "Contains Metric Cards: " . (strpos($dashboardHtml, 'Total Inquiries') !== false ? 'YES' : 'NO') . "\n";

// 3. Test CSV export
$ch = curl_init("{$baseUrl}/admin/leads.php?export=csv");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$csv = curl_exec($ch);
curl_close($ch);
echo "CSV Export Headers: " . substr($csv, 0, 70) . "...\n";
echo "CSV Export Contains Lead: " . (strpos($csv, 'Alexander Hayes') !== false ? 'YES' : 'NO') . "\n";

if (file_exists($cookieJar)) unlink($cookieJar);
