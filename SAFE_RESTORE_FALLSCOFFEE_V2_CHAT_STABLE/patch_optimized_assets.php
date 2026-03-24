<?php
require_once __DIR__ . '/includes/db.php';
$db = DB::getInstance();

try {
    // Update Hero in Settings
    $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'hero_image'")
       ->execute(['assets/img/hero-coffee-optimized.png']);

    // Update Tade in Products (ID 4 based on user's browser URL in screenshot)
    $db->prepare("UPDATE products SET image_url = ? WHERE id = 4")
       ->execute(['assets/img/tade-optimized.png']);

    echo "Optimized assets applied (Hero & Tade).";
} catch (PDOException $e) {
    echo "Sync Failed: " . $e->getMessage();
}
?>
