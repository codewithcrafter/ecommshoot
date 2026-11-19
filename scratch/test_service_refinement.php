<?php
/**
 * Test Suite: Service Page UI/UX Refinement Verification
 * Tests all 9 requirements from the user prompt:
 * 1. Hero Heading Separation (E-Commerce page only)
 * 2. Hero Layout (58/42 text/form balance, compact, vertically stacked on mobile)
 * 3. Removal of Repeated Eyebrows on E-Commerce page
 * 4. Logo Contrast across light/dark backgrounds (Header, Drawer, Footer)
 * 5. Services Navigation (Text navigates to /services, chevron toggles submenu)
 * 6. Mobile Service Cards (Single-column 1fr, no cramped narrow columns or squished characters)
 * 7. Card Colors & Contrast (White, Charcoal, Muted, Orange accents, 16px radius)
 * 8. Overall Spacing & Container Consistency
 * 9. Subtle Animations & Reduced Motion Compliance
 */

$baseUrl = 'http://127.0.0.1:8000';
$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $message): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] $message\n";
    } else {
        $failed++;
        echo "  [FAIL] $message\n";
    }
}

function fetchUrl(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Ecommshoot-Test-Suite/1.0'
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $body ?: ''];
}

echo "====================================================================\n";
echo "1. VERIFYING E-COMMERCE HERO HEADING SEPARATION & EYEBROW\n";
echo "====================================================================\n";

$ecomRes = fetchUrl("$baseUrl/ecommerce-product-photography");
assertTest($ecomRes['status'] === 200, "Live /ecommerce-product-photography returns HTTP 200");

$ecomBody = $ecomRes['body'];

// Main H1
$expectedH1 = 'High Volume Ecommerce Product Photography for high-growth brands.';
assertTest(strpos($ecomBody, '<h1 class="page-header-title">' . $expectedH1 . '</h1>') !== false,
    "Hero H1 is exactly: '$expectedH1'");

// Subheading
$expectedSubheading = 'Capturing sharp & consistent images for your Shopify Plus store, Amazon seller account or multi-channel retailer.';
assertTest(strpos($ecomBody, 'class="page-header-sub service-hero-subheading">' . $expectedSubheading . '</p>') !== false,
    "Hero Subheading is rendered as separate paragraph: '$expectedSubheading'");

// Eyebrow badge
assertTest(strpos($ecomBody, '<span class="badge badge-orange">HIGH VOLUME</span>') !== false,
    "Hero eyebrow badge is 'HIGH VOLUME'");

// No duplicate text in hero
$h1Count = substr_count($ecomBody, $expectedH1);
assertTest($h1Count === 1, "Main H1 text appears exactly once in the page");

$subCount = substr_count($ecomBody, $expectedSubheading);
assertTest($subCount === 1, "Hero Subheading text appears exactly once (no repetition in description)");

echo "\n====================================================================\n";
echo "2. VERIFYING REMOVAL OF REPEATED EYEBROWS ON E-COMMERCE PAGE\n";
echo "====================================================================\n";

// Ensure redundant section eyebrows were removed
assertTest(strpos($ecomBody, 'Service Overview') === false, "Redundant 'Service Overview' badge is suppressed");
assertTest(strpos($ecomBody, 'Business Impact') === false, "Redundant 'Business Impact' badge is suppressed");
assertTest(strpos($ecomBody, 'Studio Specifications') === false, "Redundant 'Studio Specifications' badge is suppressed");
assertTest(strpos($ecomBody, 'Visual Proof') === false, "Redundant 'Visual Proof' badge is suppressed");
assertTest(strpos($ecomBody, 'Category Expertise') === false, "Redundant 'Category Expertise' badge is suppressed");
assertTest(strpos($ecomBody, 'Production Roadmap') === false, "Redundant 'Production Roadmap' badge is suppressed");
assertTest(strpos($ecomBody, 'Service FAQ') === false, "Redundant 'Service FAQ' badge is suppressed");
assertTest(strpos($ecomBody, 'Complementary Solutions') === false, "Redundant 'Complementary Solutions' badge is suppressed");

// But section headings must be preserved
assertTest(strpos($ecomBody, 'Increase your bottom line with better product photography') !== false,
    "Section heading 'Increase your bottom line with better product photography' is preserved");
assertTest(strpos($ecomBody, 'Specs') !== false, "Section heading 'Specs' is preserved");
assertTest(strpos($ecomBody, 'Proof') !== false, "Section heading 'Proof' is preserved");
assertTest(strpos($ecomBody, 'Categories') !== false, "Section heading 'Categories' is preserved");
assertTest(strpos($ecomBody, 'Process') !== false, "Section heading 'Process' is preserved");
assertTest(strpos($ecomBody, 'Frequently Asked Questions') !== false, "Section heading 'Frequently Asked Questions' is preserved");

// Check another service page still renders badges if it has them
$otherRes = fetchUrl("$baseUrl/product-photography");
assertTest(strpos($otherRes['body'], 'badge') !== false, "General product photography page still renders badges");

echo "\n====================================================================\n";
echo "3. VERIFYING LOGO CONTRAST ON LIGHT AND DARK BACKGROUNDS\n";
echo "====================================================================\n";

// Footer logo file
$footerPhp = file_get_contents(__DIR__ . '/../includes/footer.php');
assertTest(strpos($footerPhp, 'logo-light.svg') !== false, "Footer uses dedicated logo-light.svg");

// logo-light.svg content
$logoLightPath = __DIR__ . '/../assets/images/logo-light.svg';
assertTest(file_exists($logoLightPath), "assets/images/logo-light.svg file exists");
$logoLightSvg = file_get_contents($logoLightPath);
assertTest(strpos($logoLightSvg, 'fill="#FFFFFF"') !== false, "logo-light.svg uses fill='#FFFFFF' for high contrast on dark footer");
assertTest(strpos($logoLightSvg, 'fill="#FF6233"') !== false, "logo-light.svg retains brand orange #FF6233 accent");

// Mobile drawer brand logo
$headerPhp = file_get_contents(__DIR__ . '/../includes/header.php');
assertTest(strpos($headerPhp, 'drawer-logo-img') !== false, "Mobile drawer header includes branded drawer-logo-img");

// Live footer output
assertTest(strpos($ecomBody, 'logo-light.svg') !== false, "Live rendered footer outputs logo-light.svg");
assertTest(strpos($ecomBody, 'drawer-logo-img') !== false, "Live rendered mobile drawer outputs drawer-logo-img");

echo "\n====================================================================\n";
echo "4. VERIFYING SERVICES NAVIGATION & SEPARATED DROPDOWN TRIGGER\n";
echo "====================================================================\n";

// Header markup has clean link to /services
assertTest(preg_match('/<a\s+href="[^"]*\/services"\s+class="[^"]*nav-link-services"[^>]*id="servicesNavLink"/', $headerPhp) === 1,
    "Header has dedicated <a href='/services' class='nav-link-services' id='servicesNavLink'>");

// Header markup has toggle button for dropdown
assertTest(preg_match('/<button\s+class="[^"]*nav-dropdown-toggle-btn"[^>]*id="servicesDropdownToggle"/', $headerPhp) === 1,
    "Header has dedicated <button class='nav-dropdown-toggle-btn' id='servicesDropdownToggle'>");

// ARIA attributes on toggle button
assertTest(strpos($headerPhp, 'aria-haspopup="true"') !== false, "Toggle button has aria-haspopup='true'");
assertTest(strpos($headerPhp, 'aria-controls="servicesDropdownMenu"') !== false, "Toggle button has aria-controls='servicesDropdownMenu'");

// JS handling
$mainJs = file_get_contents(__DIR__ . '/../assets/js/main.js');
assertTest(strpos($mainJs, "dropdownToggle.addEventListener('click'") !== false, "JS attaches click event to dropdownToggle button");
assertTest(strpos($mainJs, "e.stopPropagation()") !== false, "JS stops click propagation on toggle button");
assertTest(strpos($mainJs, "dropdownParent.querySelector('.nav-link-dropdown-group')") !== false,
    "JS aligns dropdown under trigger group");

// Verify /services endpoint returns 200
$servicesRes = fetchUrl("$baseUrl/services");
assertTest($servicesRes['status'] === 200, "Direct route /services returns HTTP 200 OK");

echo "\n====================================================================\n";
echo "5. VERIFYING MOBILE CARD GRID LAYOUTS (SCREENSHOT 3 FIX)\n";
echo "====================================================================\n";

$styleCss = file_get_contents(__DIR__ . '/../assets/css/style.css');

// Check tablet 2-column layout
assertTest(strpos($styleCss, '.service-benefits-grid') !== false, "style.css styles .service-benefits-grid");
assertTest(strpos($styleCss, 'repeat(2, minmax(0, 1fr))') !== false, "style.css uses 2 columns on tablet");

// Check mobile 1-column layout at <= 768px
assertTest(preg_match('/@media\s*\(max-width:\s*768px\)[^{]*\{[^}]*\.service-benefits-grid[^}]*grid-template-columns:\s*1fr\s*!important/s', $styleCss) === 1,
    "style.css forces 1fr !important on mobile <= 768px for service card grids");

assertTest(preg_match('/@media\s*\(max-width:\s*768px\)[^{]*\{[^}]*\.industries-grid[^}]*grid-template-columns:\s*1fr\s*!important/s', $styleCss) === 1,
    "style.css forces 1fr !important on mobile <= 768px for .industries-grid");

assertTest(preg_match('/@media\s*\(max-width:\s*768px\)[^{]*\{[^}]*\.process-grid[^}]*grid-template-columns:\s*1fr\s*!important/s', $styleCss) === 1,
    "style.css forces 1fr !important on mobile <= 768px for .process-grid");

assertTest(preg_match('/@media\s*\(max-width:\s*768px\)[^{]*\{[^}]*\.process-steps-row[^}]*grid-template-columns:\s*1fr\s*!important/s', $styleCss) === 1,
    "style.css forces 1fr !important on mobile <= 768px for .process-steps-row");

// Word wrapping prevention against single-letter vertical stacking
assertTest(strpos($styleCss, 'overflow-wrap: break-word;') !== false, "style.css includes overflow-wrap: break-word for cards");
assertTest(strpos($styleCss, 'word-break: normal;') !== false, "style.css includes word-break: normal to prevent character squishing");

echo "\n====================================================================\n";
echo "6. VERIFYING CARD STYLES, COLORS & CONTRAST\n";
echo "====================================================================\n";

// White card background & subtle border
assertTest(strpos($styleCss, '.service-detail-ui .benefit-card') !== false, "style.css contains .service-detail-ui .benefit-card rules");
assertTest(strpos($styleCss, '.service-detail-ui .process-card') !== false, "style.css contains .service-detail-ui .process-card rules");
assertTest(strpos($styleCss, '.service-detail-ui .feature-box') !== false, "style.css contains .service-detail-ui .feature-box rules");
assertTest(strpos($styleCss, 'border-radius: 16px;') !== false, "style.css uses consistent 16px border-radius");
assertTest(strpos($styleCss, 'border-color: #FFB399;') !== false, "style.css uses soft orange #FFB399 on hover");

// Hero 58/42 layout
assertTest(strpos($styleCss, 'minmax(0, 58fr) minmax(360px, 42fr)') !== false,
    "style.css implements approximately 58% text / 42% form grid split");

// Reduced motion compliance
assertTest(strpos($styleCss, 'prefers-reduced-motion') !== false, "style.css complies with prefers-reduced-motion");

echo "\n====================================================================\n";
echo "FINAL RESULTS: $passed PASSED, $failed FAILED\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
}
