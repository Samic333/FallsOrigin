<?php
$pageTitle = 'Product Details';
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$db = DB::getInstance();
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { header('Location: shop.php'); exit; }

$reviewsStmt = $db->prepare("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC");
$reviewsStmt->execute();
$reviews = $reviewsStmt->fetchAll();
?>

<div class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-24">
            <!-- Product Vis -->
            <div class="bg-[#0a0a0a] border border-white/5 rounded-[4rem] p-16 flex items-center justify-center sticky top-32 h-[80vh]">
                <img src="<?php echo e($product['image_url']); ?>" class="max-w-[80%] max-h-full object-contain">
            </div>

            <!-- Product Data -->
            <div class="py-12">
                <div class="mb-12">
                    <span class="text-amber-600 text-[10px] font-black uppercase tracking-[0.5em] mb-4 block"><?php echo e($product['origin']); ?></span>
                    <h1 class="text-7xl font-serif font-bold text-white uppercase tracking-tighter italic leading-[0.8] mb-8"><?php echo e($product['name']); ?></h1>
                    <p class="text-white/40 text-xl font-medium tracking-tight leading-relaxed uppercase max-w-xl">
                        <?php echo e($product['description']); ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-12 mb-16 py-12 border-y border-white/5">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-4">Unit Weight</p>
                        <p class="text-white text-xs font-bold uppercase tracking-widest"><?php echo e($product['weight']); ?></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-4">Acquisition Cost</p>
                        <p class="text-amber-600 text-xs font-bold uppercase tracking-widest">$<?php echo $product['price']; ?> CAD</p>
                    </div>
                </div>

                <div class="space-y-12">
                    <button onclick="addToCart('<?php echo $product['id']; ?>')" class="w-full py-8 bg-white text-black font-black uppercase text-[11px] tracking-[0.5em] hover:bg-amber-600 hover:text-white transition-all rounded-full shadow-2xl">
                        Add to Selection
                    </button>
                    
                    <div class="flex items-center justify-center space-x-12 pt-8 opacity-20 text-[9px] font-black uppercase tracking-widest">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <span>Ethically Sourced</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
