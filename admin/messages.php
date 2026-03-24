<?php
$pageTitle = 'Message Registry';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$view = $_GET['view'] ?? 'active';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    if (isset($_POST['archive_id'])) {
        $id = (int)$_POST['archive_id'];
        $db->prepare("UPDATE contact_messages SET archived_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$id]);
        log_admin_action('Archive Message', "Inquiry #{$id} moved to archives.");
        $info = "Communication archived.";
    }

    if (isset($_POST['restore_id'])) {
        $id = (int)$_POST['restore_id'];
        $db->prepare("UPDATE contact_messages SET archived_at = NULL WHERE id = ?")->execute([$id]);
        log_admin_action('Restore Message', "Inquiry #{$id} restored to active registry.");
        $info = "Communication restored to active inbox.";
    }

    if (isset($_POST['perm_delete_id'])) {
        $id = (int)$_POST['perm_delete_id'];
        $db->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        log_admin_action('Permanent Delete', "Inquiry #{$id} purged from database.");
        $info = "Communication record permanently deleted.";
    }
}

$where = ($view === 'archived') ? "archived_at IS NOT NULL" : "archived_at IS NULL";
$messages = $db->query("SELECT * FROM contact_messages WHERE $where AND deleted_at IS NULL ORDER BY created_at DESC")->fetchAll();
?>

<div class="max-w-5xl">
    <div class="flex justify-between items-end mb-10 pb-6 border-b border-white/5">
        <div>
            <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-white/40 mb-2 italic">Provenance Logs</h3>
            <div class="flex items-center gap-8">
                <a href="?view=active" class="text-xl font-serif font-bold uppercase tracking-tight transition-all <?php echo $view === 'active' ? 'text-white border-b-2 border-amber-600 pb-1' : 'text-white/30 hover:text-white'; ?>">Active Influx</a>
                <a href="?view=archived" class="text-xl font-serif font-bold uppercase tracking-tight transition-all <?php echo $view === 'archived' ? 'text-white border-b-2 border-amber-600 pb-1' : 'text-white/30 hover:text-white'; ?>">Archived Vault</a>
            </div>
        </div>
        <?php if (isset($info)): ?>
            <div class="px-6 py-2 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-full italic animate-pulse"><?php echo $info; ?></div>
        <?php endif; ?>
    </div>

    <div class="bg-[#0a0a0a] border border-white/10 rounded-[3rem] overflow-hidden shadow-2xl">
        <div class="space-y-[1px] bg-white/[0.03]">
            <?php if (empty($messages)): ?>
                <div class="p-24 text-center bg-[#0a0a0a]"><p class="text-white/30 text-[10px] font-black uppercase tracking-[0.4em]">Zero frequencies detected in this sector.</p></div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <div class="bg-[#0a0a0a] p-12 group hover:bg-white/[0.01] transition-all border-b border-white/[0.05] flex justify-between items-center">
                    <div class="flex-grow">
                        <div class="flex items-center gap-6 mb-5">
                            <span class="text-[9px] font-black font-mono text-white/40 tracking-widest border border-white/10 px-2 py-0.5 rounded">#<?php echo $m['id']; ?></span>
                            <?php if($m['status'] === 'Unread'): ?>
                                <span class="text-[8px] font-black uppercase tracking-widest text-amber-500 bg-amber-500/10 px-3 py-1 rounded shadow-sm">NEW INQUIRY</span>
                            <?php elseif($m['status'] === 'Replied'): ?>
                                <span class="text-[8px] font-black uppercase tracking-widest text-green-400 bg-green-400/10 px-3 py-1 rounded">REPLIED</span>
                            <?php endif; ?>
                        </div>
                        <h4 class="text-2xl font-serif font-bold text-white tracking-tight mb-3"><?php echo e($m['subject']); ?></h4>
                        <div class="flex items-center gap-6 text-[10px] font-black uppercase tracking-widest text-white/60 mb-5">
                            <span class="text-amber-600/80"><?php echo e($m['name']); ?></span>
                            <span class="text-white/10">/</span>
                            <span><?php echo e($m['email']); ?></span>
                            <span class="text-white/10">/</span>
                            <span class="font-mono text-[9px]"><?php echo date('M d, Y', strtotime($m['created_at'])); ?></span>
                        </div>
                        <p class="text-white/40 text-[12px] leading-relaxed line-clamp-1 max-w-2xl font-medium"><?php echo e($m['message']); ?></p>
                    </div>
                    
                    <div class="flex items-center gap-10 pl-16 border-l border-white/5">
                        <a href="message-view.php?id=<?php echo $m['id']; ?>" class="text-[10px] font-black uppercase tracking-widest text-white/60 hover:text-white transition-colors bg-white/5 px-6 py-3 rounded-full border border-white/5 hover:border-white/20">View</a>
                        
                        <?php if($view === 'active'): ?>
                        <form action="messages.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <input type="hidden" name="archive_id" value="<?php echo $m['id']; ?>">
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-white/30 hover:text-amber-600 transition-colors">Archive</button>
                        </form>
                        <?php else: ?>
                        <form action="messages.php?view=archived" method="POST" class="flex items-center gap-6">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <input type="hidden" name="restore_id" value="<?php echo $m['id']; ?>">
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-green-500/60 hover:text-green-500 transition-colors">Restore</button>
                            
                            <input type="hidden" name="perm_delete_id" value="<?php echo $m['id']; ?>">
                            <button type="submit" onclick="return confirm('WARNING: THIS ACTION IS IRREVERSIBLE. PURGE THIS RECORD FROM THE SOURCE?');" form="none" class="text-[10px] font-black uppercase tracking-widest text-red-500/40 hover:text-red-500 transition-colors" name="perm_delete_btn">Purge</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('button[name="perm_delete_btn"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if(!confirm('WARNING: THIS ACTION IS IRREVERSIBLE. PURGE THIS RECORD FROM THE SOURCE?')) {
            e.preventDefault();
            return false;
        }
        this.closest('form').querySelector('input[name="restore_id"]').remove();
        this.closest('form').submit();
    });
});
</script>

</main></div></body></html>
