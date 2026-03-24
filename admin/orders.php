<?php
$pageTitle = 'Order Registry';
require_once __DIR__ . '/includes/header.php';

$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($status !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $query .= " AND (id LIKE ? OR customer_name LIKE ? OR email LIKE ?)";
    $searchWild = '%' . $search . '%';
    $params[] = $searchWild;
    $params[] = $searchWild;
    $params[] = $searchWild;
}

$query .= " ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden">
    <div class="p-10 border-b border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Full Transaction Ledger</h3>
        <form action="orders.php" method="GET" class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search ID / Name..." class="w-full md:w-auto bg-white/[0.02] border border-white/5 p-3 rounded-full text-white text-[10px] font-black uppercase tracking-widest outline-none focus:border-amber-600 px-6">
            <select name="status" onchange="this.form.submit()" class="w-full md:w-auto bg-white/[0.02] border border-white/5 p-3 rounded-full text-white text-[10px] font-black uppercase tracking-widest outline-none focus:border-amber-600 appearance-none px-6">
                <option value="all">ALL STATUSES</option>
                <?php foreach (['pending', 'paid', 'preparing', 'shipped', 'delivered', 'cancelled', 'refunded'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $status == $s ? 'selected' : ''; ?>><?php echo mb_strtoupper($s); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-6 py-3 bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full transition-colors w-full md:w-auto hidden md:block">Search</button>
        </form>
    </div>
    <div class="overflow-x-auto no-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[9px] font-black uppercase tracking-[0.3em] text-white/20 border-b border-white/5">
                    <th class="px-10 py-6">Identity</th>
                    <th class="px-10 py-6">Customer</th>
                    <th class="px-10 py-6">Status</th>
                    <th class="px-10 py-6 text-right">Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr onclick="window.location='order-view.php?id=<?php echo $order['id']; ?>'" class="hover:bg-white/[0.01] transition-colors cursor-pointer group border-b border-white/[0.02]">
                    <td class="px-10 py-8">
                        <span class="text-[11px] font-bold font-mono tracking-tighter group-hover:text-amber-600 transition-colors"><?php echo $order['id']; ?></span>
                    </td>
                    <td class="px-10 py-8">
                        <span class="text-xs font-bold uppercase tracking-tight text-white"><?php echo e($order['customer_name']); ?></span>
                    </td>
                    <td class="px-10 py-8">
                        <span class="px-4 py-1.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-white/5 text-white/40 border border-white/5">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    <td class="px-10 py-8 text-right font-serif font-bold text-lg text-white">
                        $<?php echo number_format($order['total'], 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main></div></body></html>
