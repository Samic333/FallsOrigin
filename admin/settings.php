<?php
$pageTitle = 'Control Settings';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();

// Auto-patch: Ensure settings table and hero_image key exist
$db->exec("CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL
)");
$check = $db->prepare("SELECT setting_key FROM settings WHERE setting_key = 'hero_image'");
$check->execute();
if (!$check->fetch()) {
    $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")
       ->execute(['hero_image', 'assets/img/hero-coffee.png']);
}

// Ensure opacity settings exist
$keys = ['hero_opacity' => '0.95', 'hero_overlay_strength' => '0.6'];
foreach ($keys as $k => $v) {
    $c = $db->prepare("SELECT setting_key FROM settings WHERE setting_key = ?");
    $c->execute([$k]);
    if (!$c->fetch()) {
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute([$k, $v]);
    }
}

// Handle Settings Update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_hero') {
        // Handle Image
        if (!empty($_FILES['hero_image']['name'])) {
            $targetDir = "../assets/img/";
            $fileName = "hero-coffee-" . time() . "." . pathinfo($_FILES["hero_image"]["name"], PATHINFO_EXTENSION);
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES["hero_image"]["tmp_name"], $targetFile)) {
                $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'hero_image'")
                   ->execute(['assets/img/' . $fileName]);
                log_admin_action("Update Hero Image", "Changed hero asset to $fileName");
            }
        }
        
        // Handle Opacity/Overlay
        $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'hero_opacity'")->execute([$_POST['hero_opacity']]);
        $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'hero_overlay_strength'")->execute([$_POST['hero_overlay_strength']]);
        $message = "Atmospheric settings updated successfully.";
    }
}

$logs = $db->query("SELECT * FROM admin_audit_logs ORDER BY created_at DESC LIMIT 50")->fetchAll();
$admins = $db->query("SELECT * FROM admin_users")->fetchAll();

$settings_data = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$heroImg = $settings_data['hero_image'] ?? 'assets/img/hero-coffee.png';
$heroOpacity = $settings_data['hero_opacity'] ?? '0.95';
$heroOverlayStr = $settings_data['hero_overlay_strength'] ?? '0.6';
?>

<?php if ($message): ?>
<div class="mb-8 p-6 bg-amber-600/10 border border-amber-600/30 rounded-2xl">
    <p class="text-[10px] font-black uppercase tracking-widest text-amber-500"><?php echo e($message); ?></p>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
    <!-- Hero Management -->
    <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem]">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Hero Visualization</h3>
        <div class="space-y-8">
            <div class="aspect-video bg-white/5 rounded-2xl overflow-hidden border border-white/10">
                <img src="../<?php echo e($heroImg); ?>?v=<?php echo time(); ?>" alt="Current Hero" class="w-full h-full object-cover">
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="action" value="update_hero">
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-4">Select New Atmosphere Asset</label>
                    <input type="file" name="hero_image" class="block w-full text-[10px] text-white/40 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-amber-600/10 file:text-amber-500 hover:file:bg-amber-600/20 transition-all">
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-4">Image Opacity (0.1 - 1.0)</label>
                        <input type="number" step="0.05" min="0" max="1" name="hero_opacity" value="<?php echo e($heroOpacity); ?>" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white text-xs">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-4">Overlay Density (0.1 - 1.0)</label>
                        <input type="number" step="0.05" min="0" max="1" name="hero_overlay_strength" value="<?php echo e($heroOverlayStr); ?>" class="w-full bg-white/5 border border-white/10 rounded-xl p-4 text-white text-xs">
                    </div>
                </div>
                <button type="submit" class="btn btn-gold w-full py-6 uppercase text-[10px] tracking-[0.4em]">Apply Visual Change</button>
            </form>
        </div>
    </div>

    <!-- Admin Users -->
    <div class="bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem]">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Identity Management</h3>
        <div class="space-y-8">
            <?php foreach ($admins as $adm): ?>
            <div class="flex items-center justify-between bg-white/[0.01] border border-white/5 p-6 rounded-2xl">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-white"><?php echo e($adm['username']); ?></p>
                    <p class="text-[9px] text-white/20 uppercase tracking-widest mt-1"><?php echo e($adm['email']); ?></p>
                </div>
                <div class="text-[9px] font-black uppercase tracking-widest text-white/10">
                    Active
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Audit Log -->
    <div class="lg:col-span-2 bg-[#0a0a0a] border border-white/5 p-12 rounded-[3.5rem]">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white mb-10">Administrative Audit</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($logs as $log): ?>
            <div class="flex items-start justify-between border-b border-white/[0.02] pb-6">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-amber-600 mb-1"><?php echo e($log['action']); ?></p>
                    <p class="text-white/40 text-[10px] font-medium uppercase tracking-tight"><?php echo e($log['details']); ?></p>
                </div>
                <p class="text-[8px] font-mono text-white/10 uppercase"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</main></div></body></html>
