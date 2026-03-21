<?php
$pageTitle = 'Operational Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $db->query("SELECT SUM(total) FROM orders")->fetchColumn() ?: 0;
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Paid'")->fetchColumn();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
    <div class="bg-[#0a0a0a] border border-white/5 p-10 rounded-[2.5rem] relative overflow-hidden group">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20 mb-4">Total Revenue Generated</p>
        <h3 class="text-5xl font-serif font-bold text-white tracking-tighter">$<?php echo number_format($totalRevenue, 2); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-10 rounded-[2.5rem] relative overflow-hidden group">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20 mb-4">Orders to Process</p>
        <h3 class="text-5xl font-serif font-bold text-amber-600 tracking-tighter"><?php echo str_pad($pendingOrders, 2, '0', STR_PAD_LEFT); ?></h3>
    </div>
    <div class="bg-[#0a0a0a] border border-white/5 p-10 rounded-[2.5rem] relative overflow-hidden group">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-white/20 mb-4">Total Orders</p>
        <h3 class="text-5xl font-serif font-bold text-white tracking-tighter"><?php echo str_pad($totalOrders, 2, '0', STR_PAD_LEFT); ?></h3>
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
                <p class="text-[10px] font-black uppercase tracking-widest text-white/40 group-hover:text-amber-500 transition-colors">View Inquiries</p>
            </a>
        </div>
    </div>
</div>

</main></div></body></html>
