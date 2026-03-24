<?php
require_once __DIR__ . '/includes/config.php';

echo "<h2>Falls Origin - Database Connectivity Debugger</h2>";
echo "<pre>";

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
echo "Attempting connection to: " . DB_HOST . " / " . DB_NAME . "\n";

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "\n[SUCCESS] Connected to database successfully.\n";
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(", ", $tables) . "\n";
    
    if (in_array('admin_users', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        echo "Admin victims found: " . $count . "\n";
    } else {
        echo "[WARNING] admin_users table is MISSING. Please run schema.sql import.\n";
    }

} catch (PDOException $e) {
    echo "\n[FAILURE] Connection failed!\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    
    echo "\n--- Troubleshooting Tips ---\n";
    if ($e->getCode() == 1045) {
        echo "- Check DB_USER and DB_PASS. They are likely incorrect.\n";
        echo "- Ensure the user has been added to the database in cPanel with all privileges.\n";
    } elseif ($e->getCode() == 2002) {
        echo "- Check DB_HOST. Try 'localhost' or '127.0.0.1'.\n";
    } elseif ($e->getCode() == 1049) {
        echo "- Check DB_NAME. The database does not exist.\n";
    }
}

echo "</pre>";
?>
