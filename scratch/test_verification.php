<?php
/**
 * Comprehensive Automated Verification Suite for Ecommshoot UI/UX Refinement
 * Performs live HTTP assertions against the running local server.
 */

require_once __DIR__ . '/../config/config.php';

$baseUrl = 'http://127.0.0.1:8000';
$results = [
    'passed' => 0,
    'failed' => 0,
    'details' => []
];

function record_test($name, $status, $message = '') {
    global $results;
    if ($status) {
        $results['passed']++;
        echo "  [PASS] {$name}\n";
    } else {
        $results['failed']++;
        echo "  [FAIL] {$name}: {$message}\n";
    }
    $results['details'][] = ['name' => $name, 'status' => $status, 'message' => $message];
}

function http_get($url, $follow = true) {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'follow_location' => $follow ? 1 : 0,
            'max_redirects' => 5,
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $headers = $http_response_header ?? [];
    $statusLine = $headers[0] ?? '';
    preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $m);
    $statusCode = isset($m[1]) ? (int)$m[1] : 0;
    
    $location = null;
    foreach ($headers as $h) {
        if (stripos($h, 'Location:') === 0) {
            $location = trim(substr($h, 9));
        }
    }
    
    return [
        'code' => $statusCode,
        'headers' => $headers,
        'location' => $location,
        'body' => $body ?: ''
    ];
}

echo "====================================================================\n";
echo "1. VERIFYING UNPUBLISHED ENTERPRISE SERVICE & PRESERVED DATABASE\n";
echo "====================================================================\n";

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM services WHERE slug = 'professional-product-photography'");
$entRow = $stmt->fetch(PDO::FETCH_ASSOC);
record_test(
    "Enterprise service DB row preserved with status = 'draft'",
    $entRow && $entRow['status'] === 'draft',
    $entRow ? "status is " . $entRow['status'] : "row missing"
);

$genStmt = $db->query("SELECT * FROM services WHERE slug = 'product-photography'");
$genRow = $genStmt->fetch(PDO::FETCH_ASSOC);
record_test(
    "General Product Photography service preserved and published",
    $genRow && $genRow['status'] === 'published',
    $genRow ? "status is " . $genRow['status'] : "row missing"
);

$countStmt = $db->query("SELECT COUNT(*) FROM services WHERE status = 'published'");
$publishedCount = $countStmt->fetchColumn();
record_test(
    "Exactly 8 services remain published",
    (int)$publishedCount === 8,
    "Count is " . $publishedCount
);

echo "\n====================================================================\n";
echo "2. VERIFYING CANONICAL 301 REDIRECTS FOR DISCONTINUED SERVICE\n";
echo "====================================================================\n";

$resExt = http_get("{$baseUrl}/professional-product-photography", false);
record_test(
    "/professional-product-photography returns 301 redirect",
    $resExt['code'] === 301,
    "Status code: " . $resExt['code']
);
record_test(
    "/professional-product-photography redirects to /product-photography",
    $resExt['location'] === '/product-photography',
    "Location: " . $resExt['location']
);

$resPhp = http_get("{$baseUrl}/professional-product-photography.php", false);
record_test(
    "/professional-product-photography.php returns 301 redirect",
    $resPhp['code'] === 301,
    "Status code: " . $resPhp['code']
);
record_test(
    "/professional-product-photography.php redirects to /product-photography",
    $resPhp['location'] === '/product-photography',
    "Location: " . $resPhp['location']
);

echo "\n====================================================================\n";
echo "3. VERIFYING PUBLIC NAVIGATION, FOOTER, SITEMAP, LLMS.TXT\n";
echo "====================================================================\n";

$homeRes = http_get("{$baseUrl}/");
record_test(
    "Homepage navigation dropdown does not contain Enterprise Commercial link",
    strpos($homeRes['body'], 'professional-product-photography') === false,
    "Found reference in homepage HTML"
);

$footerContent = file_get_contents(__DIR__ . '/../includes/footer.php');
record_test(
    "Footer template does not contain Enterprise Commercial link",
    strpos($footerContent, 'professional-product-photography') === false,
    "Found reference in footer.php"
);

$sitemapContent = file_get_contents(__DIR__ . '/../sitemap.xml');
record_test(
    "Sitemap does not contain Enterprise service",
    strpos($sitemapContent, 'professional-product-photography') === false,
    "Found reference in sitemap.xml"
);

$llmsContent = file_get_contents(__DIR__ . '/../llms.txt');
record_test(
    "llms.txt does not contain Enterprise service",
    strpos($llmsContent, 'professional-product-photography') === false,
    "Found reference in llms.txt"
);

$servicesRes = http_get("{$baseUrl}/services");
record_test("Services index page loads with HTTP 200", $servicesRes['code'] === 200);
record_test(
    "Services index page does not display Enterprise service card",
    strpos($servicesRes['body'], 'professional-product-photography') === false,
    "Found reference on /services"
);
record_test(
    "Services index page displays agency enquiry form",
    strpos($servicesRes['body'], 'services-enquiry-card') !== false
);

echo "\n====================================================================\n";
echo "4. VERIFYING ALL 8 REMAINING SERVICE PAGES & ENQUIRY FORMS\n";
echo "====================================================================\n";

$serviceSlugs = [
    'product-photography',
    'ecommerce-product-photography',
    'lifestyle-product-photography',
    'fashion-product-photography',
    'creative-product-photography',
    'white-background-product-photography',
    'jewellery-product-photography',
    'product-photo-editing-retouching'
];

$requiredPlaceholders = [
    'e.g. Rahul Sharma',
    'e.g. rahul@company.com',
    'e.g. 9876543210',
    'e.g. We need 50 product images for our Amazon listings...'
];

foreach ($serviceSlugs as $slug) {
    $res = http_get("{$baseUrl}/{$slug}");
    record_test("Service /{$slug}: HTTP 200 OK", $res['code'] === 200, "Status is {$res['code']}");

    $html = $res['body'];

    $hasCard = strpos($html, 'services-enquiry-card') !== false;
    record_test("Service /{$slug}: Agency enquiry form card present", $hasCard);

    $allPlaceholdersPresent = true;
    foreach ($requiredPlaceholders as $ph) {
        if (strpos($html, $ph) === false) {
            $allPlaceholdersPresent = false;
            break;
        }
    }
    record_test("Service /{$slug}: All 4 required realistic placeholders present", $allPlaceholdersPresent);

    $noEnterpriseInDropdown = strpos($html, 'value="professional-product-photography"') === false;
    record_test("Service /{$slug}: Dropdown excludes Enterprise service", $noEnterpriseInDropdown);

    $hasFormSelect = strpos($html, 'form-select') !== false;
    record_test("Service /{$slug}: Uses styled .form-select dropdown", $hasFormSelect);

    $hasSelectedCurrent = preg_match('/<option\s+value="[^"]*"\s+selected>/i', $html) === 1;
    record_test("Service /{$slug}: Auto-selects current service in dropdown", $hasSelectedCurrent);

    $hasHeroTitle = strpos($html, 'page-header-title') !== false;
    record_test("Service /{$slug}: Displays hero title", $hasHeroTitle);

    $hasDarkenedHero = strpos($html, 'service-detail-enquiry-hero') !== false;
    record_test("Service /{$slug}: Uses 60/40 darkened photography hero", $hasDarkenedHero);
}

echo "\n====================================================================\n";
echo "5. VERIFYING MAIN BLOG LISTING PAGE (/blog)\n";
echo "====================================================================\n";

$blogRes = http_get("{$baseUrl}/blog");
record_test("Blog listing (/blog): HTTP 200 OK", $blogRes['code'] === 200, "Status is {$blogRes['code']}");
$blogHtml = $blogRes['body'];

record_test("Blog listing: Contains OrderPrintex fluid category track", strpos($blogHtml, 'blog-category-track') !== false);
record_test("Blog listing: Contains category chips", strpos($blogHtml, 'blog-cat-chip') !== false);
record_test("Blog listing: Contains live search form", strpos($blogHtml, 'blog-search-form') !== false);
record_test("Blog listing: Contains search input & button", strpos($blogHtml, 'blog-search-input') !== false && strpos($blogHtml, 'blog-search-btn') !== false);
record_test("Blog listing: Contains featured article hero showcase", strpos($blogHtml, 'blog-featured-article') !== false);
record_test("Blog listing: Contains 3-column posts grid", strpos($blogHtml, 'blog-posts-grid') !== false);
record_test("Blog listing: Contains .blog-card articles", strpos($blogHtml, 'blog-card') !== false);
record_test("Blog listing: Contains author initials avatar (RS/MV/etc)", strpos($blogHtml, 'author-avatar') !== false);
record_test("Blog listing: Contains Read Full Article link", strpos($blogHtml, 'blog-card-link') !== false);

echo "\n====================================================================\n";
echo "6. VERIFYING BLOG DETAIL PAGES & STICKY TABLE OF CONTENTS\n";
echo "====================================================================\n";

$blogStmt = $db->query("SELECT id FROM blogs WHERE status = 'published' LIMIT 3");
$testBlogIds = $blogStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($testBlogIds as $bId) {
    $detailRes = http_get("{$baseUrl}/blog/{$bId}");
    record_test("Blog detail /blog/{$bId}: HTTP 200 OK", $detailRes['code'] === 200, "Status is {$detailRes['code']}");

    $detailHtml = $detailRes['body'];

    record_test("Blog detail /blog/{$bId}: Two-column editorial grid (~72% / ~28%)", strpos($detailHtml, 'article-layout-grid') !== false);
    record_test("Blog detail /blog/{$bId}: Desktop sticky sidebar column present", strpos($detailHtml, 'blog-sidebar-col') !== false);
    record_test("Blog detail /blog/{$bId}: Desktop Table of Contents widget present", strpos($detailHtml, 'blog-toc-card') !== false && strpos($detailHtml, 'desktop-toc-list') !== false);
    record_test("Blog detail /blog/{$bId}: Mobile collapsible TOC accordion present", strpos($detailHtml, 'mobile-toc-container') !== false && strpos($detailHtml, 'mobileTocToggle') !== false);
    record_test("Blog detail /blog/{$bId}: Dynamic heading anchors injected", preg_match('/id="heading-\d+-[a-z0-9-]+"/i', $detailHtml) === 1);
    record_test("Blog detail /blog/{$bId}: Author perspective widget present", strpos($detailHtml, 'blog-author-widget') !== false);
    record_test("Blog detail /blog/{$bId}: Studio booking CTA widget present", strpos($detailHtml, 'blog-cta-widget') !== false);
    record_test("Blog detail /blog/{$bId}: Related studio guides grid present", strpos($detailHtml, 'Related Studio Perspectives') !== false);
}

echo "\n====================================================================\n";
echo "7. VERIFYING CSS SHADOWS, TYPOGRAPHY & JAVASCRIPT\n";
echo "====================================================================\n";

$cssContent = file_get_contents(__DIR__ . '/../assets/css/style.css');
record_test("CSS: Contains unified shadow variables", strpos($cssContent, '--shadow-card-sm') !== false && strpos($cssContent, '--shadow-hover') !== false);
record_test("CSS: Contains clamp typography for service hero", strpos($cssContent, 'clamp(2.15rem, 3.4vw, 3.25rem)') !== false);
record_test("CSS: Contains OrderPrintex blog grid rules", strpos($cssContent, '.blog-posts-grid') !== false && strpos($cssContent, 'repeat(3, minmax(0, 1fr))') !== false);
record_test("CSS: Contains sticky TOC sidebar rules", strpos($cssContent, '.blog-sidebar-col') !== false && strpos($cssContent, 'position: sticky') !== false);
record_test("CSS: Contains mobile TOC container rules", strpos($cssContent, '.mobile-toc-container') !== false);

$jsContent = file_get_contents(__DIR__ . '/../assets/js/main.js');
record_test("JS: Contains initTableOfContents", strpos($jsContent, 'initTableOfContents') !== false);
record_test("JS: Supports mobile TOC drawer toggle", strpos($jsContent, 'mobileTocToggle') !== false && strpos($jsContent, 'mobileTocDrawer') !== false);
record_test("JS: Supports IntersectionObserver scrollspy", strpos($jsContent, 'IntersectionObserver') !== false && strpos($jsContent, 'setActiveToc') !== false);

echo "\n====================================================================\n";
echo "8. VERIFYING REMOVAL OF TECHNICAL FORMULA BOXES & SEAMLESS FLOW\n";
echo "====================================================================\n";

$b3Res = http_get("{$baseUrl}/blog/3");
record_test("Blog 3 HTML: 'Amazon Main Image Formula' completely removed", strpos($b3Res['body'], 'Amazon Main Image Formula') === false);
record_test("Blog 3 HTML: Zero <pre><code> formula boxes present", strpos($b3Res['body'], 'Zero Suppression Risk') === false);
record_test("Blog 3 HTML: Natural flow from rule 6 into Secondary Images heading", preg_match('#High Resolution for Zoom.*?</h2>\s*<h2[^>]*>Optimizing Secondary Images#s', $b3Res['body']) !== false);

$b3MdRes = http_get("{$baseUrl}/blog/3.md");
record_test("Blog 3 Markdown: 'Amazon Main Image Formula' completely removed", strpos($b3MdRes['body'], 'Amazon Main Image Formula') === false);

$b1Res = http_get("{$baseUrl}/blog/1");
record_test("Blog 1 HTML: Formula code block removed", strpos($b1Res['body'], '<pre><code>Conversion Confidence') === false);

$b1MdRes = http_get("{$baseUrl}/blog/1.md");
record_test("Blog 1 Markdown: Formula code block removed", strpos($b1MdRes['body'], '```\nConversion Confidence') === false && strpos($b1MdRes['body'], '```\r\nConversion Confidence') === false);

foreach ($serviceSlugs as $slug) {
    $svcRes = http_get("{$baseUrl}/{$slug}");
    record_test("Service /{$slug}: Zero 'Image Formula' components present", stripos($svcRes['body'], 'Image Formula') === false);
}

echo "\n====================================================================\n";
echo "FINAL RESULTS: {$results['passed']} PASSED, {$results['failed']} FAILED\n";
echo "====================================================================\n";

if ($results['failed'] > 0) {
    exit(1);
}
