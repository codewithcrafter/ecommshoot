<?php
/**
 * ECOMSHOOT - Universal Markdown Representation Endpoint
 * Serves public Markdown documentation, pages, services, and blogs as clean inline plain text.
 * Prevents redirects when accessing .md URLs directly (e.g. /about.md, /contact.md, /services.md).
 */

require_once __DIR__ . '/config/config.php';

$rawPath = $_GET['path'] ?? $_GET['slug'] ?? '';
$rawPath = trim($rawPath, '/');
$rawPath = preg_replace('/\.md$/i', '', $rawPath);

// Also extract from REQUEST_URI if path query param wasn't set (direct invocation)
if (empty($rawPath) && !empty($_SERVER['REQUEST_URI'])) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestPath = trim($requestPath, '/');
    if (preg_match('/\.md$/i', $requestPath)) {
        $rawPath = preg_replace('/\.md$/i', '', $requestPath);
    }
}

if (empty($rawPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "404 Not Found: No markdown document specified.";
    exit;
}

$segments = explode('/', $rawPath);
$type = count($segments) > 1 ? strtolower($segments[0]) : '';
$slug = strtolower(end($segments));

$content = null;
$filename = $slug . '.md';
$modTime = time();

// 1. Check Blog Markdown: /blog/{slug}.md or /blog/{id}.md
if ($type === 'blog' || is_numeric($slug)) {
    try {
        $db = Database::getInstance()->getConnection();
        $isNumeric = is_numeric($slug);
        $stmt = $isNumeric
            ? $db->prepare("SELECT id, slug, title, updated_at, status, markdown_content FROM blogs WHERE id = :id LIMIT 1")
            : $db->prepare("SELECT id, slug, title, updated_at, status, markdown_content FROM blogs WHERE slug = :slug LIMIT 1");
        $stmt->execute($isNumeric ? ['id' => (int)$slug] : ['slug' => $slug]);
        $blog = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($blog && ($blog['status'] === 'published' || Auth::check())) {
            $blogId = (int)$blog['id'];
            $content = MarkdownGenerator::getFileContent($blogId);
            if ($content === null) {
                if (MarkdownGenerator::generate($blogId)) {
                    $content = MarkdownGenerator::getFileContent($blogId);
                }
            }
            if ($content === null && !empty($blog['markdown_content'])) {
                $content = $blog['markdown_content'];
            }
            $filename = ($blog['slug'] ?: 'blog-' . $blogId) . '.md';
            $modTime = !empty($blog['updated_at']) ? strtotime($blog['updated_at']) : time();
        }
    } catch (Exception $e) {}
}

// 2. Check Service Markdown: /service/{slug}.md or /{service-slug}.md
if ($content === null && ($type === 'service' || $type === 'services' || in_array($slug, [
    'product-photography',
    'ecommerce-product-photography',
    'lifestyle-product-photography',
    'fashion-product-photography',
    'creative-product-photography',
    'jewellery-product-photography',
    'product-photo-editing-retouching',
    'white-background-product-photography',
    'professional-product-photography'
]))) {
    $serviceFile = ROOT_PATH . '/content/services/' . $slug . '.md';
    if (file_exists($serviceFile)) {
        $content = file_get_contents($serviceFile);
        $modTime = filemtime($serviceFile);
        $filename = $slug . '.md';
    }
}

// 3. Check Page Markdown: /about.md, /contact.md, /services.md, /home.md
if ($content === null) {
    $pageAliases = [
        'about' => 'about.md',
        'contact' => 'contact.md',
        'contact-guidelines' => 'contact-guidelines.md',
        'services' => 'services.md',
        'services-catalog' => 'services-catalog.md',
        'home' => 'home.md',
        'index' => 'home.md'
    ];

    $targetFile = $pageAliases[$slug] ?? ($slug . '.md');
    $pagePath = ROOT_PATH . '/content/pages/' . $targetFile;

    // Fallbacks for known aliases
    if (!file_exists($pagePath)) {
        if ($slug === 'contact') {
            $pagePath = ROOT_PATH . '/content/pages/contact-guidelines.md';
        } elseif ($slug === 'services') {
            $pagePath = ROOT_PATH . '/content/pages/services-catalog.md';
        } elseif ($slug === 'index') {
            $pagePath = ROOT_PATH . '/content/pages/home.md';
        }
    }

    if (file_exists($pagePath)) {
        $content = file_get_contents($pagePath);
        $modTime = filemtime($pagePath);
        $filename = $slug . '.md';
    }
}

// 4. Check database markdown_documents table as authoritative fallback
if ($content === null) {
    try {
        $db = Database::getInstance()->getConnection();
        $dbAliases = [
            'contact' => 'contact-guidelines',
            'services' => 'services-catalog',
            'index' => 'home'
        ];
        $searchSlug = $dbAliases[$slug] ?? $slug;

        $stmt = $db->prepare("SELECT content, updated_at FROM markdown_documents WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $searchSlug]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($doc && !empty($doc['content'])) {
            $content = $doc['content'];
            $modTime = !empty($doc['updated_at']) ? strtotime($doc['updated_at']) : time();
            $filename = $slug . '.md';
        }
    } catch (Exception $e) {}
}

// 5. If still not found, return clean 404 text (no redirect to home)
if ($content === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "404 Not Found: The requested markdown document '{$slug}.md' was not found.";
    exit;
}

// HTTP Caching & Conditional GET Headers
$etag = '"' . md5($filename . '-' . $modTime) . '"';

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
    http_response_code(304);
    exit;
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modTime) {
    http_response_code(304);
    exit;
}

// Stream Markdown Content inline in the browser
header('Content-Type: text/plain; charset=UTF-8');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $modTime));
header('ETag: ' . $etag);
header('Content-Length: ' . strlen($content));

echo $content;
exit;
