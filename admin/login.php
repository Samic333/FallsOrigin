<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    $username = $_POST['username'];

    $password = $_POST['password'];

    $db = DB::getInstance();
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'] ?? 'admin';
        $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        log_admin_action('Login', 'Admin user ' . $user['username'] . ' successfully authenticated.');
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}

$pageTitle = 'Secure Authentication';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="pt-8 md:pt-12 pb-24 bg-[#050505] min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem] shadow-2xl">
        <div class="text-center mb-12">
            <h1 class="text-2xl font-black uppercase tracking-[0.3em] text-white">Origin <span class="text-amber-600">OS</span></h1>
            <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 mt-4">Authorized Access Only</p>
        </div>
        <?php if (isset($error)): ?>
            <div class="mb-8 p-4 bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest rounded-2xl text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div>
                <label class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 block mb-3 ml-2">Terminal Identity</label>
                <input type="text" name="username" required placeholder="admin@fallscoffee.ca" class="w-full bg-[#111111] border-2 border-white/10 p-5 rounded-2xl text-white text-xs font-bold focus:border-amber-600 transition-all outline-none placeholder-white/40">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 block mb-3 ml-2">Security Key</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-[#111111] border-2 border-white/10 p-5 rounded-2xl text-white text-xs font-bold focus:border-amber-600 transition-all outline-none placeholder-white/40">
            </div>
            <button type="submit" class="w-full py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] hover:bg-amber-600 hover:text-white transition-all rounded-3xl shadow-xl">Initialize Session</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
