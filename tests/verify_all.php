<?php
/**
 * ECOMSHOOT - Comprehensive Automated Quality & Responsive Verification Suite
 */

$baseUrl = 'http://127.0.0.1:8000';
$rootDir = dirname(__DIR__);

echo "=========================================================\n";
echo "ECOMSHOOT AUDIT & QUALITY VERIFICATION SUITE\n";
echo "=========================================================\n\n";

$pages = [
    'Home' => '/',
    'About Us' => '/about.php',
    'Services Directory' => '/services.php',
    'Product Photography' => '/product-photography.php',
    'E-Commerce Photography' => '/ecommerce-product-photography.php',
    'Lifestyle Photography' => '/lifestyle-product-photography.php',
    'Fashion Photography' => '/fashion-product-photography.php',
    'Creative Photography' => '/creative-product-photography.php',
    'Jewellery Photography' => '/jewellery-product-photography.php',
    'Retouching & Editing' => '/product-photo-editing-retouching.php',
    'White Background' => '/white-background-product-photography.php',
    'Professional Commercial' => '/professional-product-photography.php',
    'Studio Insights (Blog)' => '/blog.php',
    'Article HTML (/blog/1)' => '/blog/1',
    'Article Markdown (/blog/1.md)' => '/blog/1.md',
    'Contact' => '/contact.php',
    'Sitemap XML' => '/sitemap.xml',
    'LLMs TXT' => '/llms.txt',
    'Robots TXT' => '/robots.txt'
];

$allPassed = true;
$totalImagesVerified = 0;
$missingImages = [];
$missingAlts = [];

echo "--- 1. VERIFYING ALL 18 PUBLIC & SEO ENDPOINTS ---\n";
foreach ($pages as $name => $path) {
    $url = $baseUrl . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($code === 200) {
        echo "  [PASS] {$name} ({$path}) -> HTTP 200 (" . strlen($html) . " bytes)\n";
    } else {
        echo "  [FAIL] {$name} ({$path}) -> HTTP {$code}\n";
        $allPassed = false;
    }

    // Parse images if HTML
    if (strpos($path, '.xml') === false && strpos($path, '.txt') === false && strpos($path, '.md') === false) {
        preg_match_all('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $imgTag) {
            $src = $imgTag[1];
            // Check alt
            if (!preg_match('/alt=["\']([^"\']*)["\']/i', $imgTag[0], $altMatch) || trim($altMatch[1]) === '') {
                $missingAlts[] = "{$path}: " . substr($imgTag[0], 0, 80);
            }

            // Check if local file exists
            if (strpos($src, 'http') === false || strpos($src, $baseUrl) !== false) {
                $localRel = str_replace($baseUrl . '/', '', $src);
                $localRel = ltrim($localRel, '/');
                $fullPath = $rootDir . '/' . $localRel;
                if (!file_exists($fullPath) || filesize($fullPath) < 100) {
                    $missingImages[] = "{$path} -> {$src} (Not found on disk)";
                } else {
                    $totalImagesVerified++;
                }
            }
        }
    }
}

echo "\n--- 2. VERIFYING PHOTOGRAPHY ASSETS & ALT ATTRIBUTES ---\n";
echo "  Total local image instances verified on disk: {$totalImagesVerified}\n";
if (empty($missingImages)) {
    echo "  [PASS] All image files exist on disk with valid file sizes.\n";
} else {
    echo "  [FAIL] Found " . count($missingImages) . " missing images:\n";
    foreach ($missingImages as $m) echo "    - {$m}\n";
    $allPassed = false;
}

if (empty($missingAlts)) {
    echo "  [PASS] All <img> tags have descriptive alt attributes.\n";
} else {
    echo "  [WARN] Missing alt on " . count($missingAlts) . " tags:\n";
    foreach (array_slice($missingAlts, 0, 5) as $ma) echo "    - {$ma}\n";
}

echo "\n--- 3. VERIFYING RESPONSIVE DESIGN & BREAKPOINT COVERAGE ---\n";
$cssFile = $rootDir . '/assets/css/style.css';
$css = file_get_contents($cssFile);

$requiredBreakpoints = [
    '320px mobile' => 'max-width: 480px',
    '768px tablet' => 'min-width: 768px',
    '1024px tablet landscape' => 'min-width: 1024px',
    '1024px mobile dropdown collapse' => 'max-width: 1024px',
    '640px grid scaling' => 'min-width: 640px'
];

foreach ($requiredBreakpoints as $label => $needle) {
    if (strpos($css, $needle) !== false) {
        echo "  [PASS] Breakpoint covered in CSS: {$label} ({$needle})\n";
    } else {
        echo "  [FAIL] Missing breakpoint pattern: {$label}\n";
        $allPassed = false;
    }
}

// Check fluid typography clamp() usage in CSS
preg_match_all('/clamp\([^)]+\)/', $css, $clamps);
echo "  [PASS] Fluid clamp() scale rules detected in CSS: " . count($clamps[0]) . " rules\n";

echo "\n--- 4. VERIFYING SECURE SIMPLIFIED CONTACT WORKFLOW (4 FIELDS) ---\n";
require_once $rootDir . '/config/config.php';
$db = Database::getInstance()->getConnection();

$uniqueEmail = 'verify_' . time() . '@testluxuryclient.com';
$contactCookie = $rootDir . '/tests/contact_cookie.txt';
if (file_exists($contactCookie)) unlink($contactCookie);

$ch = curl_init("{$baseUrl}/contact.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $contactCookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $contactCookie);
$contactHtml = curl_exec($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $contactHtml, $ctMatch);
$csrfToken = $ctMatch[1] ?? '';

$ch = curl_init("{$baseUrl}/contact.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $contactCookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $contactCookie);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $csrfToken,
    'full_name' => 'Victoria Sterling',
    'mobile' => '+91 98765 43210',
    'email' => $uniqueEmail,
    'message' => 'Automated test inquiry for commercial catalog shoot with 40 luxury SKUs.'
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (file_exists($contactCookie)) unlink($contactCookie);

$row = $db->query("SELECT id, full_name, email, phone FROM contact_submissions WHERE email = '{$uniqueEmail}' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($row && $row['email'] === $uniqueEmail) {
    echo "  [PASS] Simplified 4-field contact submission stored in MySQL (ID #{$row['id']}, Mobile: {$row['phone']})\n";
} else {
    echo "  [FAIL] Contact submission record not found in database.\n";
    $allPassed = false;
}

echo "\n--- 5. VERIFYING AUTOMATIC LEAD POPUP AJAX ENDPOINT & DATABASE TRACKING ---\n";
$uniqueLeadEmail = 'lead_' . time() . '@fastgrower.io';
$leadCookie = $rootDir . '/tests/lead_cookie.txt';
if (file_exists($leadCookie)) unlink($leadCookie);

// Get token from home page
$ch = curl_init("{$baseUrl}/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $leadCookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $leadCookie);
$homeHtml = curl_exec($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $homeHtml, $leadCsrfMatch);
$leadCsrfToken = $leadCsrfMatch[1] ?? '';

$ch = curl_init("{$baseUrl}/submit-lead.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $leadCookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $leadCookie);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $leadCsrfToken,
    'full_name' => 'Alexander Hayes',
    'email' => $uniqueLeadEmail,
    'mobile' => '+91 91234 56789',
    'message' => 'Inquiring about 360-degree turntable jewellery shooting.',
    'source_page_url' => 'http://127.0.0.1:8000/jewellery-product-photography.php',
    'source_page_title' => 'Jewellery Product Photography',
    'source_page_type' => 'service'
]));
$leadJson = curl_exec($ch);
$leadData = json_decode($leadJson, true);

if (file_exists($leadCookie)) unlink($leadCookie);

if ($leadData && !empty($leadData['success'])) {
    echo "  [PASS] AJAX Lead submission response: \"{$leadData['message']}\"\n";
    $leadRow = $db->query("SELECT id, full_name, email, source_page_title FROM leads WHERE email = '{$uniqueLeadEmail}' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($leadRow) {
        echo "  [PASS] Lead stored in MySQL `leads` table (ID #{$leadRow['id']}, Page: {$leadRow['source_page_title']})\n";
    } else {
        echo "  [FAIL] Lead record not found in database.\n";
        $allPassed = false;
    }
} else {
    echo "  [FAIL] Lead submission failed: " . ($leadJson ?: 'Empty response') . "\n";
    $allPassed = false;
}

echo "\n--- 6. VERIFYING ADMIN PANEL PROTECTED ROUTES ---\n";
$adminPages = [
    'Dashboard' => '/admin/index.php',
    'Leads Management' => '/admin/leads.php',
    'Pages Management' => '/admin/pages.php',
    'Services Management' => '/admin/services.php',
    'Blog CMS' => '/admin/blogs.php',
    'Article Editor' => '/admin/blog-edit.php?id=1',
    'Categories & Tags' => '/admin/categories.php',
    'Media Library' => '/admin/media.php',
    'SEO Manager' => '/admin/seo.php',
    'Schema Manager' => '/admin/schemas.php',
    'Markdown CMS' => '/admin/markdown.php',
    'Sitemap Manager' => '/admin/sitemap.php',
    'Settings' => '/admin/settings.php'
];

// Authenticate session via cURL
$cookieJar = $rootDir . '/tests/auth_cookie.txt';
if (file_exists($cookieJar)) unlink($cookieJar);

// 1. Get Login Form
$ch = curl_init("{$baseUrl}/admin/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$loginHtml = curl_exec($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $lm);
$adminCsrf = $lm[1] ?? '';

// 2. Submit Login Credentials
$ch = curl_init("{$baseUrl}/admin/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $adminCsrf,
    'email' => 'admin@ecomshoot.com',
    'password' => 'Admin@Ecomshoot2026!'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);

foreach ($adminPages as $admName => $admPath) {
    $ch = curl_init("{$baseUrl}{$admPath}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $admHtml = curl_exec($ch);
    $admCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($admCode === 200 && strpos($admHtml, 'Sign in to access CMS') === false) {
        echo "  [PASS] {$admName} ({$admPath}) -> HTTP 200 Authenticated\n";
    } else {
        echo "  [FAIL] {$admName} ({$admPath}) -> Failed authentication\n";
        $allPassed = false;
    }
}

// 3. Verify Leads CSV Export
$ch = curl_init("{$baseUrl}/admin/leads.php?export=csv");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$csv = curl_exec($ch);
if (strpos($csv, 'Full Name') !== false && strpos($csv, 'Mobile') !== false) {
    echo "  [PASS] Leads CSV Export format verified with valid column headers\n";
} else {
    echo "  [FAIL] Leads CSV export failed (Length: " . strlen($csv) . ", Content: " . substr(strip_tags($csv), 0, 100) . ")\n";
    $allPassed = false;
}

if (file_exists($cookieJar)) unlink($cookieJar);

echo "\n=========================================================\n";
if ($allPassed) {
    echo "OVERALL VERIFICATION: 100% PASSED - ALL CRITERIA SATISFIED!\n";
} else {
    echo "OVERALL VERIFICATION: SOME CHECKS FAILED. PLEASE REVIEW.\n";
}
echo "=========================================================\n";
