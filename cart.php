<?php
$pageTitle = 'Your Selection';
require_once __DIR__ . '/includes/header.php';

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
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $cartItems[] = ['product' => $p, 'quantity' => $qty, 'subtotal' => $subtotal];
    }
}
?>

<div class="pt-32 pb-24">
    <div class="max-w-5xl mx-auto px-6">
        <header class="mb-16 border-b border-white/5 pb-10 flex justify-between items-end">
            <div>
                <h2 class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20 mb-2">Acquisition Pipeline</h2>
                <h1 class="text-4xl font-serif font-bold text-white uppercase tracking-tighter">Current Selection</h1>
            </div>
            <a href="shop.php" class="text-[9px] font-black uppercase tracking-widest text-amber-600 hover:text-white transition-colors">Continue Browsing</a>
        </header>

        <?php if (empty($cartItems)): ?>
            <div class="py-32 text-center bg-[#0a0a0a] border border-white/5 rounded-[3rem]">
                <p class="text-white/20 text-[10px] font-black uppercase tracking-[0.4em] mb-12 italic">Your selection is currently void.</p>
                <a href="shop.php" class="px-12 py-5 border border-white/10 text-white font-black uppercase text-[9px] tracking-[0.4em] hover:bg-white hover:text-black transition-all rounded-full">Explore Catalog</a>
            </div>
        <?php else: ?>
            <div class="space-y-6 mb-16">
                <?php foreach ($cartItems as $item): ?>
                <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2.5rem] flex items-center group hover:border-white/10 transition-all">
                    <div class="w-24 h-24 bg-stone-900 rounded-2xl flex items-center justify-center p-4 mr-8 group-hover:scale-105 transition-transform duration-500">
                        <img src="<?php echo e($item['product']['image_url']); ?>" class="max-w-full max-h-full object-contain">
                    </div>
                    <div class="flex-grow">
                        <h4 class="text-xs font-black uppercase tracking-widest text-white mb-2"><?php echo e($item['product']['name']); ?></h4>
                        <p class="text-[9px] text-white/20 uppercase tracking-[0.2em] italic"><?php echo e($item['product']['weight']); ?></p>
                    </div>
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center bg-white/5 rounded-full border border-white/5 overflow-hidden">
                            <a href="?action=update&id=<?php echo $item['product']['id']; ?>&qty=<?php echo $item['quantity']-1; ?>" class="px-3 py-1 text-white/40 hover:text-amber-600 transition-colors">-</a>
                            <span class="px-3 py-1 text-[10px] font-black text-white"><?php echo $item['quantity']; ?></span>
                            <a href="?action=update&id=<?php echo $item['product']['id']; ?>&qty=<?php echo $item['quantity']+1; ?>" class="px-3 py-1 text-white/40 hover:text-amber-600 transition-colors">+</a>
                        </div>
                        <p class="text-sm font-serif font-bold text-white w-20 text-right italic">$<?php echo number_format($item['subtotal'], 2); ?></p>
                        <a href="?action=remove&id=<?php echo $item['product']['id']; ?>" class="text-white/10 hover:text-red-500 transition-colors p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] flex flex-col md:flex-row justify-between items-center gap-12">
                <div class="text-center md:text-left">
                    <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 mb-2 italic italic">Financial Commitment</p>
                    <p class="text-3xl font-serif font-bold text-white tracking-tighter italic">$<?php echo number_format($total, 2); ?> CAD</p>
                </div>
                <a href="checkout.php" class="w-full md:w-auto px-16 py-7 bg-white text-black font-black uppercase text-[11px] tracking-[0.5em] hover:bg-amber-600 hover:text-white transition-all rounded-full shadow-2xl">
                    Proceed to Settlement
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
