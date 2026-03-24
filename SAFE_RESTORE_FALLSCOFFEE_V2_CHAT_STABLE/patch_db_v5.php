<?php
require_once __DIR__ . '/includes/db.php';

echo "🛠️ Patching Database Schema (v5 - Chat System)...\n";

try {
    $db = DB::getInstance();
    
    // Add direction column
    try {
        $db->exec("ALTER TABLE contact_messages ADD COLUMN direction ENUM('inbound', 'outbound') DEFAULT 'inbound' AFTER message");
        echo "✅ Added column: direction\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️ Column direction already exists.\n";
        } else {
            throw $e;
        }
    }

    // Add parent_id column for formal threading (optional but good)
    try {
        $db->exec("ALTER TABLE contact_messages ADD COLUMN parent_id INT DEFAULT NULL AFTER id");
        echo "✅ Added column: parent_id\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️ Column parent_id already exists.\n";
        } else {
            throw $e;
        }
    }
    
    echo "🏁 Patching complete. Chat system logic now supported.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
