<?php
$pageTitle = 'Identity Management';
require_once __DIR__ . '/includes/header.php';

// Access Control: Only super_admin or admin can manage other users
if (!in_array($_SESSION['admin_role'], ['super_admin', 'admin'])) {
    die("Access Denied. Insufficient clearance for Identity Management.");
}

$db = DB::getInstance();
$error = '';
$success = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF validation failed.');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'] ?? '';
        $id = $_POST['id'] ?? null;

        try {
            if ($action === 'add') {
                if (empty($password)) throw new Exception("Security key required for new identities.");
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO admins (username, email, role, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $role, $hash]);
                $success = "New identity authorized: {$username}";
                log_admin_action('Create Admin', "Added new admin: {$username} as {$role}");
            } else {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE admins SET username=?, email=?, role=?, password_hash=? WHERE id=?");
                    $stmt->execute([$username, $email, $role, $hash, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE admins SET username=?, email=?, role=? WHERE id=?");
                    $stmt->execute([$username, $email, $role, $id]);
                }
                $success = "Identity updated: {$username}";
                log_admin_action('Edit Admin', "Modified admin: {$username} (ID: {$id})");
            }
        } catch (Exception $e) {
            $error = "Transmission Error: " . $e->getMessage();
        }
    }

    if ($action === 'delete' && $_SESSION['admin_role'] === 'super_admin') {
        $id = (int)$_POST['id'];
        if ($id === 1) { // Protect root admin
            $error = "Root protocol protection active. Cannot delete primary admin.";
        } else {
            $db->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
            log_admin_action('Delete Admin', "Terminated admin ID: {$id}");
            $success = "Identity deleted successfully.";
        }
    }
}

$admins = $db->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll();
$editUser = null;
if (isset($_GET['edit'])) {
    foreach ($admins as $a) {
        if ($a['id'] == $_GET['edit']) {
            $editUser = $a;
            break;
        }
    }
}
?>

<div class="max-w-6xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <!-- List View -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden shadow-2xl">
                <div class="p-10 border-b border-white/5 bg-white/[0.01]">
                    <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Authorized Identities</h3>
                </div>
                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[9px] font-black uppercase tracking-[0.3em] text-white/20 border-b border-white/5">
                                <th class="px-10 py-6">ID</th>
                                <th class="px-10 py-6">Identity</th>
                                <th class="px-10 py-6 text-right">Clearance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr class="hover:bg-white/[0.01] transition-colors group border-b border-white/[0.02]">
                                <td class="px-10 py-8">
                                    <span class="text-[10px] font-bold font-mono tracking-tighter text-white/40"><?php echo $admin['id']; ?></span>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold uppercase tracking-tight text-white"><?php echo e($admin['username']); ?></span>
                                        <span class="text-[9px] text-white/20 mt-1 uppercase tracking-widest"><?php echo e($admin['email']); ?></span>
                                        <div class="flex gap-4 mt-4 opacity-0 group-hover:opacity-100 transition-all">
                                            <a href="admins.php?edit=<?php echo $admin['id']; ?>" class="text-[9px] font-black uppercase tracking-widest text-amber-600 hover:text-white">Modify</a>
                                            <?php if($_SESSION['admin_role'] === 'super_admin' && $admin['id'] != 1): ?>
                                                <form action="admins.php" method="POST" onsubmit="return confirm('Terminate this session permanently?');" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                                    <button type="submit" class="text-[9px] font-black uppercase tracking-widest text-red-500/60 hover:text-red-500">Deauthorize</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <span class="px-4 py-1.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-white/5 border border-white/5 
                                        <?php echo ($admin['role'] === 'super_admin') ? 'text-amber-500 border-amber-500/20' : 'text-white/40'; ?>">
                                        <?php echo str_replace('_', ' ', $admin['role'] ?? 'admin'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form View -->
        <div class="space-y-8">
            <div class="bg-[#0a0a0a] border border-white/5 p-10 rounded-[3rem] shadow-2xl sticky top-32">
                <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10 italic">
                    <?php echo $editUser ? 'Modify Authorization' : 'New Identity Entry'; ?>
                </h3>

                <?php if ($error): ?>
                    <div class="mb-8 p-4 bg-red-900/20 text-red-500 rounded-2xl border border-red-500/20 text-[9px] font-black uppercase tracking-widest text-center"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="mb-8 p-4 bg-green-900/20 text-green-500 rounded-2xl border border-green-500/20 text-[9px] font-black uppercase tracking-widest text-center"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="admins.php" method="POST" class="space-y-8">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Terminal Identity</label>
                        <input type="text" name="username" required value="<?php echo e($editUser['username'] ?? ''); ?>" class="w-full bg-[#111111] border border-white/5 p-4 rounded-xl text-white text-xs font-bold focus:border-amber-600 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Secure Email</label>
                        <input type="email" name="email" required value="<?php echo e($editUser['email'] ?? ''); ?>" class="w-full bg-[#111111] border border-white/5 p-4 rounded-xl text-white text-xs font-bold focus:border-amber-600 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Clearance Level</label>
                        <select name="role" class="w-full bg-[#111111] border border-white/5 p-4 rounded-xl text-white text-[10px] font-black uppercase tracking-widest focus:border-amber-600 outline-none appearance-none">
                            <option value="admin">ADMIN</option>
                            <option value="super_admin" <?php echo (($editUser['role']??'') === 'super_admin') ? 'selected' : ''; ?>>SUPER ADMIN</option>
                            <option value="order_manager" <?php echo (($editUser['role']??'') === 'order_manager') ? 'selected' : ''; ?>>ORDER MANAGER</option>
                            <option value="catalog_manager" <?php echo (($editUser['role']??'') === 'catalog_manager') ? 'selected' : ''; ?>>CATALOG MANAGER</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Security Key <?php echo $editUser ? '(Leave empty to preserve)' : ''; ?></label>
                        <input type="password" name="password" <?php echo $editUser ? '' : 'required'; ?> class="w-full bg-[#111111] border border-white/5 p-4 rounded-xl text-white text-xs font-bold focus:border-amber-600 outline-none">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-5 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] hover:bg-amber-600 hover:text-white transition-all rounded-2xl shadow-2xl">
                            <?php echo $editUser ? 'Update Protocol' : 'Authorize Identity'; ?>
                        </button>
                        <?php if($editUser): ?>
                            <a href="admins.php" class="block text-center mt-6 text-[9px] font-black uppercase tracking-widest text-white/20 hover:text-white">Cancel Modification</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

</main></div></body></html>
