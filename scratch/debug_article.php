<?php
$html = file_get_contents('http://127.0.0.1:8000/blog-detail.php?slug=how-product-photography-boosts-shopify-conversions');
echo "HTML Length: " . strlen($html) . "\n";
echo "Tail 500 chars:\n" . substr($html, -500) . "\n";

