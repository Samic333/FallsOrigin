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
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Log error internally if needed, then set mock mode
            $this->is_mock = true;
            $this->pdo = null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Proxies methods to PDO or returns mock results.
     */
    public function prepare($sql) {
        if ($this->is_mock) return new MockPDOStatement();
        return $this->pdo->prepare($sql);
    }

    public function query($sql) {
        if ($this->is_mock) return new MockPDOStatement();
        return $this->pdo->query($sql);
    }

    public function lastInsertId() {
        if ($this->is_mock) return "MOCK-" . uniqid();
        return $this->pdo->lastInsertId();
    }

    public function isConnected() {
        return !$this->is_mock;
    }
}

/**
 * Mock PDO Statement for Graceful Failures
 */
class MockPDOStatement {
    public function execute($params = []) { return true; }
    public function fetch() { return false; }
    public function fetchAll() { return []; }
    public function fetchColumn() { return 0; }
    public function rowCount() { return 0; }
}
?>
