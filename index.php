<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
// Fetch 3 featured products for the collection section
$stmt = $db->query("SELECT * FROM products LIMIT 3");
$products = $stmt->fetchAll();

// Mock data for preview if database is empty
if (empty($products)) {
    $products = [
        ['id' => 1, 'name' => 'Yirgacheffe', 'price' => 19.99, 'description' => 'Bright, floral, and complex notes.'],
        ['id' => 2, 'name' => 'Sidamo', 'price' => 19.99, 'description' => 'Deep berry notes with a smooth finish.'],
        ['id' => 3, 'name' => 'Guji', 'price' => 19.99, 'description' => 'Sweet citrus and balanced acidity.']
    ];
}

$featuredProduct = $products[0] ?? null;
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg-image" style="background-image: url('assets/img/hero-coffee.png');"></div>
    <div class="hero-overlay"></div>
    
    <div class="container">
        <div class="hero-content">
            <h1 class="display-title font-serif">
                Premium Ethiopian <span style="color: var(--accent-gold);">Coffee</span> <br>
                Crafted for Canada
            </h1>
            <p class="hero-description">
                Rich, bold, and smooth — delivered fresh <br>
                from origin to your door.
            </p>
            <div style="display: flex; gap: 1.5rem;">
                <a href="shop.php" class="btn btn-gold">Shop Now</a>
                <a href="shop.php" class="btn btn-outline" style="border-radius: 4px;">View Collection</a>
            </div>
        </div>
    </div>
</section>

<!-- Our Collection -->
<section id="collection">
    <div class="container">
        <h2 class="section-title font-serif" style="text-transform: none; letter-spacing: -0.02em;">Our Collection</h2>
        <div class="collection-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" style="padding: 1.5rem;">
                <a href="product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                    <img src="assets/img/product_front.png" alt="<?php echo e($product['name']); ?>" style="max-width: 90%; margin-bottom: 1.5rem;">
                    <h3 class="product-name font-serif" style="margin-bottom: 0.25rem;"><?php echo e($product['name']); ?></h3>
                    <p class="product-price">$<?php echo $product['price']; ?></p>
                    <button class="btn btn-gold" style="width: 100%; border-radius: 4px;">Add to Cart</button>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Benefits / Trust -->
<section style="background-color: var(--bg-primary); padding: 4rem 0; border-top: 1px solid rgba(255, 255, 255, 0.05);">
    <div class="container">
        <div class="benefits-grid" style="gap: 2rem;">
            <div class="benefit-item">
                <div style="margin-bottom: 1rem;"><img src="https://flagcdn.com/w40/et.png" width="30" alt="Ethiopia"></div>
                <h4 class="benefit-title font-serif" style="text-transform: none; letter-spacing: normal;">Ethiopian Origin</h4>
                <p class="benefit-text" style="opacity: 0.6;">Sourced direct from the finest<br>Ethiopian coffee farms.</p>
            </div>
            <div class="benefit-item">
                <div style="margin-bottom: 1rem;"><span style="font-size: 2rem;">☕</span></div>
                <h4 class="benefit-title font-serif" style="text-transform: none; letter-spacing: normal;">Freshly Roasted</h4>
                <p class="benefit-text" style="opacity: 0.6;">Roasted locally in Canada for<br>peak freshness.</p>
            </div>
            <div class="benefit-item">
                <div style="margin-bottom: 1rem;"><img src="https://flagcdn.com/w40/ca.png" width="30" alt="Canada"></div>
                <h4 class="benefit-title font-serif" style="text-transform: none; letter-spacing: normal;">Delivered in Canada</h4>
                <p class="benefit-text" style="opacity: 0.6;">Fast reliable shipping<br>across Canada.</p>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="cta-section">
    <div class="cta-bg"></div>
    <div class="cta-overlay"></div>
    <div class="container cta-content">
        <h2 class="display-title font-serif" style="font-size: clamp(2rem, 5vw, 3.5rem); text-transform: none;">Experience Coffee the Right Way</h2>
        <a href="shop.php" class="btn btn-gold" style="margin-top: 2rem; border-radius: 4px;">Order Now</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
