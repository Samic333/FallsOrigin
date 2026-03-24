<?php
require_once __DIR__ . '/includes/db.php';

echo "🛠️ Patching Database Schema (v2 - Orders Sync)...\n";

try {
    $db = DB::getInstance();
    
    // List of columns to add to 'orders' table
    $columns = [
        'email' => "ALTER TABLE orders ADD COLUMN email VARCHAR(150) NOT NULL AFTER customer_name",
        'address' => "ALTER TABLE orders ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER customer_phone",
        'city' => "ALTER TABLE orders ADD COLUMN city VARCHAR(100) DEFAULT NULL AFTER address",
        'province' => "ALTER TABLE orders ADD COLUMN province VARCHAR(100) DEFAULT NULL AFTER city",
        'postal_code' => "ALTER TABLE orders ADD COLUMN postal_code VARCHAR(20) DEFAULT NULL AFTER province",
        'items' => "ALTER TABLE orders ADD COLUMN items LONGTEXT DEFAULT NULL AFTER payment_intent_id",
        'carrier' => "ALTER TABLE orders ADD COLUMN carrier VARCHAR(100) DEFAULT NULL AFTER items",
        'eta' => "ALTER TABLE orders ADD COLUMN eta VARCHAR(100) DEFAULT NULL AFTER carrier",
        'delivery_signature' => "ALTER TABLE orders ADD COLUMN delivery_signature VARCHAR(255) DEFAULT NULL AFTER eta",
        'review_email_sent' => "ALTER TABLE orders ADD COLUMN review_email_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_signature",
        // Rename or add tracking_number if missing from previous schema iterations
        'tracking_number' => "ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(150) DEFAULT NULL AFTER payment_intent_id"
    ];
    
    foreach ($columns as $col => $sql) {
        try {
            $db->exec($sql);
            echo "✅ Added/Checked column: $col\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "ℹ️ Column $col already exists.\n";
            } else {
                echo "❌ Error adding $col: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "🏁 Patching complete. Order flow and Admin views now synchronized.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
