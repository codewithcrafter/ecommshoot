<?php
/**
 * Test Suite: ECOMMSHOOT - CTA Contrast & Text Visibility Verification
 */

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $message): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] $message\n";
        $passCount++;
    } else {
        echo "  [FAIL] $message\n";
        $failCount++;
    }
}

echo "====================================================================\n";
echo "1. VERIFYING BLOG CONSULTATION CTA SECTION IN blog.php\n";
echo "====================================================================\n";

$blogPhp = file_get_contents(__DIR__ . '/../blog.php');
assertTest(strpos($blogPhp, 'section-editorial-consultation') !== false, "blog.php contains .section-editorial-consultation semantic section");
assertTest(strpos($blogPhp, 'color: rgba(255,255,255,0.85)') === false, "blog.php does not contain fragile inline white description color");
assertTest(strpos($blogPhp, 'background-color: var(--color-primary-teal)') === false, "blog.php does not contain undefined var(--color-primary-teal)");
assertTest(strpos($blogPhp, 'Need Custom Technical Art Direction for Your Next Commercial Product Campaign?') !== false, "blog.php preserves original CTA heading");
assertTest(strpos($blogPhp, 'Discuss your SKU count, lighting mood boards, and marketplace compliance needs') !== false, "blog.php preserves original CTA description");
assertTest(strpos($blogPhp, 'Book Studio Estimate') !== false, "blog.php preserves primary button 'Book Studio Estimate'");
assertTest(strpos($blogPhp, 'Explore All Services') !== false, "blog.php preserves secondary button 'Explore All Services'");
assertTest(strpos($blogPhp, 'btn-secondary') !== false || strpos($blogPhp, 'cta-btn-secondary') !== false, "blog.php secondary button uses .btn-secondary/.cta-btn-secondary");

echo "\n====================================================================\n";
echo "2. VERIFYING GLOBAL FOOTER PRE-FOOTER CTA IN includes/footer.php\n";
echo "====================================================================\n";

$footerPhp = file_get_contents(__DIR__ . '/../includes/footer.php');
assertTest(strpos($footerPhp, 'section-cta') !== false, "footer.php contains .section-cta section");
assertTest(strpos($footerPhp, 'cta-banner-editorial') !== false, "footer.php contains .cta-banner-editorial wrapper");
assertTest(strpos($footerPhp, 'cta-banner-title') !== false, "footer.php contains .cta-banner-title");
assertTest(strpos($footerPhp, 'cta-banner-desc') !== false, "footer.php contains .cta-banner-desc");
assertTest(strpos($footerPhp, 'cta-secondary-btn') !== false, "footer.php secondary button has .cta-secondary-btn class");

echo "\n====================================================================\n";
echo "3. VERIFYING CSS CONTRAST & BACKGROUND-AWARE RULES IN style.css\n";
echo "====================================================================\n";

$styleCss = file_get_contents(__DIR__ . '/../assets/css/style.css');

// 1. Description text dark charcoal / muted charcoal (#475467)
assertTest(preg_match('/\.editorial-consultation-desc\s*\{[^}]*color:\s*#475467/i', $styleCss) === 1, "CSS sets .editorial-consultation-desc to muted charcoal #475467");

// 2. Secondary button dark charcoal text, visible border, white/off-white bg, orange hover
assertTest(preg_match('/\.cta-btn-secondary[^{]*\{[^}]*color:\s*#17171D/i', $styleCss) === 1, "CSS sets secondary button text to dark charcoal #17171D");
assertTest(preg_match('/\.cta-btn-secondary[^{]*\{[^}]*border:\s*1\.5px\s+solid\s+#D1D5DB/i', $styleCss) === 1, "CSS sets secondary button border to visible #D1D5DB");
assertTest(preg_match('/\.cta-btn-secondary[^{]*\{[^}]*background(-color)?:\s*#FFFFFF/i', $styleCss) === 1, "CSS sets secondary button background to #FFFFFF");
assertTest(preg_match('/\.cta-btn-secondary:hover[^{]*\{[^}]*color:\s*#FF6233/i', $styleCss) === 1, "CSS sets secondary button hover text to Ecommshoot orange #FF6233");
assertTest(preg_match('/\.cta-btn-secondary:hover[^{]*\{[^}]*background(-color)?:\s*#FFF0EA/i', $styleCss) === 1, "CSS sets secondary button hover background to subtle orange #FFF0EA");

// 3. Primary orange button unchanged
assertTest(preg_match('/\.btn-primary\s*\{[^}]*background-color:\s*var\(--color-orange\)/i', $styleCss) === 1, "CSS retains .btn-primary orange background");

// 4. Background-aware CTA system (dark vs light contexts)
assertTest(preg_match('/\.section-cta\s*\.cta-secondary-btn[^{]*\{[^}]*color:\s*#FFFFFF/i', $styleCss) === 1, "CSS preserves white text for secondary button on dark .section-cta");
assertTest(preg_match('/\.section-cta\.is-light[^{]*\.cta-secondary-btn[^{]*\{[^}]*color:\s*#17171D/i', $styleCss) === 1, "CSS applies dark text for secondary button in light CTA contexts");
assertTest(preg_match('/\.section-cta\.is-light[^{]*\{[^}]*background(-color)?:\s*#FAF5EF/i', $styleCss) === 1, "CSS applies #FAF5EF for light CTA background");
assertTest(strpos($styleCss, '--color-primary-teal: #0F2C2E;') !== false, "CSS defines --color-primary-teal fallback in :root");

echo "\n====================================================================\n";
echo "4. VERIFYING LIVE HTTP 200 & CTA CONTRAST ACROSS PUBLIC PAGES\n";
echo "====================================================================\n";

$urls = [
    'http://127.0.0.1:8000/blog' => 'Blog Listing',
    'http://127.0.0.1:8000/lifestyle-product-photography' => 'Lifestyle Product Photography',
    'http://127.0.0.1:8000/product-photography' => 'Product Photography',
    'http://127.0.0.1:8000/services' => 'Services Directory',
    'http://127.0.0.1:8000/' => 'Homepage',
    'http://127.0.0.1:8000/about' => 'About Us',
    'http://127.0.0.1:8000/contact' => 'Contact Page'
];

foreach ($urls as $url => $label) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    assertTest($status === 200, "$label ($url) returns HTTP 200 OK");
}

// Check blog page specific markup
$ch = curl_init('http://127.0.0.1:8000/blog');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$blogHtml = curl_exec($ch);
curl_close($ch);

assertTest(strpos($blogHtml, 'section-editorial-consultation') !== false, "Live /blog serves .section-editorial-consultation");
assertTest(strpos($blogHtml, 'editorial-consultation-title') !== false, "Live /blog serves .editorial-consultation-title");
assertTest(strpos($blogHtml, 'editorial-consultation-desc') !== false, "Live /blog serves .editorial-consultation-desc");
assertTest(strpos($blogHtml, 'cta-btn-secondary') !== false, "Live /blog serves .cta-btn-secondary button");

// Check lifestyle page specific markup
$ch = curl_init('http://127.0.0.1:8000/lifestyle-product-photography');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$lifestyleHtml = curl_exec($ch);
curl_close($ch);

assertTest(strpos($lifestyleHtml, 'section-cta') !== false, "Live /lifestyle-product-photography serves .section-cta");
assertTest(strpos($lifestyleHtml, 'cta-secondary-btn') !== false, "Live /lifestyle-product-photography serves .cta-secondary-btn");
assertTest(strpos($lifestyleHtml, 'Explore All Services') !== false, "Live /lifestyle-product-photography displays 'Explore All Services'");

echo "\n====================================================================\n";
echo "FINAL RESULTS: $passCount PASSED, $failCount FAILED\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
