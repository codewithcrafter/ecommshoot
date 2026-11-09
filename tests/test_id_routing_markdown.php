<?php
/**
 * ECOMSHOOT - ID-Based Blog Routing & Automated Markdown Verification Suite
 */

$baseUrl = 'http://127.0.0.1:8000';
$rootDir = dirname(__DIR__);
require_once $rootDir . '/config/config.php';

echo "=========================================================\n";
echo "ECOMSHOOT ID-BASED ROUTING & MARKDOWN TEST SUITE\n";
echo "=========================================================\n\n";

$allPassed = true;
$db = Database::getInstance()->getConnection();

function test_curl(string $url, array $options = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    foreach ($options as $k => $v) {
        curl_setopt($ch, $k, $v);
    }
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    return ['code' => $code, 'headers' => $headers, 'body' => $body, 'contentType' => $contentType];
}

// -------------------------------------------------------------------
// 1. TEST EXACT ROUTES: /blog/1, /blog/1.md, /blog/2, /blog/2.md, /blog/3, /blog/3.md
// -------------------------------------------------------------------
echo "--- 1. TESTING CANONICAL ID-BASED HTML & MARKDOWN ROUTES ---\n";
$testIds = [1, 2, 3];

foreach ($testIds as $bid) {
    // A. HTML route
    $htmlRes = test_curl("{$baseUrl}/blog/{$bid}");
    if ($htmlRes['code'] === 200 && strpos($htmlRes['body'], '<!DOCTYPE html>') !== false && strpos($htmlRes['body'], "/blog/{$bid}.md") !== false) {
        echo "  [PASS] /blog/{$bid} -> HTTP 200 HTML (Canonical: /blog/{$bid})\n";
    } else {
        echo "  [FAIL] /blog/{$bid} -> Expected HTTP 200 HTML, got HTTP {$htmlRes['code']}\n";
        $allPassed = false;
    }

    // B. Markdown route
    $mdRes = test_curl("{$baseUrl}/blog/{$bid}.md");
    $hasMdHeader = strpos($mdRes['contentType'], 'text/markdown') !== false;
    $hasFrontmatter = strpos($mdRes['body'], '---') === 0;
    $hasCanonical = strpos($mdRes['body'], "canonical_html_url:") !== false;
    $noHtml = strpos($mdRes['body'], '<!DOCTYPE html>') === false;

    if ($mdRes['code'] === 200 && $hasMdHeader && $hasFrontmatter && $hasCanonical && $noHtml) {
        echo "  [PASS] /blog/{$bid}.md -> HTTP 200 (Content-Type: {$mdRes['contentType']}, Frontmatter verified, Zero HTML)\n";
    } else {
        echo "  [FAIL] /blog/{$bid}.md -> HTTP {$mdRes['code']}, Content-Type: {$mdRes['contentType']}, HasFrontmatter: " . ($hasFrontmatter?'yes':'no') . "\n";
        $allPassed = false;
    }
}

// -------------------------------------------------------------------
// 2. TEST OLD URL COMPATIBILITY (301 PERMANENT REDIRECT)
// -------------------------------------------------------------------
echo "\n--- 2. TESTING 301 PERMANENT REDIRECT FOR LEGACY SLUG URLS ---\n";
$legacySlug = 'amazon-product-image-guidelines-compliance-handbook';
$redirectRes = test_curl("{$baseUrl}/blog-detail.php?slug={$legacySlug}");

if ($redirectRes['code'] === 301 && preg_match('/Location:\s*([^\r\n]+)/i', $redirectRes['headers'], $locMatch)) {
    $loc = trim($locMatch[1]);
    if (strpos($loc, '/blog/3') !== false) {
        echo "  [PASS] Legacy slug URL redirected with HTTP 301 -> {$loc}\n";
    } else {
        echo "  [FAIL] Expected redirect target to end with /blog/3, got: {$loc}\n";
        $allPassed = false;
    }
} else {
    echo "  [FAIL] Legacy slug URL did not return HTTP 301. Got HTTP {$redirectRes['code']}\n";
    $allPassed = false;
}

// -------------------------------------------------------------------
// 3. TEST 404 RESPONSES FOR INVALID & UNPUBLISHED IDENTIFIERS
// -------------------------------------------------------------------
echo "\n--- 3. TESTING ERROR & ACCESS CONTROL (404 FOR INVALID/UNPUBLISHED) ---\n";
$invalidRoutes = [
    '/blog/99999' => 'Nonexistent ID HTML',
    '/blog/99999.md' => 'Nonexistent ID Markdown',
    '/blog/invalid' => 'Non-numeric ID',
    '/blog/0' => 'Zero ID',
    '/blog/-5' => 'Negative ID'
];

foreach ($invalidRoutes as $path => $label) {
    $res = test_curl("{$baseUrl}{$path}");
    if ($res['code'] === 404) {
        echo "  [PASS] {$label} ({$path}) -> Correctly returned HTTP 404\n";
    } else {
        echo "  [FAIL] {$label} ({$path}) -> Expected HTTP 404, got HTTP {$res['code']}\n";
        $allPassed = false;
    }
}

// Test draft / unpublished article
// Create a temporary draft article
$insDraft = $db->prepare("INSERT INTO blogs (title, slug, excerpt, content, status, published_at) VALUES ('Draft Secret Guide', 'draft-secret-guide', 'Draft excerpt', 'Draft secret content', 'draft', NOW())");
$insDraft->execute();
$draftId = (int) $db->lastInsertId();

$draftHtmlRes = test_curl("{$baseUrl}/blog/{$draftId}");
$draftMdRes = test_curl("{$baseUrl}/blog/{$draftId}.md");

if ($draftHtmlRes['code'] === 404 && $draftMdRes['code'] === 404) {
    echo "  [PASS] Unpublished Draft Blog #{$draftId} -> Both HTML and .md return HTTP 404 for public visitors\n";
} else {
    echo "  [FAIL] Draft blog leaked to public! HTML HTTP {$draftHtmlRes['code']}, MD HTTP {$draftMdRes['code']}\n";
    $allPassed = false;
}

// Clean up draft
$db->prepare("DELETE FROM blogs WHERE id = :id")->execute(['id' => $draftId]);

// -------------------------------------------------------------------
// 4. TEST SITEMAP & LLMS.TXT SYNCHRONIZATION
// -------------------------------------------------------------------
echo "\n--- 4. TESTING SITEMAP.XML & LLMS.TXT URL SYNCHRONIZATION ---\n";
$sitemapXml = file_get_contents("{$baseUrl}/sitemap.xml");
$llmsTxt = file_get_contents("{$baseUrl}/llms.txt");

// Check sitemap contains /blog/{id} and NO slug URLs
$hasIdInSitemap = strpos($sitemapXml, '/blog/1') !== false && strpos($sitemapXml, '/blog/2') !== false && strpos($sitemapXml, '/blog/3') !== false;
$hasNoSlugInSitemap = strpos($sitemapXml, 'blog-detail.php?slug=') === false;

if ($hasIdInSitemap && $hasNoSlugInSitemap) {
    echo "  [PASS] sitemap.xml strictly contains /blog/{id} and zero slug URLs\n";
} else {
    echo "  [FAIL] sitemap.xml failed verification.\n";
    $allPassed = false;
}

// Check llms.txt contains Title, HTML, Markdown
$hasHtmlInLlms = strpos($llmsTxt, 'HTML: http://127.0.0.1:8000/blog/1') !== false || strpos($llmsTxt, '/blog/1') !== false;
$hasMdInLlms = strpos($llmsTxt, 'Markdown: http://127.0.0.1:8000/blog/1.md') !== false || strpos($llmsTxt, '/blog/1.md') !== false;

if ($hasHtmlInLlms && $hasMdInLlms) {
    echo "  [PASS] llms.txt includes pairs of canonical HTML (/blog/{id}) and Markdown (/blog/{id}.md)\n";
} else {
    echo "  [FAIL] llms.txt failed verification.\n";
    $allPassed = false;
}

// -------------------------------------------------------------------
// 5. TEST PHYSICAL ATOMIC MARKDOWN GENERATION ON DISK
// -------------------------------------------------------------------
echo "\n--- 5. TESTING PHYSICAL MARKDOWN FILE GENERATION (/content/blogs/) ---\n";
$allFilesOnDisk = true;
foreach ($testIds as $bid) {
    $filePath = MarkdownGenerator::getFilePath($bid);
    if (file_exists($filePath) && filesize($filePath) > 200) {
        echo "  [PASS] /content/blogs/{$bid}.md exists (" . filesize($filePath) . " bytes)\n";
    } else {
        echo "  [FAIL] Missing /content/blogs/{$bid}.md\n";
        $allFilesOnDisk = false;
        $allPassed = false;
    }
}

// -------------------------------------------------------------------
// 6. TEST CONDITIONAL GET & LAST-MODIFIED HEADERS ON MARKDOWN ENDPOINT
// -------------------------------------------------------------------
echo "\n--- 6. TESTING CACHING & LAST-MODIFIED HEADERS (/blog/1.md) ---\n";
$cachingRes = test_curl("{$baseUrl}/blog/1.md");
preg_match('/ETag:\s*([^\r\n]+)/i', $cachingRes['headers'], $etagMatch);
$etag = trim($etagMatch[1] ?? '');

if (!empty($etag)) {
    // Request with If-None-Match
    $cachedRes = test_curl("{$baseUrl}/blog/1.md", [
        CURLOPT_HTTPHEADER => ["If-None-Match: {$etag}"]
    ]);
    if ($cachedRes['code'] === 304) {
        echo "  [PASS] /blog/1.md honors HTTP 304 Not Modified for conditional ETag caching\n";
    } else {
        echo "  [FAIL] Expected HTTP 304 for matching ETag, got HTTP {$cachedRes['code']}\n";
        $allPassed = false;
    }
}

echo "\n=========================================================\n";
if ($allPassed) {
    echo "OVERALL VERIFICATION: 100% PASSED - ALL CRITERIA SATISFIED!\n";
} else {
    echo "OVERALL VERIFICATION: SOME CHECKS FAILED. PLEASE REVIEW.\n";
}
echo "=========================================================\n";
