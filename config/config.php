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

// Automatically detects local environment vs live Hostinger production (ecommshoots.in)
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
if ($isLocalDev) {
    // Local Development (XAMPP / PHP built-in server)
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3307');
    define('DB_NAME', 'ecommshoot');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Production (Hostinger - ecommshoots.in)
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'u555726154_ecommshoot');
    define('DB_USER', 'u555726154_ecommshootuser');
    define('DB_PASS', 'PUT_YOUR_HOSTINGER_DB_PASSWORD_HERE'); // <--- ENTER YOUR HOSTINGER DATABASE PASSWORD HERE
    define('DB_CHARSET', 'utf8mb4');
}

// Base URL calculation (supports built-in php server and custom subdirectories)
if (php_sapi_name() === 'cli') {
    $scheme = 'http';
    $host = '127.0.0.1:8000';
    $basePath = '';
} else {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
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
    return BASE_URL . ($cleanPath ? '/' . $cleanPath : '');
}

/**
 * Global helper: Canonical blog HTML URL
 */
function blog_url(int $id): string {
    return url('blog/' . $id);
}

/**
 * Global helper: Canonical blog Markdown URL
 */
function blog_md_url(int $id): string {
    return url('blog/' . $id . '.md');
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

