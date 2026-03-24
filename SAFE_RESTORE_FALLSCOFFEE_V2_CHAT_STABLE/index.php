<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
// Fetch 3 featured products for the collection section
$stmt = $db->query("SELECT * FROM products WHERE is_active = 1 ORDER BY is_featured DESC, created_at DESC LIMIT 3");
$products = $stmt->fetchAll();

$settings = [];
try {
    $settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // Falls back to defaults if table doesn't exist yet
}

$heroImg = $settings['hero_image'] ?? 'assets/img/hero-coffee.png';
$heroOpacity = $settings['hero_opacity'] ?? '0.95';
$heroOverlayStr = $settings['hero_overlay_strength'] ?? '0.6';

// Asset versioning based on file modified time
$asset_v = file_exists(__DIR__ . '/' . $heroImg) ? filemtime(__DIR__ . '/' . $heroImg) : time();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg-container">
        <img src="<?php echo e($heroImg); ?>?v=<?php echo $asset_v; ?>" alt="Falls Origin Heritage" class="hero-master-img" style="opacity: <?php echo $heroOpacity; ?>;">
        <div class="hero-overlay" style="background: linear-gradient(to right, #0B0F14 20%, rgba(11, 15, 20, <?php echo $heroOverlayStr; ?>) 50%, transparent 100%);"></div>
    </div>
    
    <div class="container hero-layout-container">
        <div class="hero-content">
            <h1 class="display-title font-serif">
                <?php echo __('hero_title_1'); ?> <span style="color: var(--accent-gold);"><?php echo __('hero_title_2'); ?></span> <br>
                <?php echo __('hero_title_3'); ?>
            </h1>
            <p class="hero-description">
                <?php echo __('hero_subtext'); ?>
            </p>
            <div class="hero-actions">
                <a href="shop.php" class="btn btn-gold"><?php echo __('shop_now'); ?></a>
                <a href="#collection" class="btn btn-outline"><?php echo __('view_collection'); ?></a>
            </div>
        </div>
    </div>
</section>

<!-- Our Collection -->
<section id="collection">
    <div class="container">
        <h2 class="section-title font-serif" style="text-transform: none; letter-spacing: -0.02em;"><?php echo __('our_collection'); ?></h2>
        <div class="collection-grid">
            <?php foreach ($products as $product): 
                $displayImg = !empty($product['image_url']) ? $product['image_url'] : 'assets/img/product_front.png';
            ?>
            <div class="product-card">
                <a href="product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="product-image-container" style="margin-bottom: 2rem; overflow: hidden; border-radius: 4px;">
                        <img src="<?php echo htmlspecialchars($displayImg); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: auto; display: block; transition: var(--transition); image-rendering: -webkit-optimize-contrast;">
                    </div>
                    <h3 class="product-name font-serif" style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                </a>
                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] <= 0): ?>
                    <div style="margin-top: 1rem;">
                        <button class="btn" style="width: 100%; background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.1); cursor: not-allowed; min-height: 48px;" disabled>Out of Stock</button>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 1rem; position: relative; z-index: 10;">
                        <button onclick="addToCart(event, <?php echo $product['id']; ?>)" class="btn btn-gold" style="width: 100%; min-height: 48px; position: relative; cursor: pointer;"><?php echo __('add_to_cart'); ?></button>
                    </div>
                <?php endif; ?>
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
                <div style="margin-bottom: 1rem; display: flex; justify-content: center;"><img src="https://flagcdn.com/w40/et.png" width="30" alt="Ethiopia"></div>
                <h4 class="benefit-title font-serif" style="text-transform: none; letter-spacing: normal;"><?php echo __('ethiopian_origin'); ?></h4>
                <p class="benefit-text" style="opacity: 0.6;"><?php echo __('ethiopian_origin_desc'); ?></p>
            </div>
            <div class="benefit-item">
                <div style="margin-bottom: 1rem; display: flex; justify-content: center;"><span style="font-size: 2rem;">☕</span></div>
                <h4 class="benefit-title font-serif" style="text-transform: none; letter-spacing: normal;"><?php echo __('freshly_roasted'); ?></h4>
                <p class="benefit-text" style="opacity: 0.6;"><?php echo __('freshly_roasted_desc'); ?></p>
            </div>
            <div class="benefit-item">
                <div style="margin-bottom: 1rem; display: flex; justify-content: center;"><img src="https://flagcdn.com/w40/ca.png" width="30" alt="Canada"></div>
                <h4 class="benefit-title font-serif" style="text-transform: none; letter-spacing: normal;"><?php echo __('delivered_canada'); ?></h4>
                <p class="benefit-text" style="opacity: 0.6;"><?php echo __('delivered_canada_desc'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="cta-section">
    <div class="cta-bg"></div>
    <div class="cta-overlay"></div>
    <div class="container cta-content">
        <h2 class="display-title font-serif" style="font-size: clamp(2rem, 5vw, 3.5rem); text-transform: none;"><?php echo __('experience_coffee'); ?></h2>
        <a href="shop.php" class="btn btn-gold" style="margin-top: 2rem; border-radius: 4px;"><?php echo __('order_now'); ?></a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
