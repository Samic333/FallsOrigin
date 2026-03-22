<?php
require_once __DIR__ . '/includes/db.php';

try {
    $db = DB::getInstance();

    // Ensure admins table exists if the fallback is entirely empty
    $db->prepare("CREATE TABLE IF NOT EXISTS `admins` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
      `username` varchar(50) NOT NULL UNIQUE,
      `password_hash` varchar(255) NOT NULL,
      `email` varchar(100) NOT NULL UNIQUE,
      `last_login` timestamp NULL DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
    )")->execute();

    $username = 'admin@fallscoffee.ca';
    $password = 'FallsCoffee#2026';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if the user exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $db->prepare("UPDATE admins SET password_hash = ?, email = ? WHERE username = ?");
        $stmt->execute([$hash, $username, $username]);
        echo "Admin updated successfully.\n";
    } else {
        $stmt = $db->prepare("INSERT INTO admins (username, password_hash, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $username]);
        echo "Admin inserted successfully.\n";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
