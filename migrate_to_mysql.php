<?php
require_once __DIR__ . '/includes/db.php';

echo "🚀 Starting Migration to MySQL...\n";

try {
    $db = DB::getInstance();
    $schemaFile = __DIR__ . '/schema.sql';
    
    if (!file_exists($schemaFile)) {
        die("❌ Error: schema.sql not found.\n");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Execute multiple queries
    // PDO::exec doesn't handle multiple queries separated by ; well for some drivers/versions if they contain certain characters,
    // but for MySQL it usually works. However, splitting by ; and executing individually is safer for a migration script.
    
    // Simple split by ; - might be fragile for complex TRIGGERS or PROCEDURES but okay for this schema.
    $queries = explode(';', $sql);
    
    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $db->exec($query);
            $count++;
        } catch (PDOException $e) {
            echo "⚠️ Warning in query: " . substr($query, 0, 50) . "...\n";
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Migration completed. $count queries executed.\n";
    
} catch (PDOException $e) {
    if ($e->getCode() == 2002) {
        echo "❌ Connection Refused: MySQL is likely not running on this local machine at port 3306.\n";
        echo "💡 Note: You mentioned that the cPanel database is now attached. This script tests the transition to that live configuration.\n";
    } else {
        echo "❌ Database Error: " . $e->getMessage() . "\n";
    }
}
?>
