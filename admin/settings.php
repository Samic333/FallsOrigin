<?php
$pageTitle = 'Control Settings';
require_once __DIR__ . '/../includes/admin_header.php';

$db = DB::getInstance();
$logs = $db->query("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT 50")->fetchAll();
$admins = $db->query("SELECT * FROM admin_users")->fetchAll();
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
    <!-- Audit Log -->
    <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem]">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Administrative Audit</h3>
        <div class="space-y-6">
            <?php foreach ($logs as $log): ?>
            <div class="flex items-start justify-between border-b border-white/[0.02] pb-6">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-amber-600 mb-1"><?php echo e($log['action']); ?></p>
                    <p class="text-white/40 text-[10px] font-medium uppercase tracking-tight"><?php echo e($log['details']); ?></p>
                </div>
                <p class="text-[8px] font-mono text-white/10 uppercase"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Admin Users -->
    <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem]">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Identity Management</h3>
        <div class="space-y-8">
            <?php foreach ($admins as $adm): ?>
            <div class="flex items-center justify-between bg-white/[0.01] border border-white/5 p-6 rounded-2xl">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-white"><?php echo e($adm['username']); ?></p>
                    <p class="text-[9px] text-white/20 uppercase tracking-widest mt-1"><?php echo e($adm['email']); ?></p>
                </div>
                <div class="text-[9px] font-black uppercase tracking-widest text-white/10">
                    Active
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</main></div></body></html>
