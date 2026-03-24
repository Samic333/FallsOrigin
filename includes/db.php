<?php
require_once __DIR__ . '/config.php';

/**
 * DB Class - Resilience Layer
 * Handles database connectivity strictly. Enforces MySQL connection.
 */
class DB {
    private static $instance = null;
    private $pdo = null;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            error_log("CRITICAL DB ERROR: " . $e->getMessage());
            die("Service Temporarily Unavailable. Please check database connection settings in production.");
        }
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

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function isConnected() {
        return $this->pdo !== null;
    }
}
?>
