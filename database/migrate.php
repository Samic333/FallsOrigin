<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "Falls Origin Coffee - DB Migration Utility\n";
echo "==========================================\n\n";

$db = DB::getInstance();

if (!$db->isConnected()) {
    echo "Warning: Running in MOCK mode. Could not connect to database using config credentials.\n";
    echo "This script is designed to safely run against the live MySQL server.\n";
    exit(1);
}

try {
    // Check if the column exists
    $stmt = $db->query("SHOW COLUMNS FROM products LIKE 'is_active'");
    $hasIsActive = $stmt->fetch();

    $stmt = $db->query("SHOW COLUMNS FROM products LIKE 'active'");
    $hasActive = $stmt->fetch();

    if (!$hasIsActive) {
        if ($hasActive) {
            echo "Renaming legacy 'active' column to 'is_active'...\n";
            $db->query("ALTER TABLE products CHANGE COLUMN active is_active TINYINT(1) NOT NULL DEFAULT 1");
            echo "Successfully updated column name and constraints.\n";
        } else {
            echo "Adding 'is_active' column to products table...\n";
            $db->query("ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
            echo "Successfully added is_active column.\n";
        }
    } else {
        echo "'is_active' column already exists on products table.\n";
        // Ensure it has the NOT NULL DEFAULT 1 constraint
        $db->query("ALTER TABLE products MODIFY COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
        echo "Ensured is_active has NOT NULL DEFAULT 1 constraint.\n";
    }

    // Safely backfill any existing NULL values just in case
    $updated = $db->query("UPDATE products SET is_active = 1 WHERE is_active IS NULL")->rowCount();
    if ($updated > 0) {
        echo "Successfully backfilled $updated existing products with is_active = 1.\n";
    }

    echo "\nDatabase migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
