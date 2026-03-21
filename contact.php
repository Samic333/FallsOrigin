<?php
$pageTitle = 'Connect';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

<div class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        <header class="mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic">Direct Frequency</h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter">Secure Communication</h1>
        </header>

        <div class="bg-[#0a0a0a] border border-white/5 p-16 rounded-[4rem]">
            <?php if (isset($msg)): ?>
                <div class="mb-12 p-6 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-3xl text-center italic">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form action="contact.php" method="POST" class="space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Identity</label>
                        <input type="text" name="name" required class="w-full bg-white/[0.02] border border-white/5 p-6 rounded-2xl text-white text-xs focus:border-amber-600 transition-all uppercase">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Electronic Mail</label>
                        <input type="email" name="email" required class="w-full bg-white/[0.02] border border-white/5 p-6 rounded-2xl text-white text-xs focus:border-amber-600 transition-all uppercase">
                    </div>
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Topic of Provenance</label>
                    <input type="text" name="subject" required class="w-full bg-white/[0.02] border border-white/5 p-6 rounded-2xl text-white text-xs focus:border-amber-600 transition-all uppercase">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3 ml-2">Detailed Inquiry</label>
                    <textarea name="message" rows="6" required class="w-full bg-white/[0.02] border border-white/5 p-6 rounded-2xl text-white text-xs focus:border-amber-600 transition-all uppercase no-scrollbar"></textarea>
                </div>
                <button type="submit" class="w-full py-8 bg-white text-black font-black uppercase text-[10px] tracking-[0.5em] rounded-full hover:bg-amber-600 hover:text-white transition-all shadow-2xl">
                    Dispatch Transmission
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
