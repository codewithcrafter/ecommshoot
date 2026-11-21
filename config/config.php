<?php
/**
 * ECOMSHOOT - Global Configuration & Environment Settings
 * Premium E-Commerce Product Photography & Creative Production
 */

// Base Directory Definitions
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('SERVICES_PATH', ROOT_PATH . '/services');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('CONTENT_PATH', ROOT_PATH . '/content');

// Automatically detects local environment vs live Hostinger production (ecomshoots.in)
$isLocalDev = (isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
)) || (php_sapi_name() === 'cli' && !defined('PRODUCTION_MODE'));

// Strict error reporting in development, safe error suppression in production
if ($isLocalDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // In production, do not expose internal database/query errors to visitors
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Ensure session starts with secure parameters
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    // Configure secure session cookie defaults
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => $cookieParams['domain'],
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Database Configuration
//
// Local credentials live in the ignored project-root .env file. Environment
// variables take precedence so deployed environments can configure credentials
// without storing them in the repository.
function load_local_database_env(string $path): void {
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function database_env(string $key, string $default = ''): string {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

if ($isLocalDev) {
    // Local Development (XAMPP / PHP built-in server)
    load_local_database_env(ROOT_PATH . '/.env');
    define('DB_HOST', database_env('DB_HOST', '127.0.0.1'));
    define('DB_PORT', database_env('DB_PORT', '3306'));
    define('DB_NAME', database_env('DB_NAME', 'ecomshoot_db'));
    define('DB_USER', database_env('DB_USER', 'root'));
    define('DB_PASS', database_env('DB_PASS', ''));
    define('DB_CHARSET', database_env('DB_CHARSET', 'utf8mb4'));
} else {
    // Production (Hostinger - ecomshoots.in)
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'u555726154_ecommshoot');
    define('DB_USER', 'u555726154_ecommshootuser');
    define('DB_PASS', 'Ecomm@2026#');
    define('DB_CHARSET', 'utf8mb4');
}

// Base URL calculation (supports built-in php server, SSL proxies, and custom subdirectories)
if (php_sapi_name() === 'cli') {
    $scheme = 'https';
    $host = 'www.ecomshoots.in';
    $basePath = '';
} else {
    $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
               (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'www.ecomshoots.in';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/');
    if (substr($basePath, -6) === '/admin') {
        $basePath = substr($basePath, 0, -6);
    } elseif (substr($basePath, -8) === '/scratch') {
        $basePath = substr($basePath, 0, -8);
    } elseif (substr($basePath, -6) === '/tests') {
        $basePath = substr($basePath, 0, -6);
    }
}
define('BASE_URL', $scheme . '://' . $host . $basePath);

// Require essential architecture classes
require_once CONFIG_PATH . '/database.php';
require_once SERVICES_PATH . '/Auth.php';
require_once SERVICES_PATH . '/CSRF.php';
require_once SERVICES_PATH . '/SEO.php';
require_once SERVICES_PATH . '/SchemaManager.php';
require_once SERVICES_PATH . '/SEOGenerator.php';
require_once SERVICES_PATH . '/MarkdownParser.php';
require_once SERVICES_PATH . '/MarkdownGenerator.php';
require_once SERVICES_PATH . '/HtmlSanitizer.php';
require_once SERVICES_PATH . '/HtmlToMarkdown.php';

/**
 * Global helper: Escape HTML safely
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Global helper: Get asset URL with cache busting or base resolution
 */
function asset_url(string $path): string {
    $cleanPath = ltrim($path, '/');
    return BASE_URL . '/' . $cleanPath;
}

/**
 * Global helper: URL generator
 */
function url(string $path = ''): string {
    $cleanPath = ltrim($path, '/');
    // Public URLs use extensionless routes; router.php and .htaccess resolve them.
    $cleanPath = preg_replace('/\.php$/i', '', $cleanPath);
    return BASE_URL . ($cleanPath ? '/' . $cleanPath : '');
}

/**
 * Global helper: Canonical blog HTML URL by slug or ID
 */
function blog_url($idOrSlug): string {
    static $slugMap = null;
    if ($slugMap === null) {
        $slugMap = [];
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT id, slug FROM blogs");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slugMap[(int)$row['id']] = $row['slug'];
                $slugMap[$row['slug']] = $row['slug'];
            }
        } catch (Exception $e) {}
    }
    if (is_numeric($idOrSlug) && isset($slugMap[(int)$idOrSlug])) {
        return url('blog/' . $slugMap[(int)$idOrSlug]);
    }
    if (is_string($idOrSlug) && !empty($idOrSlug)) {
        return url('blog/' . $idOrSlug);
    }
    return url('blog/' . $idOrSlug);
}

/**
 * Global helper: Canonical blog Markdown URL by slug or ID
 */
function blog_md_url($idOrSlug): string {
    static $slugMap = null;
    if ($slugMap === null) {
        $slugMap = [];
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT id, slug FROM blogs");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slugMap[(int)$row['id']] = $row['slug'];
                $slugMap[$row['slug']] = $row['slug'];
            }
        } catch (Exception $e) {}
    }
    if (is_numeric($idOrSlug) && isset($slugMap[(int)$idOrSlug])) {
        return url('blog/' . $slugMap[(int)$idOrSlug] . '.md');
    }
    if (is_string($idOrSlug) && !empty($idOrSlug)) {
        return url('blog/' . $idOrSlug . '.md');
    }
    return url('blog/' . $idOrSlug . '.md');
}

/**
 * Global helper: Canonical service URL
 */
function service_url(string $slugOrFile): string {
    $slug = trim($slugOrFile);
    $slug = preg_replace('/\.php$/i', '', $slug);
    $slug = ltrim($slug, '/');
    if (str_starts_with($slug, 'service/')) {
        $slug = substr($slug, 8);
    } elseif (str_starts_with($slug, 'services/')) {
        $slug = substr($slug, 9);
    }
    return url('service/' . $slug);
}

/**
 * Global helper: Extract author initials (e.g. "Marcus Vance" -> "MV")
 */
function get_author_initials(?string $name): string {
    $trimmed = trim($name ?? '');
    if ($trimmed === '') {
        return 'ES';
    }
    $words = preg_split('/\s+/', $trimmed);
    if (count($words) >= 2) {
        return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1));
    }
    return mb_strtoupper(mb_substr($trimmed, 0, 2));
}

/**
 * Global helper: Redirect helper
 */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

/**
 * Global helper: Flash message management
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Global helper: Fetch setting from website_settings table with cache fallback
 */
function get_setting(string $key, string $default = ''): string {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $settingsCache = [];
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM website_settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $ex) {
            // Database might not be initialized yet
        }
    }
    return $settingsCache[$key] ?? $default;
}

