<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "Falls Origin Coffee - Database Seeder\n";
echo "====================================\n\n";

$db = DB::getInstance();

if (!$db->isConnected()) {
    echo "Error: Database connection failed. Please check your includes/config.php settings.\n";
    exit(1);
}

$products = [
    [
        'id' => 1,
        'slug' => 'yirgacheffe-heirloom',
        'name' => 'Yirgacheffe',
        'origin' => 'Ethiopia',
        'price' => 19.99,
        'weight' => '340g',
        'description' => 'Bright, floral, and complex notes. Hints of jasmine and citrus.',
        'image_url' => 'assets/img/yirgacheffe.png',
        'stock_quantity' => 10,
        'tasting_notes' => 'Jasmine, Bergamot, Blueberry',
        'brewing_suggestions' => 'Pour over (V60), 1:15 ratio, 93°C water',
        'origin_story' => 'Grown in the high altitudes of the Yirgacheffe region, these heirloom varietals are hand-picked by local farmers.',
        'is_active' => 1
    ],
    [
        'id' => 2,
        'slug' => 'sidamo-gu-reserve',
        'name' => 'Sidamo',
        'origin' => 'Ethiopia',
        'price' => 19.99,
        'weight' => '340g',
        'description' => 'Deep berry notes with a smooth chocolate finish.',
        'image_url' => 'assets/img/sidamo.png',
        'stock_quantity' => 10,
        'tasting_notes' => 'Dark Chocolate, Blackberry, Maple',
        'brewing_suggestions' => 'French Press or Espresso',
        'origin_story' => 'From the Guji zone of Sidamo, naturally processed and sun-dried on raised beds.',
        'is_active' => 1
    ],
    [
        'id' => 3,
        'slug' => 'guji-selection',
        'name' => 'Guji',
        'origin' => 'Ethiopia',
        'price' => 19.99,
        'weight' => '340g',
        'description' => 'Sweet citrus and balanced acidity. Complex jasmine aroma.',
        'image_url' => 'assets/img/guji.png',
        'stock_quantity' => 10,
        'tasting_notes' => 'Peach, Honey, Jasmine',
        'brewing_suggestions' => 'Chemex, 1:16 ratio',
        'origin_story' => 'Direct trade from a micro-lot in the Guji region, known for its exceptional sweetness.',
        'is_active' => 1
    ]
];

foreach ($products as $p) {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO products (id, slug, name, origin, price, weight, description, image_url, stock_quantity, tasting_notes, brewing_suggestions, origin_story, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$p['id'], $p['slug'], $p['name'], $p['origin'], $p['price'], $p['weight'], $p['description'], $p['image_url'], $p['stock_quantity'], $p['tasting_notes'], $p['brewing_suggestions'], $p['origin_story'], $p['is_active']]);
        echo "Seeded: " . $p['name'] . "\n";
    } catch (PDOException $e) {
        echo "Failed to seed " . $p['name'] . ": " . $e->getMessage() . "\n";
    }
}

// Seed Admin User
$adminUser = [
    'username' => 'admin@fallscoffee.ca',
    'email' => 'admin@fallscoffee.ca',
    'password' => 'FallsCoffee#2026'
];

try {
    $hashed = password_hash($adminUser['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT IGNORE INTO admin_users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$adminUser['username'], $adminUser['email'], $hashed]);
    echo "Seeded: Admin Identity (" . $adminUser['username'] . ")\n";
} catch (PDOException $e) {
    echo "Failed to seed Admin Identity: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
?>
