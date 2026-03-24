<?php
$pageTitle = 'Track Order';
require_once __DIR__ . '/includes/header.php';

$token = $_GET['token'] ?? null;
$order_number = $_GET['order_number'] ?? null;
$email = $_GET['email'] ?? null;
$order = null;

if ($token) {
    try {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? OR tracking_token = ?");
        $stmt->execute([$token, $token]);
        $order = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error retrieving order details.";
    }
} elseif ($order_number && $email) {
    try {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND customer_email = ?");
        $stmt->execute([$order_number, $email]);
        $result = $stmt->fetch();
        if ($result) {
            $order = $result;
        } else {
            $error = "No order found with these credentials.";
        }
    } catch (PDOException $e) {
        $error = "Database error occurred.";
    }
}
?>

<div class="py-12 md:py-16 bg-[#050505] min-h-screen">
    <div class="max-w-4xl mx-auto px-6">
        <div class="page-header mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic"><?php echo __('transactional_transparency'); ?></h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('order_verification'); ?></h1>
        </div>

        <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] shadow-2xl">
            <?php if (!$order): ?>
                <form action="track-order.php" method="GET" class="space-y-4 mb-8 pb-8 border-b border-white/5">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-4 ml-2">Secure Order Number</label>
                        <input type="text" name="order_number" required placeholder="ORD-202X-XXXXXX" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs font-mono tracking-widest focus:border-amber-600 outline-none uppercase transition-all mb-4">
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-4 ml-2">Email Address</label>
                        <input type="email" name="email" required placeholder="your.email@example.com" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs font-mono tracking-widest focus:border-amber-600 outline-none transition-all">
                    </div>
                    <button type="submit" class="btn btn-gold w-full py-4 uppercase text-[10px] tracking-[0.4em] mt-4">Lookup via Email</button>
                </form>

                <form action="track-order.php" method="GET" class="space-y-4">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-4 ml-2">Or Use Verification Token</label>
                        <input type="text" name="token" required placeholder="FOC-XXXXXXXXXXXX" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs font-mono tracking-widest focus:border-amber-600 outline-none transition-all uppercase">
                    </div>
                    <button type="submit" class="btn border border-white/10 text-white/40 hover:text-white w-full py-4 uppercase text-[10px] tracking-[0.4em]">Retrieve by Token</button>
                    <p class="text-[9px] text-white/30 mt-6 text-center uppercase tracking-[0.2em]"><?php echo __('check_email_token'); ?></p>
                </form>
            <?php else: ?>
                <div class="space-y-12">
                    <div class="flex justify-between items-start border-b border-white/5 pb-10">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-2 italic"><?php echo __('identity'); ?></p>
                            <h3 class="text-3xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('order_id'); ?> #<?php echo $order['id']; ?></h3>
                        </div>
                        <div class="text-right">
                            <span class="px-6 py-2 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full"><?php echo $order['status']; ?></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 text-decoration-none">
                        <div>
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-white/20 mb-6"><?php echo __('logistics_intel'); ?></h4>
                            <div class="space-y-4">
                                <p class="text-sm font-bold uppercase tracking-widest text-white"><?php echo $order['delivery_method']; ?></p>
                                <?php if ($order['tracking_number']): ?>
                                    <p class="text-[11px] font-mono text-amber-600 uppercase tracking-widest">Tracking: <?php echo e($order['tracking_number']); ?></p>
                                    <?php if (!empty($order['carrier'])): ?>
                                        <p class="text-[10px] text-white/50 uppercase tracking-widest font-bold">Carrier: <?php echo e($order['carrier']); ?></p>
                                        <a href="https://www.google.com/search?q=<?php echo urlencode($order['carrier'] . ' tracking ' . $order['tracking_number']); ?>" target="_blank" class="inline-block mt-2 text-[9px] font-black uppercase tracking-widest text-amber-600 hover:text-white transition-colors">Carrier Status &rarr;</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <p class="text-[10px] text-white/30 uppercase tracking-widest font-bold mt-4"><?php echo __('est_arrival'); ?>: <?php echo $order['eta'] ?: 'PENDING'; ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-white/20 mb-6"><?php echo __('settlement_data'); ?></h4>
                            <p class="text-4xl font-serif font-bold text-white tracking-tighter italic">$<?php echo number_format($order['total'], 2); ?> CAD</p>
                        </div>
                    </div>
                    
                    <a href="track-order.php" class="block w-full text-center py-6 text-[10px] font-black uppercase tracking-[0.4em] text-white/30 hover:text-white transition-colors border-t border-white/5 pt-8 mt-12 text-decoration-none"><?php echo __('verify_different'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
