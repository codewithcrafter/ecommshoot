<?php
/**
 * ECOMSHOOT - Authentication & Session Security Service
 */

class Auth {
    private static string $sessionKey = 'ecomshoot_admin_session';

    public static function login(string $email, string $password): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute(['email' => trim($email)]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return false;
        }

        if (password_verify($password, $admin['password_hash'])) {
            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION[self::$sessionKey] = [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'email' => $admin['email'],
                'role' => $admin['role'],
                'logged_at' => time()
            ];

            // Update last login timestamp
            $upd = $db->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = :id");
            $upd->execute(['id' => $admin['id']]);

            return true;
        }

        return false;
    }

    public static function logout(): void {
        unset($_SESSION[self::$sessionKey]);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function check(): bool {
        return isset($_SESSION[self::$sessionKey]['id']);
    }

    public static function user(): ?array {
        return $_SESSION[self::$sessionKey] ?? null;
    }

    public static function id(): ?int {
        return $_SESSION[self::$sessionKey]['id'] ?? null;
    }

    public static function role(): string {
        return $_SESSION[self::$sessionKey]['role'] ?? 'guest';
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            set_flash('error', 'Please log in to access the administration panel.');
            redirect('admin/login.php');
        }
    }

    public static function requireRole(string $role): void {
        self::requireLogin();
        if (self::role() !== $role && self::role() !== 'admin') {
            set_flash('error', 'You do not have permission to access that resource.');
            redirect('admin/index.php');
        }
    }
}

