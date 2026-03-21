<?php
$pageTitle = 'Order Control';
require_once __DIR__ . '/../includes/admin_header.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: orders.php'); exit; }

$db = DB::getInstance();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    if (isset($_POST['status'])) {

        $status = $_POST['status'];
        $tracking = $_POST['tracking_number'];
        $eta = $_POST['eta'];
        $signature = $_POST['delivery_signature'];

        $stmt = $db->prepare("UPDATE orders SET status = ?, tracking_number = ?, eta = ?, delivery_signature = ? WHERE id = ?");
        $stmt->execute([$status, $tracking, $eta, $signature, $id]);
        log_admin_action('Update Order', "Order {$id} status set to {$status}.");
        $msg = "Order updated successfully.";
    }

    if (isset($_POST['trigger_review'])) {
        $stmt = $db->prepare("UPDATE orders SET review_email_sent = TRUE WHERE id = ?");
        $stmt->execute([$id]);
        log_admin_action('Review Request', "Manual review request sent for Order {$id}.");
        $review_msg = "Review request dispatched to customer.";
    }
}

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) { header('Location: orders.php'); exit; }
$items = json_decode($order['items'], true);
?>

<div class="max-w-5xl">
    <div class="flex justify-between items-center mb-12">
        <a href="orders.php" class="flex items-center space-x-3 text-[10px] text-white/20 hover:text-amber-600 transition-all uppercase tracking-[0.3em] font-black group">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left group-hover:-translate-x-1 transition-transform"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            <span>Return to Ledger</span>
        </a>
        <?php if (isset($msg)): ?>
            <div class="px-6 py-2 bg-green-500/10 border border-green-500/20 text-green-500 text-[9px] font-black uppercase tracking-widest rounded-full"><?php echo $msg; ?></div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2 space-y-12">
            <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3rem]">
                <div class="flex justify-between items-start mb-12">
                    <div>
                        <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-white/20 mb-2">Order Authentication</h2>
                        <h1 class="text-4xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo $order['id']; ?></h1>
                    </div>
                </div>

                <div class="space-y-8">
                    <?php foreach ($items as $item): ?>
                    <div class="flex items-center bg-white/[0.01] border border-white/[0.03] p-6 rounded-2xl group hover:border-white/10 transition-all">
                        <div class="w-16 h-16 bg-stone-900 rounded-xl mr-6 flex items-center justify-center p-2 border border-white/5">
                            <img src="<?php echo e($item['product']['image_url']); ?>" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="flex-grow">
                            <h4 class="text-xs font-black uppercase tracking-widest text-white"><?php echo e($item['product']['name']); ?></h4>
                            <p class="text-[9px] text-white/20 uppercase tracking-[0.2em] mt-1"><?php echo e($item['product']['weight']); ?> x <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="text-right"><p class="text-sm font-serif font-bold text-white">$<?php echo number_format($item['product']['price'] * $item['quantity'], 2); ?></p></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-12 pt-12 border-t border-white/5 flex justify-between items-center text-white/60 text-[10px] font-black uppercase tracking-[0.3em]">
                    <span>Consolidated Value</span>
                    <span class="text-2xl font-serif font-bold text-white">$<?php echo number_format($order['total'], 2); ?> CAD</span>
                </div>
            </div>

            <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3rem]">
                <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Destination & Identity</h3>
                <div class="grid grid-cols-2 gap-12">
                    <div>
                        <p class="text-[8px] font-black uppercase tracking-widest text-white/20 mb-3">Identity Record</p>
                        <p class="text-white text-xs font-bold uppercase tracking-widest"><?php echo e($order['customer_name']); ?></p>
                        <p class="text-white/40 text-[10px] mt-1"><?php echo e($order['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-[8px] font-black uppercase tracking-widest text-white/20 mb-3">Secure Destination</p>
                        <p class="text-white text-[10px] leading-relaxed uppercase font-medium tracking-tight">
                            <?php echo e($order['address']); ?><br><?php echo e($order['city']); ?>, <?php echo e($order['province']); ?><br><?php echo e($order['postal_code']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-12">
            <div class="bg-[#0a0a0a] border border-white/5 p-10 rounded-[3rem] sticky top-12">
                <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Status Propagation</h3>
                
                <!-- Future Stripe/Finance Placeholder -->
                <div class="mb-8 p-6 bg-amber-600/5 border border-amber-600/10 rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-widest text-amber-600/40 mb-2 italic">Future Integration Point</p>
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-widest text-white/20">Stripe Sync</span>
                        <span class="px-3 py-1 bg-white/5 rounded-full text-[7px] font-black uppercase tracking-widest text-white/10">Disabled</span>
                    </div>
                </div>

                <?php if (isset($review_msg)): ?>

                    <div class="mb-8 px-6 py-3 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-xl"><?php echo $review_msg; ?></div>
                <?php endif; ?>
                <form action="order-view.php?id=<?php echo $order['id']; ?>" method="POST" class="space-y-8">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/20 block mb-4">Current Phase</label>
                        <select name="status" class="w-full bg-stone-900 border border-white/5 p-4 rounded-xl text-white text-[10px] font-black uppercase tracking-widest focus:outline-none focus:border-amber-600 transition-all appearance-none">
                            <?php foreach (['Paid', 'Preparing', 'Out for Delivery', 'Shipped', 'Delivered', 'Cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $order['status'] == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/20 block mb-4">Tracking Number</label>
                        <input name="tracking_number" type="text" value="<?php echo e($order['tracking_number']); ?>" placeholder="NUL_UNASSIGNED" class="w-full bg-stone-900 border border-white/5 p-4 rounded-xl text-white text-[10px] font-black uppercase tracking-widest focus:outline-none focus:border-amber-600 transition-all">
                    </div>
                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/20 block mb-4">ETA Projection</label>
                        <input name="eta" type="text" value="<?php echo e($order['eta']); ?>" placeholder="TBD" class="w-full bg-stone-900 border border-white/5 p-4 rounded-xl text-white text-[10px] font-black uppercase tracking-widest focus:outline-none focus:border-amber-600 transition-all">
                    </div>
                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/20 block mb-4">Delivery Signature</label>
                        <input name="delivery_signature" type="text" value="<?php echo e($order['delivery_signature']); ?>" placeholder="NOT_SIGNED" class="w-full bg-stone-900 border border-white/5 p-4 rounded-xl text-white text-[10px] font-black uppercase tracking-widest focus:outline-none focus:border-amber-600 transition-all">
                    </div>
                    <button type="submit" class="w-full py-5 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] hover:bg-amber-600 hover:text-white transition-all rounded-2xl shadow-2xl">Update Registry</button>
                </form>

                <?php if ($order['status'] === 'Delivered' && !$order['review_email_sent']): ?>
                <form action="order-view.php?id=<?php echo $order['id']; ?>" method="POST" class="mt-8 pt-8 border-t border-white/5">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="trigger_review" value="1">

                    <button type="submit" class="w-full py-4 border border-amber-600/30 text-amber-600 font-black uppercase text-[9px] tracking-[0.3em] hover:bg-amber-600 hover:text-white transition-all rounded-xl">Dispatch Review Inquiry</button>
                </form>
                <?php endif; ?>

                <!-- Future Signature Capture Placeholder -->
                <div class="mt-8 pt-8 border-t border-white/5">
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/10 mb-4 italic">Proof of Possession (Future)</p>
                    <div class="h-24 bg-white/[0.01] border border-dashed border-white/5 rounded-xl flex items-center justify-center">
                        <span class="text-[8px] font-black uppercase tracking-widest text-white/5 font-mono">Signature Canvas Placeholder</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</main></div></body></html>
