<?php
require_once __DIR__ . '/config.php';

/**
 * DB Class - Resilience Layer
 * Handles database connectivity with graceful fallback for local/misconfigured environments.
 */
class DB {
    private static $instance = null;
    private $pdo = null;
    private $is_mock = false;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        // Note: No more SQLite fallback. We are targeting the live Namecheap MySQL environment.
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    public function query($sql) {
        return $this->pdo->query($sql);
    }

    public function exec($sql) {
        return $this->pdo->exec($sql);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function isConnected() {
        return $this->pdo !== null;
    }
}
?>
