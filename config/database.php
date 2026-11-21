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
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            global $isLocalDev;
            $connected = false;

            if (!empty($isLocalDev)) {
                // If local port 3306 or db name failed, try port 3307 or alternative DB name
                $altPorts = [3306, 3307];
                $altDbs = [DB_NAME, 'ecomshoot_db', 'ecommshoot'];
                foreach ($altPorts as $p) {
                    foreach (array_unique($altDbs) as $d) {
                        if ($p == DB_PORT && $d == DB_NAME) continue;
                        try {
                            $altDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, $p, $d, DB_CHARSET);
                            $this->connection = new PDO($altDsn, DB_USER, DB_PASS, $options);
                            $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                            $connected = true;
                            break 2;
                        } catch (PDOException $ex) {}
                    }
                }
            } else {
                // If on Hostinger production, try srv1328.hstgr.io if localhost failed (or vice-versa)
                $altHost = (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') ? 'srv1328.hstgr.io' : 'localhost';
                try {
                    $altDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $altHost, DB_PORT, DB_NAME, DB_CHARSET);
                    $this->connection = new PDO($altDsn, DB_USER, DB_PASS, $options);
                    $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $connected = true;
                } catch (PDOException $ex) {}

                // Resilient fallback to local dev credentials if server is running on a local workstation
                if (!$connected && (
                    (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1'])) ||
                    (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) ||
                    php_sapi_name() === 'cli'
                )) {
                    try {
                        $localDsn = 'mysql:host=127.0.0.1;port=3306;dbname=ecomshoot_db;charset=utf8mb4';
                        $this->connection = new PDO($localDsn, 'root', '', $options);
                        $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $connected = true;
                    } catch (PDOException $ex) {}
                }
            }

            if (!$connected) {
                error_log('Database Connection Error: ' . $e->getMessage());
                die('A database connection error occurred. Please ensure MySQL is running and configured.');
            }
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
