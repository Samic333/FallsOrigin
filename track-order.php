<?php
$pageTitle = 'Order Verification';
require_once __DIR__ . '/includes/header.php';

$token = $_GET['token'] ?? null;
$order = null;

if ($token) {
    $db = DB::getInstance();
    $stmt = $db->prepare("SELECT * FROM orders WHERE tracking_token = ?");
    $stmt->execute([$token]);
    $order = $stmt->fetch();
}
?>

<div class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        <header class="mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic">Transactional Transparency</h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter">Order Verification</h1>
        </header>

        <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem]">
            <?php if (!$order): ?>
                <form action="track-order.php" method="GET" class="space-y-8">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-4 ml-2">Verification Token</label>
                        <input type="text" name="token" required placeholder="FOC-XXXXXXXXXXXX" class="w-full bg-white/[0.02] border border-white/5 p-6 rounded-2xl text-white text-sm font-mono tracking-widest focus:outline-none focus:border-amber-600 transition-all uppercase">
                    </div>
                    <button type="submit" class="w-full py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] rounded-full hover:bg-amber-600 hover:text-white transition-all shadow-xl">Retrieve Ledger</button>
                    <p class="text-[8px] text-white/10 mt-6 text-center uppercase tracking-[0.3em]">Check your acquisition confirmation email for your secure token.</p>
                </form>
            <?php else: ?>
                <div class="space-y-12">
                    <div class="flex justify-between items-start border-b border-white/5 pb-10">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-2 italic italic">Acquisition Identity</p>
                            <h3 class="text-2xl font-serif font-bold text-white uppercase italic tracking-tighter"><?php echo $order['id']; ?></h3>
                        </div>
                        <div class="text-right">
                            <span class="px-6 py-2 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full"><?php echo $order['status']; ?></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div>
                            <h4 class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-6">Logistics Intel</h4>
                            <div class="space-y-4">
                                <p class="text-xs font-bold uppercase tracking-widest text-white"><?php echo $order['delivery_method']; ?></p>
                                <?php if ($order['tracking_number']): ?>
                                    <p class="text-[10px] font-mono text-amber-600 uppercase tracking-widest">Tracking: <?php echo e($order['tracking_number']); ?></p>
                                <?php endif; ?>
                                <p class="text-[10px] text-white/40 uppercase tracking-widest">EST. ARRIVAL: <?php echo $order['eta'] ?: 'LOGISTICS PENDING'; ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h4 class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-6">Settlement Data</h4>
                            <p class="text-3xl font-serif font-bold text-white tracking-tighter italic">$<?php echo number_format($order['total'], 2); ?> CAD</p>
                        </div>
                    </div>
                    
                    <a href="track-order.php" class="block w-full text-center py-4 text-[9px] font-black uppercase tracking-[0.4em] text-white/20 hover:text-white transition-colors border-t border-white/5 pt-8 mt-12">Verify Different Sequence</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
