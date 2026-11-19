<?php
/**
 * Section Alignment & Visual Balance Verification Suite
 */
$baseUrl = 'http://127.0.0.1:8000';

function getUrl($path) {
    global $baseUrl;
    $ch = curl_init($baseUrl . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $resp];
}

$css = file_get_contents(__DIR__ . '/../assets/css/style.css');
$js = file_get_contents(__DIR__ . '/../assets/js/main.js');

echo "====================================================================\n";
echo "1. VERIFYING CSS SECTION INTRODUCTION CENTERING RULES\n";
echo "====================================================================\n";

assertRule($css, '/\.section-editorial-header,\s*\.section-header\s*\{[^}]*text-align:\s*center/i', 'Base section headers text-align: center');
assertRule($css, '/\.section-editorial-header,\s*\.section-header\s*\{[^}]*max-width:\s*950px/i', 'Base section headers max-width: 950px');
assertRule($css, '/\.section-editorial-header,\s*\.section-header\s*\{[^}]*margin-left:\s*auto;\s*margin-right:\s*auto/i', 'Base section headers centered margins');
assertRule($css, '/\.section-title-editorial,\s*\.section-title\s*\{[^}]*font-size:\s*clamp\(36px,\s*3\.5vw,\s*58px\)/i', 'Heading font-size clamp(36px, 3.5vw, 58px)');
assertRule($css, '/\.section-title-editorial,\s*\.section-title\s*\{[^}]*max-width:\s*950px/i', 'Heading max-width: 950px');
assertRule($css, '/\.section-sub-editorial,\s*\.section-subtitle\s*\{[^}]*max-width:\s*750px/i', 'Description max-width: 750px');
assertRule($css, '/\.section-editorial-header\s+\.header-tagline[^{]*\{[^}]*justify-content:\s*center/i', 'Eyebrow tagline badge centered flex');
assertRule($css, '/\.section-editorial-header\s+\.badge[^{]*\{[^}]*margin-left:\s*auto;\s*margin-right:\s*auto/i', 'Eyebrow badge centered margins');

echo "\n====================================================================\n";
echo "2. VERIFYING HOMEPAGE & ABOUT PAGE CENTERING OVERRIDES\n";
echo "====================================================================\n";

assertRule($css, '/\.home-ui-redesign\s+\.section-editorial-header\s*\{[^}]*text-align:\s*center/i', 'Homepage section header text-align: center');
assertRule($css, '/\.home-ui-redesign\s+\.section-title-editorial\s*\{[^}]*max-width:\s*950px/i', 'Homepage heading max-width: 950px');
assertRule($css, '/\.home-ui-redesign\s+\.section-sub-editorial\s*\{[^}]*max-width:\s*750px/i', 'Homepage description max-width: 750px');
assertRule($css, '/\.about-ui-redesign\s+\.section-editorial-header\s*\{[^}]*text-align:\s*center/i', 'About section header text-align: center');
assertRule($css, '/\.about-ui-redesign\s+\.section-title-editorial\s*\{[^}]*max-width:\s*950px/i', 'About heading max-width: 950px');
assertRule($css, '/\.about-ui-redesign\s+\.section-sub-editorial\s*\{[^}]*max-width:\s*750px/i', 'About description max-width: 750px');

echo "\n====================================================================\n";
echo "3. VERIFYING PRESERVATION OF INTENTIONAL SPLIT LAYOUTS\n";
echo "====================================================================\n";

assertRule($css, '/\.section-why-split\s+\.editorial-h2\s*\{[^}]*text-align:\s*left/i', 'Homepage Why Ecomshoot editorial-h2 remains text-align: left');
assertRule($css, '/\.section-why-split\s+\.about-lead\s*\{[^}]*text-align:\s*left/i', 'Homepage Why Ecomshoot about-lead remains text-align: left');
assertRule($css, '/\.editorial-split-box\s+\.editorial-h2\s*\{[^}]*text-align:\s*left/i', 'About page Our Story editorial-h2 remains text-align: left');
assertRule($css, '/\.editorial-split-box\s+\.about-lead\s*\{[^}]*text-align:\s*left/i', 'About page Our Story about-lead remains text-align: left');

echo "\n====================================================================\n";
echo "4. VERIFYING MOBILE RESPONSIVENESS & ENTRANCE ANIMATIONS\n";
echo "====================================================================\n";

assertRule($css, '/\.section-title-editorial,\s*\.section-title\s*\{[^}]*font-size:\s*clamp\(26px,\s*6\.5vw,\s*36px\)/i', 'Mobile dynamic heading clamp(26px, 6.5vw, 36px)');
assertRule($css, '/\.section-intro-hidden/i', 'CSS subtle entrance animation hidden state');
assertRule($css, '/\.section-intro-visible/i', 'CSS subtle entrance animation visible state');
assertRule($js, '/initSectionAnimations/i', 'JS initSectionAnimations function present');
assertRule($js, '/section-intro-visible/i', 'JS activates section-intro-visible on intersection');

echo "\n====================================================================\n";
echo "5. VERIFYING LIVE PUBLIC PAGES HTTP 200 & SECTION HEADINGS\n";
echo "====================================================================\n";

$pages = [
    '/' => ['Commercial Art Directed Portfolio', 'Product Photography in Delhi', 'Our 5 Stage Process', 'Industries We Shoot', 'Guides, tips and Case Studies'],
    '/about' => ['Hardware & Studio Capabilities', 'Our Tools & Technology', 'The Ecomshoot Quality Standard'],
    '/services' => ['Choosing the Right Photography Style for Your Campaign'],
    '/product-photography' => ['Better Product Images to Improve Your Business', 'Features and Deliverables in Every Shoot', 'A Closer Look at Our Product Photography'],
    '/ecommerce-product-photography' => ['Increase your bottom line with better product photography', 'Specs'],
    '/lifestyle-product-photography' => ['Conversion, Cost & Premium', 'Specification'],
    '/fashion-product-photography' => ['Benefits for Sales', 'Technical'],
    '/contact' => ['Connect with Our Creative Production Team']
];

foreach ($pages as $path => $headings) {
    $res = getUrl($path);
    echo "Page {$path}: HTTP {$res['code']}\n";
    if ($res['code'] === 200) {
        foreach ($headings as $h) {
            $hasH = (strpos($res['body'], $h) !== false || strpos($res['body'], htmlspecialchars($h)) !== false);
            echo "  - Heading '{$h}': " . ($hasH ? "PASS" : "FAIL") . "\n";
        }
    }
}

function assertRule($content, $regex, $label) {
    if (preg_match($regex, $content)) {
        echo "  [PASS] {$label}\n";
    } else {
        echo "  [FAIL] {$label}\n";
    }
}
