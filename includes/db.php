<?php
require_once __DIR__ . '/config.php';

/**
 * DB Class - Resilience Layer
 * Handles database connectivity strictly. Enforces MySQL connection.
 */
class DB {
    private static $instance = null;
    private $pdo = null;
    private $connection_failed = false;

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
            $this->connection_failed = true;
            $this->pdo = null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function prepare($sql) {
        if ($this->connection_failed) return new NullStatement();
        return $this->pdo->prepare($sql);
    }

    public function query($sql) {
        if ($this->connection_failed) return new NullStatement();
        return $this->pdo->query($sql);
    }

    public function lastInsertId() {
        if ($this->connection_failed) return 0;
        return $this->pdo->lastInsertId();
    }

    public function isConnected() {
        return $this->pdo !== null;
    }
}

/**
 * Dummy statement to prevent errors on disconnected state
 */
class NullStatement {
    public function execute($params = []) { return false; }
    public function fetch() { return false; }
    public function fetchAll() { return []; }
    public function fetchColumn() { return 0; }
    public function rowCount() { return 0; }
}
?>
