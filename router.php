<?php
/**
 * ECOMSHOOT - Router script for PHP Built-In Web Server
 * Emulates Apache mod_rewrite rules for /blog/{id} and /blog/{id}.md
 */

$rawUri = $_SERVER['REQUEST_URI'];
$uri = parse_url($rawUri, PHP_URL_PATH);

// Normalize URI
$uri = '/' . ltrim($uri, '/');

// 2. Direct ID-based Blog Markdown: /blog/{id}.md
if (preg_match('#^/blog/([0-9]+)\.md$#', $uri, $matches)) {
    $_GET['id'] = (int) $matches[1];
    require __DIR__ . '/blog-markdown.php';
    exit;
}

// 3. Direct ID-based Blog HTML: /blog/{id}
if (preg_match('#^/blog/([0-9]+)/?$#', $uri, $matches)) {
    $_GET['id'] = (int) $matches[1];
    require __DIR__ . '/blog-detail.php';
    exit;
}

// 4. Blog listing: /blog or /blog/
if ($uri === '/blog' || $uri === '/blog/') {
    require __DIR__ . '/blog.php';
    exit;
}

// 1. Direct physical static files (CSS, JS, images, svgs, etc.)
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        require $filePath;
        exit;
    }
    return false;
}

// 5. Normal index fallback
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    exit;
}

// 6. Direct PHP script without extension or standard resolution
if (file_exists($filePath . '.php')) {
    require $filePath . '.php';
    exit;
}

http_response_code(404);
echo "404 Not Found";
exit;
