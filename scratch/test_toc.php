<?php
/**
 * TOC Verification Script
 */
$baseUrl = 'http://127.0.0.1:8000';

function getUrl($path) {
    global $baseUrl;
    $ch = curl_init($baseUrl . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $resp];
}

$css = file_get_contents(__DIR__ . '/../assets/css/style.css');
$js = file_get_contents(__DIR__ . '/../assets/js/main.js');

echo "--- VERIFYING CSS RULES FOR TOC ---\n";
assertRule($css, '/\.article-layout-grid\s*\{[^}]*grid-template-columns:\s*minmax\(0,\s*73fr\)\s*minmax\(280px,\s*27fr\)/i', 'Grid 73% / 27% columns');
assertRule($css, '/\.article-layout-grid\s*\{[^}]*align-items:\s*start/i', 'Grid align-items: start');
assertRule($css, '/\.article-sidebar-sticky[^{]*\{[^}]*position:\s*sticky/i', 'Sidebar position: sticky');
assertRule($css, '/\.article-sidebar-sticky[^{]*\{[^}]*top:\s*calc\(var\(--total-header-height,\s*111px\)\s*\+\s*20px\)/i', 'Sidebar top: 20px offset below header');
assertRule($css, '/\.blog-toc-card\s*\{[^}]*background:\s*#FFFFFF/i', 'TOC Card white background #FFFFFF');
assertRule($css, '/\.blog-toc-card\s*\{[^}]*border:\s*1px\s*solid\s*#E5E7EB/i', 'TOC Card subtle border #E5E7EB');
assertRule($css, '/\.blog-toc-card\s*\{[^}]*border-top:\s*3px\s*solid\s*#FF6233/i', 'TOC Card orange top accent #FF6233');
assertRule($css, '/\.blog-toc-card\s*\{[^}]*max-height:\s*calc\(100vh\s*-\s*var\(--total-header-height,\s*111px\)\s*-\s*40px\)/i', 'TOC Card max-height viewport constraint');
assertRule($css, '/\.desktop-toc-list\s*\{[^}]*overflow-y:\s*auto/i', 'Desktop TOC internal vertical scrollbar');
assertRule($css, '/\.desktop-toc-list\s*\{[^}]*overflow-x:\s*hidden/i', 'Desktop TOC no horizontal overflow');
assertRule($css, '/\.toc-nav-link\.is-active[^{]*\{[^}]*background:\s*#FFF0EA/i', 'Active link background #FFF0EA');
assertRule($css, '/\.toc-nav-link\.is-active[^{]*\{[^}]*color:\s*#FF6233/i', 'Active link color #FF6233');
assertRule($css, '/\.toc-nav-link\.is-active[^{]*\{[^}]*border-left:\s*3px\s*solid\s*#FF6233/i', 'Active link border-left 3px solid #FF6233');

echo "\n--- VERIFYING JS IMPLEMENTATION ---\n";
assertRule($js, '/setActiveToc/i', 'JS setActiveToc function present');
assertRule($js, '/calculateActiveSection/i', 'JS calculateActiveSection present');
assertRule($js, '/offsetThreshold\s*=\s*headerHeight\s*\+\s*35/i', 'JS reading threshold calculation');
assertRule($js, '/isClickScrolling/i', 'JS click scrolling lock present');
assertRule($js, '/container\.scrollTo/i', 'JS auto-scrolls TOC list when active item changes');

echo "\n--- VERIFYING LIVE BLOG PAGES ---\n";
foreach ([1, 2, 3] as $bId) {
    $res = getUrl("/blog/{$bId}");
    echo "Blog /blog/{$bId} status: {$res['code']}\n";
    if ($res['code'] === 200) {
        $hasToc = strpos($res['body'], 'blog-toc-card') !== false;
        $hasDesktopList = strpos($res['body'], 'desktop-toc-list') !== false;
        $hasMobileToc = strpos($res['body'], 'mobile-toc-container') !== false;
        $hasAnchors = preg_match('/id="heading-\d+-[^"]+"/i', $res['body']);
        $hasAuthorWidget = strpos($res['body'], 'blog-author-widget') !== false;
        $hasCtaWidget = strpos($res['body'], 'blog-cta-widget') !== false;
        echo "  - TOC Card: " . ($hasToc ? "PASS" : "FAIL") . "\n";
        echo "  - Desktop TOC List: " . ($hasDesktopList ? "PASS" : "FAIL") . "\n";
        echo "  - Mobile TOC: " . ($hasMobileToc ? "PASS" : "FAIL") . "\n";
        echo "  - Dynamic Anchors: " . ($hasAnchors ? "PASS" : "FAIL") . "\n";
        echo "  - Author Widget: " . ($hasAuthorWidget ? "PASS" : "FAIL") . "\n";
        echo "  - CTA Widget: " . ($hasCtaWidget ? "PASS" : "FAIL") . "\n";
    }
}

function assertRule($content, $regex, $label) {
    if (preg_match($regex, $content)) {
        echo "  [PASS] {$label}\n";
    } else {
        echo "  [FAIL] {$label}\n";
    }
}
