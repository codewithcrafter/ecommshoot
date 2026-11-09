<?php
/**
 * ECOMSHOOT - Cross-Site Request Forgery (CSRF) Protection Service
 */

class CSRF {
    private static string $sessionTokenKey = 'ecomshoot_csrf_token';

    public static function getToken(): string {
        if (empty($_SESSION[self::$sessionTokenKey])) {
            $_SESSION[self::$sessionTokenKey] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$sessionTokenKey];
    }

    public static function validateToken(?string $token): bool {
        if (empty($token) || empty($_SESSION[self::$sessionTokenKey])) {
            return false;
        }
        return hash_equals($_SESSION[self::$sessionTokenKey], $token);
    }

    public static function inputField(): string {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }

    public static function check(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!self::validateToken($token)) {
                http_response_code(403);
                die('CSRF token validation failed. Please refresh the page and try again.');
            }
        }
    }
}

