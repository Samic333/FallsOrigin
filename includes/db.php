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
        $hosts = [DB_HOST, '127.0.0.1'];
        $lastException = null;

        foreach ($hosts as $host) {
            try {
                $dsn = "mysql:host=" . $host . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return; // Connection successful
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        // Output a professional error if all attempts fail
        http_response_code(503);
        die("<div style='background:#050505; color:#d97706; font-family:serif; height:100vh; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:20px;'>
                <h1 style='text-transform:uppercase; letter-spacing:0.2em;'>System Unavailable</h1>
                <p style='color:rgba(255,255,255,0.4); font-family:sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.1em;'>The Falls Origin vault is currently locked. Please contact the architect.</p>
                <!-- " . $lastException->getMessage() . " -->
             </div>");
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
