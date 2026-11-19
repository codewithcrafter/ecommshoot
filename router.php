<?php
/**
 * ECOMSHOOT - Router script for PHP Built-In Web Server
 * Handles clean SEO routes:
 * - Blog articles: /blog/{slug} (canonical) and /blog/{slug}.md
 * - Blog numeric ID legacy redirects: /blog/{id} -> /blog/{slug} (301)
 * - Service pages: /service/{slug} and /services/{slug}
 * - Legacy service redirects: /{service-slug} -> /service/{service-slug} (301)
 * - Services catalog: /services
 * - Blog catalog: /blog
 */

$rawUri = $_SERVER['REQUEST_URI'];
$uri = parse_url($rawUri, PHP_URL_PATH);

// Normalize URI
$uri = '/' . ltrim($uri, '/');

// Known published service slugs
$serviceSlugs = [
    'product-photography',
    'ecommerce-product-photography',
    'lifestyle-product-photography',
    'fashion-product-photography',
    'creative-product-photography',
    'jewellery-product-photography',
    'product-photo-editing-retouching',
    'white-background-product-photography',
];

// 1. Discontinued service canonical redirect (both .php and extensionless)
if (preg_match('#^/(?:services?/)?professional-product-photography(\.php)?/?$#i', $uri)) {
    header('Location: /service/product-photography', true, 301);
    exit;
}

// 2. Canonicalize legacy public PHP URLs without changing POST endpoint behavior.
if (preg_match('#^/(.+)\.php$#i', $uri, $legacyMatch) && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    $target = $legacyMatch[1];
    $query = parse_url($rawUri, PHP_URL_QUERY);
    $qStr = $query ? '?' . $query : '';

    if ($target === 'index') {
        header('Location: /' . $qStr, true, 301);
        exit;
    }
    if (in_array($target, $serviceSlugs, true)) {
        header('Location: /service/' . $target . $qStr, true, 301);
        exit;
    }
    header('Location: /' . $target . $qStr, true, 301);
    exit;
}

// 3. Homepage SEO alias
if ($uri === '/product-photography-agency-delhi' || $uri === '/product-photography-agency-delhi/') {
    require __DIR__ . '/index.php';
    exit;
}

// 4. Service Detail Routes: /service/{slug} and /services/{slug}
if (preg_match('#^/(?:service|services)/([a-z0-9-]+)/?$#i', $uri, $matches)) {
    $slug = strtolower($matches[1]);
    if (in_array($slug, $serviceSlugs, true) && file_exists(__DIR__ . '/' . $slug . '.php')) {
        require __DIR__ . '/' . $slug . '.php';
        exit;
    }
}

// 5. Legacy Root Service URLs: /{service-slug} -> 301 Redirect to /service/{service-slug}
if (preg_match('#^/([a-z0-9-]+)/?$#i', $uri, $matches)) {
    $slug = strtolower($matches[1]);
    if (in_array($slug, $serviceSlugs, true)) {
        $query = parse_url($rawUri, PHP_URL_QUERY);
        header('Location: /service/' . $slug . ($query ? '?' . $query : ''), true, 301);
        exit;
    }
}

// 6. Services Index Catalog: /services or /services/
if ($uri === '/services' || $uri === '/services/') {
    require __DIR__ . '/services.php';
    exit;
}

// 7. Blog Markdown Routes: /blog/{slug}.md or /blog/{id}.md
if (preg_match('#^/blog/([0-9]+)\.md$#', $uri, $matches)) {
    require_once __DIR__ . '/config/config.php';
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT slug FROM blogs WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$matches[1]]);
        $slug = $stmt->fetchColumn();
        if ($slug) {
            header('Location: /blog/' . $slug . '.md', true, 301);
            exit;
        }
    } catch (Exception $e) {}
    $_GET['id'] = (int) $matches[1];
    require __DIR__ . '/blog-markdown.php';
    exit;
}

if (preg_match('#^/blog/([a-z0-9]+(?:-[a-z0-9]+)*)\.md$#i', $uri, $matches)) {
    $_GET['slug'] = strtolower($matches[1]);
    require __DIR__ . '/blog-markdown.php';
    exit;
}

// 8. Blog HTML by ID -> 301 Permanent Redirect to /blog/{slug}
if (preg_match('#^/blog/([0-9]+)/?$#', $uri, $matches)) {
    require_once __DIR__ . '/config/config.php';
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT slug FROM blogs WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$matches[1]]);
        $slug = $stmt->fetchColumn();
        if ($slug) {
            header('Location: /blog/' . $slug, true, 301);
            exit;
        }
    } catch (Exception $e) {}
    $_GET['id'] = (int) $matches[1];
    require __DIR__ . '/blog-detail.php';
    exit;
}

// 9. Blog HTML by Slug: /blog/{slug}
if (preg_match('#^/blog/([a-z0-9]+(?:-[a-z0-9]+)*)/?$#i', $uri, $matches)) {
    $_GET['slug'] = strtolower($matches[1]);
    require __DIR__ . '/blog-detail.php';
    exit;
}

// 10. Blog Listing: /blog or /blog/
if ($uri === '/blog' || $uri === '/blog/') {
    require __DIR__ . '/blog.php';
    exit;
}

// 11. Direct physical static files (CSS, JS, images, svgs, etc.)
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        require $filePath;
        exit;
    }
    return false;
}

// 12. Normal index fallback
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    exit;
}

// 13. Direct PHP script without extension or standard resolution
$extensionlessPath = rtrim($filePath, '/\\');
if (file_exists($extensionlessPath . '.php')) {
    require $extensionlessPath . '.php';
    exit;
}

http_response_code(404);
echo "404 Not Found";
exit;
