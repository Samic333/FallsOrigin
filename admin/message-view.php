<?php
$pageTitle = 'Communication Detail';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/mail.php';

$db = DB::getInstance();
$id = $_GET['id'] ?? null;

if (!$id) { header('Location: messages.php'); exit; }

$stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$msg = $stmt->fetch();

if (!$msg) { header('Location: messages.php'); exit; }

// Auto-mark as read when viewed
if ($msg['status'] === 'Unread') {
    $db->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = ?")->execute([$id]);
    $msg['status'] = 'Read';
}

$info = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    if (isset($_POST['reply_body'])) {
        $reply = $_POST['reply_body'];
        $subject = "Re: " . $msg['subject'];
        
        if (send_customer_email($msg['email'], $subject, $reply)) {
            $stmt = $db->prepare("UPDATE contact_messages SET status = 'Replied', reply_content = ?, replied_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$reply, $id]);
            log_admin_action('Reply Message', "Replied to inquiry #{$id}.");
            $info = "Reply transmitted successfully.";
            
            $msg['status'] = 'Replied';
            $msg['reply_content'] = $reply;
            $msg['replied_at'] = date('Y-m-d H:i:s');
        } else {
            $error = "Failed to dispatch email. Check SMTP configuration.";
        }
    }

    if (isset($_POST['archive_msg'])) {
        $db->prepare("UPDATE contact_messages SET archived_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$id]);
        header('Location: messages.php?info=Communication+archived'); exit;
    }

    if (isset($_POST['restore_msg'])) {
        $db->prepare("UPDATE contact_messages SET archived_at = NULL WHERE id = ?")->execute([$id]);
        header('Location: message-view.php?id='.$id.'&info=Restored+to+active+registry'); exit;
    }

    if (isset($_POST['delete_perm'])) {
        $db->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        header('Location: messages.php?info=Record+purged'); exit;
    }
}

$info = $info ?: ($_GET['info'] ?? '');
?>

<div class="max-w-4xl">
    <div class="mb-12 flex justify-between items-center">
        <a href="messages.php" class="flex items-center space-x-3 text-[10px] text-white/60 hover:text-amber-600 transition-all uppercase tracking-[0.3em] font-black group">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left group-hover:-translate-x-1 transition-transform"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            <span>Return to Registry</span>
        </a>
        <div class="flex items-center gap-6">
            <?php if (!$msg['archived_at']): ?>
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <button type="submit" name="archive_msg" class="text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-amber-600 transition-colors">Archive</button>
                </form>
            <?php else: ?>
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <button type="submit" name="restore_msg" class="text-[10px] font-black uppercase tracking-widest text-green-500/60 hover:text-green-500 transition-colors">Restore</button>
                </form>
            <?php endif; ?>
            <form action="" method="POST" onsubmit="return confirm('DANGER: This purged the record from the database. Proceed?');">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <button type="submit" name="delete_perm" class="text-[10px] font-black uppercase tracking-widest text-red-500/40 hover:text-red-500 transition-colors">Purge Permanently</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-12">
        <!-- Original Message -->
        <div class="bg-[#0a0a0a] border border-white/10 p-12 rounded-[3rem] shadow-xl">
            <div class="flex justify-between items-start mb-10">
                <div>
                    <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-2 italic">Incoming Frequency</h2>
                    <h1 class="text-4xl font-serif font-bold text-white tracking-tighter"><?php echo e($msg['subject']); ?></h1>
                </div>
                <div class="text-right">
                    <span class="text-[8px] font-black uppercase tracking-widest text-white/60 block mb-2 font-mono">Registry ID #<?php echo $msg['id']; ?></span>
                    <span class="text-[10px] font-bold text-white/50 font-mono"><?php echo date('F d, Y @ H:i', strtotime($msg['created_at'])); ?></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-12 mb-12 py-8 border-y border-white/5">
                <div>
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/40 mb-3 italic">Identity</p>
                    <p class="text-white text-sm font-bold tracking-tight"><?php echo e($msg['name']); ?></p>
                </div>
                <div>
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/40 mb-3 italic">Electronic Mail</p>
                    <p class="text-amber-600 text-sm font-bold tracking-tight"><?php echo e($msg['email']); ?></p>
                </div>
            </div>

            <div class="mb-12">
                <p class="text-white/80 text-lg leading-relaxed font-medium selection:bg-amber-600/30">
                    <?php echo nl2br(e($msg['message'])); ?>
                </p>
            </div>
        </div>

        <!-- Reply Sector -->
        <?php if($msg['status'] === 'Replied'): ?>
            <div class="bg-amber-600/5 border border-amber-600/20 p-12 rounded-[3rem] shadow-inner">
                <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-8 italic">Outgoing Response</h3>
                <div class="mb-8">
                    <p class="text-white text-lg leading-relaxed font-medium">
                        <?php echo nl2br(e($msg['reply_content'])); ?>
                    </p>
                </div>
                <div class="text-[9px] font-black uppercase tracking-widest text-white/40 pt-6 border-t border-white/5 italic">
                    Logged in Registry on <?php echo date('F d, Y @ H:i', strtotime($msg['replied_at'])); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-[#0a0a0a] border border-white/10 p-12 rounded-[3rem]">
                <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-white/80 mb-10">Dispatch Response</h3>
                
                <?php if($info): ?>
                    <div class="mb-8 p-4 bg-green-500/10 border border-green-500/20 text-green-500 text-[10px] font-black uppercase tracking-widest rounded-2xl text-center italic"><?php echo $info; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest rounded-2xl text-center italic"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="message-view.php?id=<?php echo $id; ?>" method="POST" class="space-y-8">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-4 ml-2">Response Payload (Standard Casing)</label>
                        <textarea name="reply_body" rows="8" required placeholder="Enter response content..." class="w-full bg-white/[0.03] border border-white/10 p-8 rounded-3xl text-white text-lg outline-none focus:border-amber-600 transition-all font-medium no-scrollbar shadow-inner"></textarea>
                    </div>
                    <button type="submit" class="w-full py-7 bg-white text-black font-black uppercase text-[11px] tracking-[0.6em] hover:bg-amber-600 hover:text-white transition-all rounded-full shadow-2xl">
                        Acknowledge & Dispatch
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

</main></div></body></html>
