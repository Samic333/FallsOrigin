<?php
require_once __DIR__ . '/includes/db.php';
$db = DB::getInstance();

try {
    // Update Hero in Settings to the BRAND NEW Master Asset
    $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'hero_image'")
       ->execute(['assets/img/hero-brand-master.png']);

    echo "Brand Master Hero applied successfully.";
} catch (PDOException $e) {
    echo "Sync Failed: " . $e->getMessage();
}
?>
