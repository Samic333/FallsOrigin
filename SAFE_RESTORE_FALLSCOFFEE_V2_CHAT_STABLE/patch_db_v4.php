<?php
require_once __DIR__ . '/includes/db.php';

echo "🛠️ Patching Database Schema (v4 - Archive/Delete)...\n";

try {
    $db = DB::getInstance();
    
    // Check if archived_at exists, add if not
    try {
        $db->exec("ALTER TABLE contact_messages ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL AFTER replied_at");
        echo "✅ Added column: archived_at\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️ Column archived_at already exists.\n";
        } else {
            throw $e;
        }
    }
    
    echo "🏁 Patching complete. Admin Archive/Delete logic now supported.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
