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
            // Fallback to SQLite for persistent local development
            $db_dir = __DIR__ . '/../database';
            if (!is_dir($db_dir)) mkdir($db_dir, 0777, true);
            
            $sqlite_path = $db_dir . '/falls_coffee.db';
            $this->pdo = new PDO("sqlite:" . $sqlite_path);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->is_mock = true; // Still marked as mock to indicate local dev mode
            $this->initSQLiteSchema();
        }
    }

    private function initSQLiteSchema() {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
        if (!$stmt->fetch()) {
            $schemaFile = __DIR__ . '/../schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                
                // SQLite adjustments
                $sql = preg_replace('/INT\(.*?\)/i', 'INTEGER', $sql);
                $sql = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $sql);
                $sql = preg_replace('/ENGINE=InnoDB.*?;/i', ';', $sql);
                $sql = preg_replace('/`([a-zA-Z0-9_]+)`/i', '$1', $sql); // remove backticks
                $sql = preg_replace('/timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
                $sql = preg_replace('/timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
                $sql = preg_replace('/timestamp NULL DEFAULT NULL/i', 'DATETIME DEFAULT NULL', $sql);
                $sql = preg_replace('/INSERT IGNORE/i', 'INSERT OR IGNORE', $sql);
                
                // Remove MySQL specific headers
                $sql = preg_replace('/SET SQL_MODE.*?;/is', '', $sql);
                $sql = preg_replace('/START TRANSACTION;/is', '', $sql);
                $sql = preg_replace('/SET time_zone.*?;/is', '', $sql);

                try {
                    $this->pdo->exec($sql);
                } catch (\PDOException $e) {
                    // Fallback: manually create core tables if complex schema tool fails
                    $this->pdo->exec("CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, origin TEXT, type TEXT, price REAL, weight TEXT, description TEXT, image_url TEXT, stock_quantity INTEGER, is_active INTEGER)");
                    $this->pdo->exec("CREATE TABLE IF NOT EXISTS orders (id TEXT PRIMARY KEY, customer_name TEXT, customer_email TEXT, shipping_address TEXT, total REAL, status TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
                    $this->pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, subject TEXT, message TEXT, status TEXT DEFAULT 'Unread', created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
                    $this->pdo->exec("CREATE TABLE IF NOT EXISTS admins (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password_hash TEXT, email TEXT UNIQUE)");
                    $this->pdo->exec("INSERT OR IGNORE INTO admins (username, password_hash, email) VALUES ('admin@fallscoffee.ca', '$2y$10\$Vg4VW.nvdy5tNUJ8ykFZJOmiJHJGkxrZYu6D0cQPW82kmPQVZXLZq', 'admin@fallscoffee.ca')");
                }
            }
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

    public function exec($sql) {
        return $this->pdo->exec($sql);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function isConnected() {
        return !$this->is_mock;
    }
}
?>
