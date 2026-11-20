<?php
/**
 * ECOMSHOOT - Public Raw Markdown Article Endpoint
 * Serves public Markdown routes as readable inline text.
 */

require_once __DIR__ . '/config/config.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$slug = trim((string) ($_GET['slug'] ?? ''));

if ((!$id || $id <= 0) && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "404 Not Found: Invalid article identifier.";
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $id
    ? $db->prepare("SELECT id, title, updated_at, status, markdown_content FROM blogs WHERE id = :id LIMIT 1")
    : $db->prepare("SELECT id, title, updated_at, status, markdown_content FROM blogs WHERE slug = :slug LIMIT 1");
$stmt->execute($id ? ['id' => $id] : ['slug' => $slug]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "404 Not Found: Article does not exist.";
    exit;
}

// Ensure article is published
if ($blog['status'] !== 'published') {
    // If not admin previewing, reject with 404
    if (!Auth::check()) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "404 Not Found: Article is not publicly published.";
        exit;
    }
}

// Retrieve markdown content from /content/blogs/{id}.md or generate dynamically
$blogId = (int) $blog['id'];
$markdown = MarkdownGenerator::getFileContent($blogId);
if ($markdown === null) {
    if (MarkdownGenerator::generate($blogId)) {
        $markdown = MarkdownGenerator::getFileContent($blogId);
    }
}

if ($markdown === null) {
    // Fallback to database markdown copy if available
    $markdown = $blog['markdown_content'] ?: "# " . $blog['title'] . "\n\nContent currently unavailable.";
}

// HTTP Caching & Conditional GET Headers
$modTime = !empty($blog['updated_at']) ? strtotime($blog['updated_at']) : time();
$etag = '"' . md5($id . '-' . $modTime) . '"';

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
    http_response_code(304);
    exit;
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modTime) {
    http_response_code(304);
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
header('Content-Disposition: inline; filename="article-' . (int) $blog['id'] . '.md"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $modTime));
header('ETag: ' . $etag);
header('Content-Length: ' . strlen($markdown));

echo $markdown;
exit;

