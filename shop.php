<?php
$pageTitle = 'Collection';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$category = $_GET['category'] ?? 'all';

$query = "SELECT * FROM products WHERE is_active = 1";
$params = [];
if ($category !== 'all') {
    $query .= " AND category_id = ?";
    $params[] = $category;
}
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="py-12 md:py-16 bg-[#050505] min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <div class="page-header mb-20 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic"><?php echo __('provenance'); ?></h2>
            <h1 class="text-6xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('micro_lot_selection'); ?></h1>
        </div>

        <!-- Category Filter -->
        <div class="flex flex-wrap gap-4 justify-center mb-16">
            <a href="shop.php" class="px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 <?php echo $category === 'all' ? 'bg-amber-600 text-white border-amber-600' : 'text-white/40 hover:text-white hover:border-white/30'; ?> transition-all">All Selection</a>
            <?php 
            try {
                $categories = $db->query("SELECT * FROM categories")->fetchAll();
                foreach ($categories as $cat): 
            ?>
                <a href="shop.php?category=<?php echo $cat['id']; ?>" class="px-6 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 <?php echo $category == $cat['id'] ? 'bg-amber-600 text-white border-amber-600' : 'text-white/40 hover:text-white hover:border-white/30'; ?> transition-all">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php 
                endforeach; 
            } catch (Exception $e) {} 
            ?>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php 
            foreach ($products as $product): 
                $imgUrl = !empty($product['image_url']) ? $product['image_url'] : 'assets/img/product_front.png';
            ?>
            <div class="product-card">
                <a href="product.php?id=<?php echo $product['id']; ?>" class="block px-10 py-12 text-decoration-none">
                    <div class="product-image-container mb-8 overflow-hidden rounded-lg bg-black/20 border border-white/5">
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto transform transition-transform duration-700 hover:scale-110" style="image-rendering: -webkit-optimize-contrast;">
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-2xl font-serif font-bold text-white transition-colors uppercase tracking-tight"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <span class="text-amber-600 text-sm font-bold tracking-widest mt-1">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <p class="text-white/40 text-[11px] uppercase font-medium tracking-wide leading-relaxed line-clamp-2">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                    </div>
                </a>
                <div class="px-10 pb-12 pt-0 -mt-2 relative z-10 w-full">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <button onclick="addToCart(event, <?php echo $product['id']; ?>)" class="btn btn-gold w-full min-h-[48px] uppercase tracking-widest text-[10px]"><?php echo __('add_to_cart'); ?></button>
                    <?php else: ?>
                        <button class="btn w-full min-h-[48px]" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.1); cursor: not-allowed;" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
