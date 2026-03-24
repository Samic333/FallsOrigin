<?php
require_once __DIR__ . '/includes/db.php';

echo "🛠️ Patching Database Schema...\n";

try {
    $db = DB::getInstance();
    
    // Add missing columns if they don't exist
    $columns = [
        'brewing_suggestions' => "ALTER TABLE products ADD COLUMN brewing_suggestions TEXT DEFAULT NULL AFTER tasting_notes",
        'origin_story' => "ALTER TABLE products ADD COLUMN origin_story TEXT DEFAULT NULL AFTER brewing_suggestions"
    ];
    
    foreach ($columns as $col => $sql) {
        try {
            $db->exec($sql);
            echo "✅ Added column: $col\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "ℹ️ Column $col already exists.\n";
            } else {
                echo "❌ Error adding $col: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "🏁 Patching complete. Try adding the product again.\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
