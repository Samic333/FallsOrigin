<?php
$pageTitle = 'Message Influx';
require_once __DIR__ . '/../includes/admin_header.php';

$db = DB::getInstance();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$id]);
    log_admin_action('Read Message', "Inquiry #{$id} marked as read.");
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden max-w-5xl">
    <div class="p-10 border-b border-white/5 flex justify-between items-center">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Inquiry Registry</h3>
    </div>
    <div class="space-y-[1px] bg-white/[0.03]">
        <?php if (empty($messages)): ?>
            <div class="p-20 text-center bg-[#0a0a0a]"><p class="text-white/20 text-[10px] font-black uppercase tracking-[0.4em]">Noise floor at zero. No new frequencies.</p></div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
            <div class="bg-[#0a0a0a] p-10 group hover:bg-white/[0.01] transition-all border-b border-white/[0.02]">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-[0.4em] text-amber-600 mb-2 block">Direct Communication</span>
                        <h4 class="text-xl font-serif font-bold text-white uppercase tracking-tight"><?php echo e($msg['subject']); ?></h4>
                    </div>
                    <span class="text-[8px] font-black uppercase tracking-widest text-white/20 font-mono"><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></span>
                </div>
                <div class="flex items-center space-x-4 mb-8">
                    <div class="px-4 py-1.5 bg-white/5 rounded-full border border-white/10"><span class="text-[8px] font-black uppercase tracking-widest text-white"><?php echo e($msg['name']); ?></span></div>
                    <span class="text-[8px] font-black uppercase tracking-widest text-white/30 font-mono"><?php echo e($msg['email']); ?></span>
                    <?php if($msg['status'] === 'unread'): ?>
                        <span class="text-[8px] font-black uppercase tracking-widest text-amber-600 border border-amber-600/30 px-3 py-1 rounded-full bg-amber-600/5">NEW</span>
                    <?php endif; ?>
                </div>
                <p class="text-white/40 text-[11px] leading-relaxed font-medium uppercase tracking-tight max-w-3xl"><?php echo nl2br(e($msg['message'])); ?></p>
                <div class="mt-8 flex items-center space-x-8">
                    <a href="mailto:<?php echo $msg['email']; ?>" class="text-[9px] font-black uppercase tracking-widest text-white/40 hover:text-white transition-colors">Acknowledge via Mail</a>
                    <?php if($msg['status'] === 'unread'): ?>
                        <form action="messages.php" method="POST" class="inline">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="text-[9px] font-black uppercase tracking-widest text-amber-600/60 hover:text-amber-600 transition-colors">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</main></div></body></html>
