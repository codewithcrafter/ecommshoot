<?php
/**
 * ECOMSHOOT - Database Connection Layer (PDO Singleton)
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            // Log error safely without exposing raw database password to user
            error_log('Database Connection Error: ' . $e->getMessage());
            die('A database connection error occurred. Please ensure MySQL is running and configured.');
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    // Helper shorthand for prepared queries
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $res = $this->query($sql, $params)->fetch();
        return $res ?: null;
    }

    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }
}
