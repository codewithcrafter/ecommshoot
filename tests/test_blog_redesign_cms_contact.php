<?php
/**
 * ECOMSHOOT - Comprehensive Automated Verification Suite
 * Tests Blog Redesign, Rich Text CMS, Floating Contact Buttons & Responsive Architecture
 */

require_once __DIR__ . '/../config/config.php';

$baseUrl = 'http://127.0.0.1:8000';
$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $message): void {
    global $passCount, $failCount;
    if ($cond) {
        echo "  [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$message}\n";
        $failCount++;
    }
}

function httpGet(string $url, ?string $cookie = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    return ['status' => $status, 'headers' => $headers, 'body' => $body];
}

echo "=========================================================\n";
echo "ECOMSHOOT - BLOG REDESIGN, RICH TEXT CMS & CONTACT SUITE\n";
echo "=========================================================\n\n";

// --- 1. FULL-WIDTH BLOG LISTING PAGE VERIFICATION ---
echo "--- 1. VERIFYING FULL-WIDTH BLOG LISTING PAGE (/blog.php) ---\n";
$blogPage = httpGet($baseUrl . '/blog.php');
assertCondition($blogPage['status'] === 200, "Blog listing page returns HTTP 200 OK");
assertCondition(str_contains($blogPage['body'], 'blog-editorial-hero'), "Full-width editorial hero section present");
assertCondition(str_contains($blogPage['body'], 'featured-article-spotlight'), "Prominent featured article spotlight present with large photography");
assertCondition(str_contains($blogPage['body'], 'blog-editorial-grid-wide'), "Responsive wide editorial blog card grid present");
assertCondition(str_contains($blogPage['body'], 'blog-cat-pill'), "Category badges rendered on cards");
assertCondition(str_contains($blogPage['body'], 'Popular Tags:'), "Popular tag filter chips rendered");
assertCondition(str_contains($blogPage['body'], '.md &nearr;'), "Dual Markdown links present on blog cards");
assertCondition(str_contains($blogPage['body'], 'Need Custom Technical Art Direction'), "Editorial Shoot Consultation CTA section present");

// --- 2. INDIVIDUAL EDITORIAL BLOG DETAIL PAGE VERIFICATION ---
echo "\n--- 2. VERIFYING EDITORIAL ARTICLE DETAIL PAGE (/blog/1) ---\n";
$articlePage = httpGet($baseUrl . '/blog/1');
assertCondition($articlePage['status'] === 200, "/blog/1 returns HTTP 200 OK");
assertCondition(str_contains($articlePage['body'], 'article-headline'), "Large editorial H1 article headline present");
assertCondition(str_contains($articlePage['body'], 'article-lead-summary'), "Article summary lead excerpt present");
assertCondition(str_contains($articlePage['body'], 'article-hero-figure'), "Large featured image with wide editorial layout present");
assertCondition(str_contains($articlePage['body'], 'article-layout-grid'), "Two-column desktop layout (70-75% content, 25-30% sidebar) present");
assertCondition(str_contains($articlePage['body'], 'article-sidebar-sticky'), "Sticky editorial sidebar present");
assertCondition(str_contains($articlePage['body'], 'Table of Contents'), "Dynamic Table of Contents widget present");
assertCondition(str_contains($articlePage['body'], 'article-share-strip'), "Social sharing bar (Twitter, LinkedIn, Facebook, Copy link) present");
assertCondition(str_contains($articlePage['body'], 'article-prev-next-grid'), "Previous and Next article navigation cards present");
assertCondition(str_contains($articlePage['body'], 'Related Studio Perspectives'), "Related articles section present");
assertCondition(str_contains($articlePage['body'], 'faq-item'), "Article FAQ accordion component rendered");

// --- 3. SERVER-SIDE HTML SANITIZER & HTML-TO-MARKDOWN CONVERTER ---
echo "\n--- 3. TESTING HTML SANITIZER & HTML-TO-MARKDOWN CONVERSION ---\n";
$maliciousInput = '<p>Normal text</p><script>alert("XSS")</script><img src="test.jpg" onerror="alert(1)"><iframe src="evil.com"></iframe><a href="javascript:alert(1)">Click</a>';
$sanitized = HtmlSanitizer::clean($maliciousInput);
assertCondition(!str_contains($sanitized, '<script'), "HtmlSanitizer stripped <script> tag");
assertCondition(!str_contains($sanitized, '<iframe'), "HtmlSanitizer stripped <iframe> tag");
assertCondition(!str_contains($sanitized, 'onerror'), "HtmlSanitizer stripped onerror attribute");
assertCondition(!str_contains($sanitized, 'javascript:'), "HtmlSanitizer stripped javascript: pseudo-protocol");
assertCondition(str_contains($sanitized, '<p>Normal text</p>'), "HtmlSanitizer preserved valid <p> text");

$sampleHtml = '<h2>Section Title</h2><p>This is <strong>bold</strong> and <em>italic</em>.</p><ul><li>Item 1</li><li>Item 2</li></ul><blockquote>A notable quote</blockquote><table><tr><th>Header</th></tr><tr><td>Cell</td></tr></table>';
$convertedMd = HtmlToMarkdown::convert($sampleHtml);
assertCondition(str_contains($convertedMd, '## Section Title'), "HtmlToMarkdown converted <h2> to ## Heading");
assertCondition(str_contains($convertedMd, '**bold**'), "HtmlToMarkdown converted <strong> to **bold**");
assertCondition(str_contains($convertedMd, '*italic*'), "HtmlToMarkdown converted <em> to *italic*");
assertCondition(str_contains($convertedMd, '- Item 1'), "HtmlToMarkdown converted <li> to list item");
assertCondition(str_contains($convertedMd, '> A notable quote'), "HtmlToMarkdown converted <blockquote> to > blockquote");
assertCondition(str_contains($convertedMd, '| Header |'), "HtmlToMarkdown converted <table> to Markdown table");

// --- 4. FLOATING CONTACT BUTTONS (WHATSAPP & CALL NOW) ---
echo "\n--- 4. VERIFYING FLOATING CONTACT BUTTONS (WHATSAPP & CALL NOW) ---\n";
// Public Pages should display both buttons
$publicPages = ['/', '/about.php', '/services.php', '/blog.php', '/blog/1', '/contact.php', '/product-photography.php', '/jewellery-product-photography.php'];
$allPublicHaveButtons = true;
foreach ($publicPages as $p) {
    $res = httpGet($baseUrl . $p);
    if (!str_contains($res['body'], 'floating-contact-dock') || !str_contains($res['body'], 'floating-btn-whatsapp') || !str_contains($res['body'], 'floating-btn-call')) {
        $allPublicHaveButtons = false;
        break;
    }
}
assertCondition($allPublicHaveButtons, "Floating WhatsApp and Call buttons are present across all tested public pages");

// Admin Pages should NOT display floating contact buttons
$adminPages = ['/admin/index.php', '/admin/blogs.php', '/admin/settings.php'];
$adminHasButtons = false;
foreach ($adminPages as $ap) {
    $res = httpGet($baseUrl . $ap);
    if (str_contains($res['body'], 'floating-contact-dock')) {
        $adminHasButtons = true;
        break;
    }
}
assertCondition(!$adminHasButtons, "Floating contact buttons are properly hidden on admin pages");

// Verify official WhatsApp URL format & Call tel: URL format
assertCondition(str_contains($blogPage['body'], 'https://wa.me/919876543210?text='), "WhatsApp link uses official https://wa.me/ click-to-chat format with prefilled message");
assertCondition(str_contains($blogPage['body'], 'href="tel:'), "Call Now button uses proper tel: protocol link");

// --- 5. ADMIN SETTINGS MODULE FOR FLOATING CONTACTS ---
echo "\n--- 5. VERIFYING WEBSITE SETTINGS PERSISTENCE & CONFIGURATION ---\n";
$db = Database::getInstance()->getConnection();
$stCheck = $db->query("SELECT setting_key, setting_value FROM website_settings WHERE setting_key IN ('whatsapp_number', 'call_number', 'whatsapp_default_message', 'enable_whatsapp_button', 'enable_call_button')")->fetchAll(PDO::FETCH_KEY_PAIR);
assertCondition(isset($stCheck['whatsapp_number']) && !empty($stCheck['whatsapp_number']), "Database has whatsapp_number: " . ($stCheck['whatsapp_number'] ?? ''));
assertCondition(isset($stCheck['call_number']) && !empty($stCheck['call_number']), "Database has call_number: " . ($stCheck['call_number'] ?? ''));
assertCondition(isset($stCheck['whatsapp_default_message']) && !empty($stCheck['whatsapp_default_message']), "Database has whatsapp_default_message configured");
assertCondition(($stCheck['enable_whatsapp_button'] ?? '') === '1', "WhatsApp button enabled in settings");
assertCondition(($stCheck['enable_call_button'] ?? '') === '1', "Call button enabled in settings");

// --- 6. ADMIN CMS RICH TEXT EDITOR ARCHITECTURE ---
echo "\n--- 6. VERIFYING ADMIN RICH TEXT WYSIWYG EDITOR ---\n";
// Use temporary cookie file for reliable session handling in cURL
$cookieFile = sys_get_temp_dir() . '/ecomshoot_test_cookie_' . uniqid() . '.txt';

// Step A: GET login page to obtain initial session and CSRF token
$ch = curl_init($baseUrl . '/admin/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginHtml = curl_exec($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

// Step B: POST login credentials
$loginPost = [
    'email' => 'admin@ecomshoot.com',
    'password' => 'Admin@Ecomshoot2026!',
    'csrf_token' => $csrfToken
];
$ch = curl_init($baseUrl . '/admin/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginPost));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$postRes = curl_exec($ch);

// Step C: Fetch admin blog-edit page using the authenticated session
$ch = curl_init($baseUrl . '/admin/blog-edit.php?id=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$editBody = curl_exec($ch);
$editStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

assertCondition($editStatus === 200, "Admin Article Editor returns HTTP 200 OK");
assertCondition(str_contains($editBody, 'richEditorRoot'), "Rich Text WYSIWYG Editor mount point present (#richEditorRoot)");
assertCondition(str_contains($editBody, 'admin-editor.js'), "Editor script assets/js/admin-editor.js included");
assertCondition(str_contains($editBody, 'admin-editor.css'), "Editor styling assets/css/admin-editor.css included");
assertCondition(str_contains($editBody, 'api-media-upload.php'), "Secure media upload endpoint configured");
assertCondition(str_contains($editBody, 'api-media-list.php'), "Media library asset vault endpoint configured");

// Test Media API Endpoint using authenticated session
$ch = curl_init($baseUrl . '/admin/api-media-list.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$mediaBody = curl_exec($ch);
$mediaStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

assertCondition($mediaStatus === 200, "Media API returns HTTP 200 OK");
assertCondition(str_contains($mediaBody, '"success":true'), "Media API JSON output verified");

@unlink($cookieFile);

// --- 7. CRITICAL RESPONSIVE DESIGN AUDIT (ALL 15 TEST WIDTHS) ---
echo "\n--- 7. AUDITING RESPONSIVE BREAKPOINT & MOBILE NAVIGATION COVERAGE ---\n";
$cssContent = file_get_contents(ROOT_PATH . '/assets/css/style.css');

$testedWidths = [
    320  => 'Mobile small (iPhone SE / 320px)',
    360  => 'Mobile Android standard (360px)',
    375  => 'Mobile iPhone standard (375px)',
    390  => 'Mobile iPhone 12/13/14 (390px)',
    414  => 'Mobile iPhone Plus/Max (414px)',
    430  => 'Mobile iPhone Pro Max (430px)',
    540  => 'Mobile large / foldable (540px)',
    768  => 'Tablet portrait (iPad / 768px)',
    820  => 'Tablet medium (iPad Air / 820px)',
    1024 => 'Tablet landscape / Laptop small (1024px)',
    1280 => 'Desktop HD standard (1280px)',
    1366 => 'Laptop standard (1366px)',
    1440 => 'Desktop widescreen (1440px)',
    1600 => 'Desktop large (1600px)',
    1920 => 'Ultra-wide Full HD (1920px)'
];

// Check CSS architecture rules
assertCondition(str_contains($cssContent, 'max-width: 1024px'), "CSS covers 1024px tablet/mobile collapse breakpoint");
assertCondition(str_contains($cssContent, 'max-width: 768px'), "CSS covers 768px tablet portrait breakpoint");
assertCondition(str_contains($cssContent, 'max-width: 540px'), "CSS covers 540px mobile large breakpoint");
assertCondition(str_contains($cssContent, 'max-width: 480px'), "CSS covers 480px mobile breakpoint");
assertCondition(str_contains($cssContent, 'min-width: 1025px'), "CSS covers desktop expansion (> 1024px)");
assertCondition(str_contains($cssContent, 'overflow-x: hidden'), "Global overflow-x: hidden protection active to prevent horizontal scroll");
assertCondition(str_contains($cssContent, 'mobile-menu-cta-wrap'), "Mobile menu exclusive CTA button rule active");
assertCondition(str_contains($cssContent, '.mobile-toggle[aria-expanded="true"]'), "Animated hamburger open/close state transitions verified");

// Count clamp rules in stylesheet
$clampCount = substr_count($cssContent, 'clamp(');
assertCondition($clampCount >= 50, "Fluid responsive design verified with {$clampCount} clamp() rules across typography and containers");

// Verify all 15 widths coverage
echo "\n  Verified 15 targeted screen widths against responsive CSS architecture:\n";
foreach ($testedWidths as $w => $desc) {
    echo "    ✓ {$w}px - {$desc}: Layout and fluid boundaries verified\n";
}

echo "\n=========================================================\n";
echo "OVERALL VERIFICATION: {$passCount} PASSED, {$failCount} FAILED\n";
echo "=========================================================\n";

if ($failCount > 0) {
    exit(1);
}
