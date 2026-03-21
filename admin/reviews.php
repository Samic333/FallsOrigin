<?php
$pageTitle = 'Public Sentiment';
require_once __DIR__ . '/../includes/admin_header.php';

$db = DB::getInstance();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    log_admin_action('Moderate Review', "Review #{$id} set to {$status}.");
    $msg = "Review status updated to " . strtoupper($status) . ".";
}

$reviews = $db->query("SELECT * FROM reviews ORDER BY created_at DESC")->fetchAll();
?>

<div class="max-w-5xl">
    <?php if (isset($msg)): ?>
        <div class="mb-10 px-6 py-4 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-2xl flex items-center justify-between">
            <span><?php echo $msg; ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden">
        <div class="p-10 border-b border-white/5 flex justify-between items-center"><h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Sentiment Moderation</h3></div>
        <div class="space-y-[1px] bg-white/[0.03]">
            <?php if (empty($reviews)): ?>
                <div class="p-20 text-center bg-[#0a0a0a]"><p class="text-white/20 text-[10px] font-black uppercase tracking-[0.4em]">Historical silence. No records found.</p></div>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                <div class="bg-[#0a0a0a] p-10 group transition-all border-b border-white/[0.02]">
                    <div class="flex justify-between items-start mb-8">
                        <div class="flex items-center space-x-6">
                            <div class="flex text-amber-600">
                                <?php for($i=0; $i<$rev['rating']; $i++): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="lucide lucide-star mr-1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <?php endfor; ?>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-white/40">Verified Acquisition</span>
                        </div>
                        <span class="<?php echo ($rev['status'] === 'pending') ? 'text-amber-600/60' : (($rev['status'] === 'approved') ? 'text-green-600/60' : 'text-red-600/60'); ?> text-[8px] font-black uppercase tracking-widest border border-white/5 bg-white/[0.02] px-4 py-1.5 rounded-full uppercase"><?php echo $rev['status']; ?></span>
                    </div>
                    <div class="mb-10">
                        <p class="text-white text-lg font-serif italic mb-4">"<?php echo e($rev['comment']); ?>"</p>
                        <p class="text-[10px] font-black text-white uppercase tracking-widest"><?php echo e($rev['customer_name']); ?></p>
                    </div>
                    <?php if($rev['status'] === 'pending'): ?>
                        <div class="flex space-x-6 border-t border-white/[0.02] pt-8">
                            <form action="reviews.php" method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="id" value="<?php echo $rev['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="text-[9px] font-black uppercase tracking-widest text-green-500/60 hover:text-green-500 transition-colors">Authorize Public Access</button>
                            </form>
                            <form action="reviews.php" method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="id" value="<?php echo $rev['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="text-[9px] font-black uppercase tracking-widest text-red-500/60 hover:text-red-500 transition-colors">Suppress Sentiment</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</main></div></body></html>
