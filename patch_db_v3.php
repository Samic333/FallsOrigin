<?php
require_once __DIR__ . '/includes/db.php';

echo "🛠️ Patching Database Schema (v3 - Admin Comms)...\n";

try {
    $db = DB::getInstance();
    
    // Columns for contact_messages
    $columns = [
        'reply_content' => "ALTER TABLE contact_messages ADD COLUMN reply_content TEXT DEFAULT NULL AFTER message",
        'replied_at' => "ALTER TABLE contact_messages ADD COLUMN replied_at TIMESTAMP NULL DEFAULT NULL AFTER reply_content",
        'deleted_at' => "ALTER TABLE contact_messages ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER created_at",
        'status_fix' => "ALTER TABLE contact_messages MODIFY COLUMN status ENUM('Unread','Read','Replied') NOT NULL DEFAULT 'Unread'"
    ];
    
    foreach ($columns as $col => $sql) {
        try {
            $db->exec($sql);
            echo "✅ Added/Checked column: $col\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                echo "ℹ️ Column $col already exists.\n";
            } else {
                echo "❌ Error adding $col: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "🏁 Patching complete. Admin Comms logic now ready.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
