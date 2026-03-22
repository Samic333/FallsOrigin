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
    <div class="hero-bg-image"></div>
    <div class="hero-overlay"></div>
    
    <div class="container">
        <div class="hero-content">
            <h1 class="display-title font-serif">
                <?php echo __('hero_title_1'); ?> <span style="color: var(--accent-gold);"><?php echo __('hero_title_2'); ?></span> <br>
                <?php echo __('hero_title_3'); ?>
            </h1>
            <p class="hero-description">
                <?php echo __('hero_subtext'); ?>
            </p>
            <div class="hero-actions" style="display: flex; gap: 1.5rem;">
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
            <?php 
            $imageMap = [
                'Yirgacheffe' => 'yirgacheffe.png',
                'Sidamo' => 'sidamo.png',
                'Guji' => 'guji.png'
            ];
            foreach ($products as $product): 
                $imgName = $imageMap[$product['name']] ?? 'product_front.png';
            ?>
            <div class="product-card">
                <a href="product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="product-image-container" style="margin-bottom: 2rem; overflow: hidden; border-radius: 4px;">
                        <img src="assets/img/<?php echo $imgName; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: auto; display: block; transition: var(--transition);">
                    </div>
                    <h3 class="product-name font-serif" style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                    <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] <= 0): ?>
                        <div style="margin-top: 1rem;">
                            <button class="btn" style="width: 100%; background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.1); cursor: not-allowed; min-height: 48px;" disabled>Out of Stock</button>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 1rem; position: relative; z-index: 10;">
                            <button onclick="addToCart(event, <?php echo $product['id']; ?>)" class="btn btn-gold" style="width: 100%; min-height: 48px; position: relative; cursor: pointer;"><?php echo __('add_to_cart'); ?></button>
                        </div>
                    <?php endif; ?>
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
