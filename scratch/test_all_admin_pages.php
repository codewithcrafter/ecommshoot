<?php
$pages = [
    'admin/index.php',
    'admin/blogs.php',
    'admin/blog-edit.php',
    'admin/categories.php',
    'admin/contacts.php',
    'admin/leads.php',
    'admin/markdown.php',
    'admin/markdown-edit.php',
    'admin/media.php',
    'admin/pages.php',
    'admin/page-edit.php?id=1',
    'admin/schemas.php',
    'admin/schema-edit.php',
    'admin/seo.php',
    'admin/seo-edit.php?id=1',
    'admin/services.php',
    'admin/service-edit.php?id=1',
    'admin/settings.php',
    'admin/sitemap.php',
];

$cookie = __DIR__ . '/test_cookie.txt';
$errors = [];
foreach ($pages as $p) {
    $ch = curl_init("http://127.0.0.1:8085/$p");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $hasFatal = (stripos($body, 'Fatal error:') !== false || stripos($body, 'Parse error:') !== false || stripos($body, 'Uncaught ') !== false);
    echo "$p => HTTP $status" . ($hasFatal ? ' [FATAL ERROR]' : ' [OK]') . "\n";
    if ($status !== 200 || $hasFatal) {
        $errors[] = $p;
    }
}

if (empty($errors)) {
    echo "\nAll admin pages loaded cleanly with HTTP 200!\n";
} else {
    echo "\nErrors on: " . implode(', ', $errors) . "\n";
}
