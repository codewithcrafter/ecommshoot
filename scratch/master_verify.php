<?php
/**
 * Master Verification Test
 */
$cookie = __DIR__ . '/test_cookie.txt';

echo "=== 1. VERIFY ADMIN LOGIN PAGE RENDERING ===\n";
$loginHtml = file_get_contents('http://127.0.0.1:8085/admin/login.php');
$hasAdminCss = (strpos($loginHtml, 'admin.css') !== false);
$hasNoStyleCss = (strpos($loginHtml, 'style.css') === false);
$hasLoginTitle = (strpos($loginHtml, 'Studio Staff Portal') !== false);
echo "Admin CSS Linked: " . ($hasAdminCss ? 'PASS' : 'FAIL') . "\n";
echo "No Style.css Clash: " . ($hasNoStyleCss ? 'PASS' : 'FAIL') . "\n";
echo "Title Rendered: " . ($hasLoginTitle ? 'PASS' : 'FAIL') . "\n";

echo "\n=== 2. VERIFY ADMIN MARKDOWN CMS ===\n";
$ch = curl_init('http://127.0.0.1:8085/admin/markdown.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$mdHtml = curl_exec($ch);
curl_close($ch);
$hasDocs = (strpos($mdHtml, 'ecommerce-product-photography') !== false);
echo "Markdown Documents in Admin: " . ($hasDocs ? 'PASS' : 'FAIL') . "\n";

echo "\n=== 3. VERIFY ADMIN SCHEMAS PAGE ===\n";
$ch = curl_init('http://127.0.0.1:8085/admin/schemas.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$schemaHtml = curl_exec($ch);
curl_close($ch);
$hasSchemas = (strpos($schemaHtml, 'PhotographyStudio') !== false && strpos($schemaHtml, 'AboutPage') !== false);
echo "Schemas in Admin: " . ($hasSchemas ? 'PASS' : 'FAIL') . "\n";

echo "\n=== 4. VERIFY PUBLIC PAGE SCHEMAS (ABOUT) ===\n";
$aboutHtml = file_get_contents('http://127.0.0.1:8085/about');
$hasAboutSchema = (strpos($aboutHtml, '"@type": "AboutPage"') !== false || strpos($aboutHtml, '"@type":"AboutPage"') !== false);
$hasBreadcrumb = (strpos($aboutHtml, '"@type": "BreadcrumbList"') !== false || strpos($aboutHtml, '"@type":"BreadcrumbList"') !== false);
echo "AboutPage Schema in Head: " . ($hasAboutSchema ? 'PASS' : 'FAIL') . "\n";
echo "BreadcrumbList in Head: " . ($hasBreadcrumb ? 'PASS' : 'FAIL') . "\n";

echo "\n=== 5. VERIFY SERVICE PAGE SCHEMAS (ECOMMERCE) ===\n";
$svcHtml = file_get_contents('http://127.0.0.1:8085/service/ecommerce-product-photography');
$hasSvcSchema = (strpos($svcHtml, '"@type": "Service"') !== false);
$hasFaqSchema = (strpos($svcHtml, '"@type": "FAQPage"') !== false);
echo "Service Schema in Head: " . ($hasSvcSchema ? 'PASS' : 'FAIL') . "\n";
echo "FAQPage Schema in Head: " . ($hasFaqSchema ? 'PASS' : 'FAIL') . "\n";

echo "\n=== 6. VERIFY RAW MARKDOWN ENDPOINTS ===\n";
$ch = curl_init('http://127.0.0.1:8085/blog/how-product-photography-boosts-shopify-conversions.md');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$rawMd = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$hasFrontmatter = (strpos($rawMd, '---') === 0);
echo "Blog .md HTTP Status: $code (" . ($code === 200 ? 'PASS' : 'FAIL') . ")\n";
echo "Frontmatter Present: " . ($hasFrontmatter ? 'PASS' : 'FAIL') . "\n";

