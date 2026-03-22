<?php
$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    $name = $_POST['name'];

    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $db = DB::getInstance();
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    send_admin_notification("New Inquiry: " . $subject, "From: $name ($email)\n\n$message", $email);
    $msg = "Your inquiry has been logged in our registry. Our masters will respond shortly.";
}
?>

<div class="pt-32 pb-24 bg-[#050505] min-h-screen">
    <div class="max-w-4xl mx-auto px-6">
        <header class="mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic"><?php echo __('direct_frequency'); ?></h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo __('secure_communication'); ?></h1>
        </header>

        <div class="bg-[#0a0a0a] border border-white/5 p-16 rounded-[4rem] shadow-2xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 mb-16">
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600"><?php echo __('location'); ?></h3>
                    <p class="text-xl text-white font-serif italic">Niagara Falls, Ontario, CA</p>
                </div>
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600"><?php echo __('communication'); ?></h3>
                    <p class="text-xl text-white font-serif italic">+1 289-668-7975</p>
                </div>
            </div>

            <?php if (isset($msg)): ?>
                <div class="mb-12 p-6 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-3xl text-center italic">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form action="contact.php" method="POST" class="space-y-10">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('identity'); ?></label>
                        <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 p-6 rounded-2xl text-white text-sm focus:border-amber-600 transition-all outline-none">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('electronic_mail'); ?></label>
                        <input type="email" name="email" required class="w-full bg-white/5 border border-white/10 p-6 rounded-2xl text-white text-sm focus:border-amber-600 transition-all outline-none">
                    </div>
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('topic'); ?></label>
                    <input type="text" name="subject" required class="w-full bg-white/5 border border-white/10 p-6 rounded-2xl text-white text-sm focus:border-amber-600 transition-all outline-none">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2"><?php echo __('detailed_inquiry'); ?></label>
                    <textarea name="message" rows="6" required class="w-full bg-white/5 border border-white/10 p-6 rounded-2xl text-white text-sm focus:border-amber-600 transition-all outline-none no-scrollbar"></textarea>
                </div>
                <button type="submit" class="w-full py-8 bg-white text-black font-black uppercase text-[11px] tracking-[0.5em] rounded-full hover:bg-amber-600 hover:text-white transition-all shadow-2xl">
                    <?php echo __('dispatch'); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
