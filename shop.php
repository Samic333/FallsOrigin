<?php
$pageTitle = 'Collection';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$category = $_GET['category'] ?? 'all';

$query = "SELECT * FROM products";
$params = [];
if ($category !== 'all') {
    $query .= " WHERE type = ?";
    $params[] = $category;
}
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="pt-32 pb-24 bg-[#050505] min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <header class="mb-20 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic"><?php echo __('provenance'); ?></h2>
            <h1 class="text-6xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('micro_lot_selection'); ?></h1>
        </header>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
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
                <a href="product.php?id=<?php echo $product['id']; ?>" class="block px-10 py-12 text-decoration-none">
                    <div class="product-image-container mb-8 overflow-hidden rounded-lg bg-black/20 border border-white/5">
                        <img src="assets/img/<?php echo $imgName; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto transform transition-transform duration-700 hover:scale-110" style="image-rendering: -webkit-optimize-contrast;">
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-2xl font-serif font-bold text-white transition-colors uppercase tracking-tight"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <span class="text-amber-600 text-sm font-bold tracking-widest mt-1">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <p class="text-white/40 text-[11px] uppercase font-medium tracking-wide leading-relaxed line-clamp-2">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="btn btn-gold w-full mt-6"><?php echo __('add_to_cart'); ?></button>
                        <?php else: ?>
                            <button class="btn w-full mt-6" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.1); cursor: not-allowed;" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
