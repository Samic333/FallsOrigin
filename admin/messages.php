<?php
$pageTitle = 'Message Influx';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    if (isset($_POST['mark_read'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = ? AND status = 'Unread'");
        $stmt->execute([$id]);
        log_admin_action('Read Message', "Inquiry #{$id} marked as read.");
    }

    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $stmt = $db->prepare("UPDATE contact_messages SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        log_admin_action('Delete Message', "Inquiry #{$id} soft-deleted.");
        $msg = "Communication record archived.";
    }
}

$messages = $db->query("SELECT * FROM contact_messages WHERE deleted_at IS NULL ORDER BY created_at DESC")->fetchAll();
?>

<div class="max-w-5xl">
    <div class="flex justify-between items-center mb-10">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Inquiry Registry</h3>
        <?php if (isset($msg)): ?>
            <div class="px-6 py-2 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-full italic"><?php echo $msg; ?></div>
        <?php endif; ?>
    </div>

    <div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden">
        <div class="space-y-[1px] bg-white/[0.03]">
            <?php if (empty($messages)): ?>
                <div class="p-20 text-center bg-[#0a0a0a]"><p class="text-white/20 text-[10px] font-black uppercase tracking-[0.4em]">Noise floor at zero. No active frequencies.</p></div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <div class="bg-[#0a0a0a] p-10 group hover:bg-white/[0.01] transition-all border-b border-white/[0.02] flex justify-between items-center">
                    <div class="flex-grow">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-[8px] font-black font-mono text-white/20 tracking-widest">#<?php echo $m['id']; ?></span>
                            <?php if($m['status'] === 'Unread'): ?>
                                <span class="text-[7px] font-black uppercase tracking-widest text-amber-600 bg-amber-600/10 px-2 py-0.5 rounded border border-amber-600/20">NEW</span>
                            <?php elseif($m['status'] === 'Replied'): ?>
                                <span class="text-[7px] font-black uppercase tracking-widest text-green-500 bg-green-500/10 px-2 py-0.5 rounded border border-green-500/20">REPLIED</span>
                            <?php endif; ?>
                        </div>
                        <h4 class="text-lg font-serif font-bold text-white uppercase tracking-tight mb-2"><?php echo e($m['subject']); ?></h4>
                        <div class="flex items-center gap-4 text-[9px] font-black uppercase tracking-widest text-white/40 mb-4">
                            <span><?php echo e($m['name']); ?></span>
                            <span class="text-white/10">•</span>
                            <span><?php echo e($m['email']); ?></span>
                            <span class="text-white/10">•</span>
                            <span class="font-mono text-[8px]"><?php echo date('M d, H:i', strtotime($m['created_at'])); ?></span>
                        </div>
                        <p class="text-white/30 text-[10px] uppercase font-medium line-clamp-1 max-w-2xl"><?php echo e($m['message']); ?></p>
                    </div>
                    
                    <div class="flex items-center gap-8 pl-12 border-l border-white/5">
                        <a href="message-view.php?id=<?php echo $m['id']; ?>" class="text-[9px] font-black uppercase tracking-widest text-white/40 hover:text-white transition-colors">View & Reply</a>
                        <form action="messages.php" method="POST" onsubmit="return confirm('Archive this communication permanently?');">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <input type="hidden" name="delete_id" value="<?php echo $m['id']; ?>">
                            <button type="submit" class="text-[9px] font-black uppercase tracking-widest text-red-500/40 hover:text-red-500 transition-colors">Archive</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</main></div></body></html>
