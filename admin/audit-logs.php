<?php
require_once __DIR__ . '/includes/header.php';

// RBAC: Only super_admin or admin can view logs
if (!in_array($_SESSION['admin_role'], ['super_admin', 'admin'])) {
    header("Location: dashboard.php?error=Unauthorized_Access"); exit;
}

$db = DB::getInstance();
$logs = $db->query("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT 100")->fetchAll();
?>

<div class="p-8">
    <div class="flex justify-between items-center mb-12">
        <div>
            <h1 class="text-4xl font-serif font-bold text-white uppercase tracking-tighter">Security Audit Trail</h1>
            <p class="text-white/40 text-[10px] font-black uppercase tracking-[0.3em] mt-2 italic shadow-amber-600/20">Operational Integrity Ledger</p>
        </div>
        <div class="flex gap-4">
            <span class="px-6 py-2 bg-white/5 border border-white/10 rounded-full text-[10px] font-black uppercase tracking-widest text-white/40">Total Events: <?php echo count($logs); ?></span>
        </div>
    </div>

    <div class="bg-[#0a0a0a] border border-white/5 rounded-[2.5rem] overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-white/[0.02]">
                        <th class="px-8 py-6 text-[9px] font-black uppercase tracking-widest text-white/20">Timestamp</th>
                        <th class="px-8 py-6 text-[9px] font-black uppercase tracking-widest text-white/20">Administrator</th>
                        <th class="px-8 py-6 text-[9px] font-black uppercase tracking-widest text-white/20">Action Event</th>
                        <th class="px-8 py-6 text-[9px] font-black uppercase tracking-widest text-white/20">Contextual Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-white/20 text-[10px] font-black uppercase tracking-widest italic">No security events recorded</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $log): 
                        $badgeClass = (strpos($log['action'], 'Delete') !== false || strpos($log['action'], 'Security') !== false) ? 'text-red-500 bg-red-500/10 border-red-500/20' : 'text-amber-500 bg-amber-500/10 border-amber-500/20';
                    ?>
                        <tr class="hover:bg-white/[0.01] transition-colors group">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <span class="text-[10px] font-mono text-white/40"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-[10px] font-black text-white/40 border border-white/10"><?php echo strtoupper(substr($log['admin_user'], 0, 1)); ?></div>
                                    <span class="text-xs font-bold text-white uppercase tracking-tight"><?php echo htmlspecialchars($log['admin_user']); ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest border <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-[11px] text-white/60 line-clamp-1 group-hover:line-clamp-none transition-all"><?php echo htmlspecialchars($log['details']); ?></p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
