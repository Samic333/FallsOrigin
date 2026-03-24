<?php
$pageTitle = 'Operational Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT SUM(total) FROM orders")->fetchColumn() ?: 0;
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'paid', 'preparing')")->fetchColumn();
$shippedOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'shipped'")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStockProducts = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5")->fetchColumn();
$unreadMessages = $db->query("SELECT COUNT(*) FROM contact_messages WHERE LOWER(status) = 'unread'")->fetchColumn();
$pendingReviews = $db->query("SELECT COUNT(*) FROM reviews WHERE LOWER(status) = 'pending'")->fetchColumn();
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Total Revenue</p>
        <h3 class="text-4xl font-serif font-bold text-white tracking-tighter">$<?php echo number_format($totalRevenue, 2); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Total Orders</p>
        <h3 class="text-4xl font-serif font-bold text-white tracking-tighter"><?php echo str_pad($totalOrders, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Pending Orders</p>
        <h3 class="text-4xl font-serif font-bold text-amber-600 tracking-tighter"><?php echo str_pad($pendingOrders, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Shipped Orders</p>
        <h3 class="text-4xl font-serif font-bold text-white/70 tracking-tighter"><?php echo str_pad($shippedOrders, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Total Products</p>
        <h3 class="text-4xl font-serif font-bold text-white tracking-tighter"><?php echo str_pad($totalProducts, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Low-Stock</p>
        <h3 class="text-4xl font-serif font-bold text-red-500 tracking-tighter"><?php echo str_pad($lowStockProducts, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Unread Msgs</p>
        <h3 class="text-4xl font-serif font-bold text-amber-600 tracking-tighter"><?php echo str_pad($unreadMessages, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/50 mb-3">Pending Reviews</p>
        <h3 class="text-4xl font-serif font-bold text-amber-600 tracking-tighter"><?php echo str_pad($pendingReviews, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
    <div class="bg-[#0a0a0a] border border-white/5 p-10 rounded-[3rem]">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-8">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-6">
            <a href="orders.php" class="p-6 bg-white/5 border border-white/5 rounded-2xl hover:border-amber-600/30 transition-all group">
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 group-hover:text-amber-500 transition-colors">Manage Orders</p>
            </a>
            <a href="messages.php" class="p-6 bg-white/5 border border-white/5 rounded-2xl hover:border-amber-600/30 transition-all group">
                <p class="text-[10px] font-black uppercase tracking-widest text-white/70 group-hover:text-amber-500 transition-colors">View Inquiries</p>
            </a>
        </div>
    </div>
</div>

</main></div></body></html>
