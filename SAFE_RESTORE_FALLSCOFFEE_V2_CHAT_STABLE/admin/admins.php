<?php
$pageTitle = 'Identity Management';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF validation failed.');
    }
    
    // Add Admin
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($username && $email && $password) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed]);
                log_admin_action('Create Identity', "Admin user {$username} created.");
                $success = "Identity deployed successfully.";
            }
        } else {
            $error = "All fields are required.";
        }
    }
    
    // Delete Admin
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        // Prevent deleting oneself
        $stmt = $db->prepare("SELECT username FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $targetUser = $stmt->fetchColumn();
        
        if ($targetUser === $_SESSION['admin_user']) {
            $error = "Cannot terminate active session identity.";
        } else {
            $stmt = $db->prepare("DELETE FROM admin_users WHERE id = ?");
            $stmt->execute([$id]);
            log_admin_action('Terminate Identity', "Admin user ID {$id} terminated.");
            $success = "Identity terminated.";
        }
    }
}

$admins = $db->query("SELECT * FROM admin_users ORDER BY id ASC")->fetchAll();
?>

<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2">
            <div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden">
                <div class="p-10 border-b border-white/5 flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Active Identities</h3>
                </div>
                
                <?php if ($success): ?>
                    <div class="px-10 py-5 bg-green-500/10 border-b border-green-500/20 text-green-500 text-[10px] font-black uppercase tracking-widest text-center">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="px-10 py-5 bg-red-500/10 border-b border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest text-center">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-[1px] bg-white/[0.03]">
                    <?php foreach ($admins as $adm): ?>
                        <div class="bg-[#0a0a0a] p-10 flex justify-between items-center border-b border-white/[0.02] group">
                            <div>
                                <p class="text-xs font-black uppercase tracking-widest text-white mb-1"><?php echo e($adm['username']); ?></p>
                                <p class="text-[10px] text-white/40 uppercase tracking-widest"><?php echo e($adm['email']); ?></p>
                            </div>
                            <div class="flex items-center gap-6">
                                <?php if ($adm['username'] === $_SESSION['admin_user']): ?>
                                    <span class="px-4 py-1.5 bg-amber-600/10 border border-amber-600/30 text-amber-600 rounded-full text-[8px] font-black uppercase tracking-widest">Active</span>
                                <?php else: ?>
                                    <form action="admins.php" method="POST" onsubmit="return confirm('Terminate this identity?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $adm['id']; ?>">
                                        <button type="submit" class="text-[9px] font-black uppercase tracking-widest text-red-500 hover:text-red-400 transition-colors">Terminate</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] p-10 sticky top-12">
                <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-white mb-8">Deploy Identity</h3>
                <form action="admins.php" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/40 block mb-3">Username</label>
                        <input type="text" name="username" required class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600 transition-all">
                    </div>
                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/40 block mb-3">Electronic Mail</label>
                        <input type="email" name="email" required class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600 transition-all">
                    </div>
                    <div>
                        <label class="text-[8px] font-black uppercase tracking-widest text-white/40 block mb-3">Security Key</label>
                        <input type="password" name="password" required class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600 transition-all">
                    </div>
                    <button type="submit" class="w-full py-5 bg-amber-600 hover:bg-amber-500 text-white font-black uppercase text-[10px] tracking-[0.4em] rounded-2xl transition-all shadow-xl mt-4">
                        Initialize Identity
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</main></div></body></html>
