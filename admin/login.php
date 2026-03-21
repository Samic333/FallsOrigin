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
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['username'];
        $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        log_admin_action('Login', 'Admin user ' . $user['username'] . ' successfully authenticated.');
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Origin OS | Secure Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #050505; color: #f5f5f4; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
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
                <input type="text" name="username" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs font-bold focus:outline-none focus:border-amber-600 transition-all">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 block mb-3 ml-2">Security Key</label>
                <input type="password" name="password" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs font-bold focus:outline-none focus:border-amber-600 transition-all">
            </div>
            <button type="submit" class="w-full py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] hover:bg-amber-600 hover:text-white transition-all rounded-3xl shadow-xl">Initialize Session</button>
        </form>
    </div>
</body>
</html>
