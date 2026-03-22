<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$id = $_GET['id'] ?? null;
$db = DB::getInstance();
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { header('Location: shop.php'); exit; }

$pageTitle = $product['name'] ?? 'Product Details';
require_once __DIR__ . '/includes/header.php';

$reviewsStmt = $db->prepare("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC");
$reviewsStmt->execute();
$reviews = $reviewsStmt->fetchAll();
?>

<div class="pt-32 pb-24 bg-[#050505] min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-24">
            <!-- Product Vis -->
            <?php 
                $imageMap = [
                    'Yirgacheffe' => 'yirgacheffe.png',
                    'Sidamo' => 'sidamo.png',
                    'Guji' => 'guji.png'
                ];
                $imgName = $imageMap[$product['name']] ?? 'product_front.png';
            ?>
            <div class="bg-[#0a0a0a] border border-white/5 rounded-[4rem] p-16 flex items-center justify-center sticky top-32 h-[75vh] shadow-2xl">
                <img src="assets/img/<?php echo $imgName; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-w-[85%] max-h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
            </div>

            <!-- Product Data -->
            <div class="py-12">
                <div class="mb-12">
                    <span class="text-amber-600 text-[11px] font-black uppercase tracking-[0.5em] mb-4 block italic"><?php echo __('provenance'); ?></span>
                    <h1 class="text-7xl font-serif font-bold text-white uppercase tracking-tighter italic leading-[0.8] mb-8"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="text-white/50 text-xl font-medium tracking-tight leading-relaxed uppercase max-w-xl">
                        <?php echo htmlspecialchars($product['description']); ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-12 mb-16 py-12 border-y border-white/5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/20 mb-4 italic">Unit Weight</p>
                        <p class="text-white text-sm font-bold uppercase tracking-widest"><?php echo htmlspecialchars($product['weight']); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/20 mb-4 italic"><?php echo __('settlement_data'); ?></p>
                        <p class="text-amber-600 text-sm font-bold uppercase tracking-widest">$<?php echo number_format($product['price'], 2); ?> CAD</p>
                    </div>
                </div>

                <div class="space-y-12">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <button onclick="addToCart('<?php echo $product['id']; ?>')" class="btn btn-gold w-full py-8 uppercase text-[12px] tracking-[0.5em] shadow-2xl">
                            <?php echo __('add_to_cart'); ?>
                        </button>
                    <?php else: ?>
                        <button disabled class="w-full py-8 uppercase text-[12px] tracking-[0.5em] shadow-2xl" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); border: 1px solid rgba(255,255,255,0.1); cursor: not-allowed;">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                    <div class="flex items-center justify-center space-x-12 pt-8 opacity-40 text-[10px] font-black uppercase tracking-widest text-white">
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <span>Ethically Sourced</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>Micro-lot Certified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function addToCart(id) {
    const res = await fetch('cart.php?action=add&id=' + id);
    if (res.ok) window.location.href = 'cart.php';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
