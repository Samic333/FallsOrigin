<?php
$pageTitle = 'Your Selection';
require_once __DIR__ . '/includes/functions.php';

// Cart Logic
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;
    $qty = (int)($_GET['qty'] ?? 1);

    if ($action === 'add' && $id) {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
    } elseif ($action === 'remove' && $id) {
        unset($_SESSION['cart'][$id]);
    } elseif ($action === 'update' && $id) {
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id] = $qty;
    }
    header('Location: cart.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$cartItems = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        if (!isset($_SESSION['cart'][$p['id']])) continue;
        
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $cartItems[] = ['product' => $p, 'quantity' => $qty, 'subtotal' => $subtotal];
    }
}
?>

<div class="pt-8 md:pt-12 pb-24 bg-[#050505] min-h-screen">
    <div class="max-w-5xl mx-auto px-6">
        <div class="page-header mb-16 border-b border-white/5 pb-10 flex justify-between items-end">
            <div>
                <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-amber-600 mb-2 italic"><?php echo __('transactional_transparency'); ?></h2>
                <h1 class="text-4xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('cart'); ?></h1>
            </div>
            <a href="shop.php" class="text-[10px] font-black uppercase tracking-widest text-amber-600 hover:text-white transition-colors text-decoration-none"><?php echo __('view_collection'); ?></a>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="py-32 text-center bg-[#0a0a0a] border border-white/5 rounded-[3rem] shadow-xl">
                <p class="text-white/20 text-[10px] font-black uppercase tracking-[0.4em] mb-12 italic"><?php echo __('cart_empty'); ?></p>
                <a href="shop.php" class="btn btn-gold px-12 py-5 uppercase text-[10px] tracking-[0.4em]"><?php echo __('shop_now'); ?></a>
            </div>
        <?php else: ?>
            <div class="space-y-6 mb-16">
                <?php foreach ($cartItems as $item): 
                    $imageMap = [
                        'Yirgacheffe' => 'yirgacheffe.png',
                        'Sidamo' => 'sidamo.png',
                        'Guji' => 'guji.png'
                    ];
                    $imgName = $imageMap[$item['product']['name']] ?? 'product_front.png';
                ?>
                <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2.5rem] flex items-center group hover:border-white/10 transition-all shadow-lg">
                    <div class="w-32 h-32 bg-black/20 rounded-2xl flex items-center justify-center p-4 mr-8 group-hover:scale-105 transition-transform duration-500 border border-white/5">
                        <img src="assets/img/<?php echo $imgName; ?>" class="max-w-full max-h-full object-contain" style="image-rendering: -webkit-optimize-contrast;">
                    </div>
                    <div class="flex-grow">
                        <h4 class="text-xl font-serif font-bold uppercase tracking-tight text-white mb-2"><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                        <p class="text-[10px] text-white/30 uppercase tracking-[0.2em] italic font-bold"><?php echo htmlspecialchars($item['product']['origin']); ?></p>
                    </div>
                    <div class="flex items-center space-x-12">
                        <div class="flex items-center bg-white/5 rounded-full border border-white/5 overflow-hidden">
                            <a href="?action=update&id=<?php echo $item['product']['id']; ?>&qty=<?php echo $item['quantity']-1; ?>" class="px-4 py-2 text-white/40 hover:text-amber-600 transition-colors text-decoration-none font-bold">-</a>
                            <span class="px-2 py-2 text-[11px] font-black text-white"><?php echo $item['quantity']; ?></span>
                            <a href="?action=update&id=<?php echo $item['product']['id']; ?>&qty=<?php echo $item['quantity']+1; ?>" class="px-4 py-2 text-white/40 hover:text-amber-600 transition-colors text-decoration-none font-bold">+</a>
                        </div>
                        <p class="text-lg font-serif font-bold text-white w-24 text-right italic">$<?php echo number_format($item['subtotal'], 2); ?></p>
                        <a href="?action=remove&id=<?php echo $item['product']['id']; ?>" class="text-white/10 hover:text-red-500 transition-colors p-2 ml-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] flex flex-col md:flex-row justify-between items-center gap-12 shadow-2xl">
                <div class="text-center md:text-left">
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20 mb-2 italic"><?php echo __('settlement_data'); ?></p>
                    <p class="text-4xl font-serif font-bold text-white tracking-tighter italic">$<?php echo number_format($total, 2); ?> CAD</p>
                </div>
                <a href="checkout.php" class="btn btn-gold px-16 py-7 uppercase text-[12px] tracking-[0.5em] shadow-2xl">
                    <?php echo __('checkout'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
