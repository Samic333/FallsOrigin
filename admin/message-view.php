<?php
$pageTitle = 'Communication History';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/mail.php';

$db = DB::getInstance();
$id = $_GET['id'] ?? null;

if (!$id) { header('Location: messages.php'); exit; }

// Get primary record to identify email/thread
$stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$mainMsg = $stmt->fetch();

if (!$mainMsg) { header('Location: messages.php'); exit; }

$email = $mainMsg['email'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    if (isset($_POST['reply_body'])) {
        $reply = $_POST['reply_body'];
        $subject = "Re: " . $mainMsg['subject'];
        
        require_once __DIR__ . '/../includes/classes/Mailer.php';
        $mailer = Mailer::getInstance();

        if ($mailer->send($email, $subject, $reply)) {
            // 1. Log reply on the MAIN thread record for legacy/status
            $db->prepare("UPDATE contact_messages SET status = 'Replied', reply_content = ?, replied_at = CURRENT_TIMESTAMP WHERE id = ?")
               ->execute([$reply, $mainMsg['id']]);
            
            // 2. Insert as a NEW chat record for history
            $db->prepare("INSERT INTO contact_messages (parent_id, name, email, subject, message, direction, status) VALUES (?, ?, ?, ?, ?, 'outbound', 'Replied')")
               ->execute([$mainMsg['id'], 'Admin', $email, $subject, $reply]);
            
            log_admin_action('Reply Message', "Replied to inquiry #{$id}.");
            header("Location: message-view.php?id=$id&info=Reply+transmitted"); exit;
        } else {
            $error = "Failed to dispatch email. Check SMTP configuration.";
        }
    }

    // Lifecycle actions on the MAIN record
    if (isset($_POST['archive_msg'])) {
        $db->prepare("UPDATE contact_messages SET archived_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$id]);
        header('Location: messages.php?info=Thread+archived'); exit;
    }
    if (isset($_POST['restore_msg'])) {
        $db->prepare("UPDATE contact_messages SET archived_at = NULL WHERE id = ?")->execute([$id]);
        header("Location: message-view.php?id=$id&info=Restored+to+active"); exit;
    }
    if (isset($_POST['delete_perm'])) {
        $db->prepare("DELETE FROM contact_messages WHERE id = ? OR parent_id = ? OR email = ?")->execute([$id, $id, $email]);
        header('Location: messages.php?info=Entire+conversation+purged'); exit;
    }
}

// Auto-mark as read
if ($mainMsg['status'] === 'Unread') {
    $db->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = ?")->execute([$id]);
    $mainMsg['status'] = 'Read';
}

// Fetch entire conversation thread for this email
$history = $db->prepare("SELECT * FROM contact_messages WHERE email = ? AND deleted_at IS NULL ORDER BY created_at ASC");
$history->execute([$email]);
$messages = $history->fetchAll();

$info = $_GET['info'] ?? '';
?>

<div class="max-w-4xl">
    <div class="mb-12 flex justify-between items-center">
        <a href="messages.php" class="flex items-center space-x-3 text-[10px] text-white/60 hover:text-amber-600 transition-all uppercase tracking-[0.3em] font-black group">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left group-hover:-translate-x-1 transition-transform"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            <span>Return to Registry</span>
        </a>
        <div class="flex items-center gap-6">
            <?php if (!$mainMsg['archived_at']): ?>
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
            <form action="" method="POST" onsubmit="return confirm('DANGER: Purge ENTIRE history with this email?');">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <button type="submit" name="delete_perm" class="text-[10px] font-black uppercase tracking-widest text-red-500/40 hover:text-red-500 transition-colors">Purge History</button>
            </form>
        </div>
    </div>

    <div class="space-y-12 mb-16">
        <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-white/30 italic">Conversation Logs / <?php echo e($email); ?></h3>
        
        <?php foreach ($messages as $m): ?>
            <?php $isOutbound = ($m['direction'] === 'outbound'); ?>
            <div class="flex <?php echo $isOutbound ? 'justify-end' : 'justify-start'; ?>">
                <div class="max-w-[85%] <?php echo $isOutbound ? 'bg-amber-600/10 border-amber-600/30' : 'bg-[#0a0a0a] border-white/10'; ?> border p-10 rounded-[2.5rem] shadow-xl relative">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-[8px] font-black uppercase tracking-widest <?php echo $isOutbound ? 'text-amber-500' : 'text-white/40'; ?>">
                            <?php echo $isOutbound ? 'OUTGOING FREQUENCY' : 'INCOMING FREQUENCY'; ?>
                        </span>
                        <span class="text-[9px] font-mono text-white/30">
                            <?php echo date('M d, H:i', strtotime($m['created_at'])); ?>
                        </span>
                    </div>
                    <?php if (!$isOutbound && $m['subject'] !== $mainMsg['subject']): ?>
                        <p class="text-[10px] font-bold text-white/60 mb-3 italic">Regarding: <?php echo e($m['subject']); ?></p>
                    <?php endif; ?>
                    <p class="text-white/90 text-lg leading-relaxed font-medium">
                        <?php echo nl2br(e($m['message'])); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Dispatch Station -->
    <div class="bg-[#0a0a0a] border border-white/10 p-12 rounded-[3.5rem] shadow-2xl">
        <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-white/80 mb-10 italic">Dispatch Station</h3>
        
        <?php if($info): ?>
            <div class="mb-8 p-4 bg-green-500/10 border border-green-500/20 text-green-500 text-[10px] font-black uppercase tracking-widest rounded-3xl text-center italic"><?php echo e($info); ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest rounded-3xl text-center italic"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <div>
                <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-4 ml-2">Secure Payload (Auto-Threaded)</label>
                <textarea name="reply_body" rows="6" required placeholder="Enter follow-up message..." class="w-full bg-white/[0.03] border border-white/10 p-8 rounded-[2rem] text-white text-lg outline-none focus:border-amber-600 transition-all font-medium no-scrollbar shadow-inner"></textarea>
            </div>
            <button type="submit" class="w-full py-7 bg-white text-black font-black uppercase text-[11px] tracking-[0.6em] hover:bg-amber-600 hover:text-white transition-all rounded-full shadow-2xl">
                Acknowledge & Dispatch
            </button>
        </form>
    </div>
</div>

</main></div></body></html>
