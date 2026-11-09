<?php
$html = file_get_contents('http://127.0.0.1:8000/blog-detail.php?slug=how-product-photography-boosts-shopify-conversions');

$checks = [
    'Blogs Navigation' => strpos($html, '>Blogs</a>') !== false,
    'Official Email in Dispatch' => strpos($html, 'contact@ecommshoot.in') !== false,
    'Lead Popup Modal Present' => strpos($html, 'id="leadPopupOverlay"') !== false,
    'Lead Popup 5s Delay Script' => strpos($html, 'delayMs: 5000') !== false,
    'Assigned Tag on Article' => strpos($html, 'blog.php?tag=') !== false,
    'No Fake NY Facility in Top Bar' => strpos($html, 'New York Facility:') === false,
    'No Fake 555-SHOOT Phone in Top Bar' => strpos($html, '+1 (800) 555-SHOOT') === false,
    'UTF-8 Meta Present' => strpos($html, '<meta charset="UTF-8">') !== false
];

foreach ($checks as $title => $pass) {
    echo ($pass ? "[PASS] " : "[FAIL] ") . $title . "\n";
}
