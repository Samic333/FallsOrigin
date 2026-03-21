<?php
$pageTitle = 'Share Sentiment';
require_once __DIR__ . '/includes/header.php';

$orderId = $_GET['order'] ?? null;
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $name = $_POST['customer_name'];
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'];

    $db = DB::getInstance();
    $stmt = $db->prepare("INSERT INTO reviews (order_id, customer_name, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
    if ($stmt->execute([$orderId, $name, $rating, $comment])) {
        $msg = "Sentiment logged in our encrypted vault. Pending verification by the masters.";
    }
}
?>

<div class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        <header class="mb-16 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic">Post-Acquisition Intel</h2>
            <h1 class="text-5xl font-serif font-bold text-white uppercase tracking-tighter">Share Sentiment</h1>
        </header>

        <div class="bg-[#0a0a0a] border border-white/5 p-16 rounded-[4rem]">
            <?php if ($msg): ?>
                <div class="mb-12 p-8 bg-amber-600/10 border border-amber-600/20 text-amber-600 text-xs font-black uppercase tracking-widest rounded-[2rem] text-center italic">
                    <?php echo $msg; ?>
                </div>
                <div class="text-center"><a href="shop.php" class="text-white/20 hover:text-white text-[9px] font-black uppercase tracking-[0.4em] transition-colors">Return to Catalog</a></div>
            <?php else: ?>
                <form action="reviews.php" method="POST" class="space-y-12">
                    <input type="hidden" name="order_id" value="<?php echo e($orderId); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div>
                            <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Identity</label>
                            <input type="text" name="customer_name" required class="w-full bg-white/[0.02] border border-white/5 p-5 rounded-2xl text-white text-xs">
                        </div>
                        <div>
                            <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Rating (1-5)</label>
                            <div class="flex space-x-4 h-full items-center">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" required class="hidden peer">
                                        <span class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center text-[10px] font-black text-white/20 peer-checked:bg-amber-600 peer-checked:text-white group-hover:border-amber-600/50 transition-all font-mono">
                                            <?php echo $i; ?>
                                        </span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase tracking-widest text-white/20 block mb-3">Detailed Sentiment</label>
                        <textarea name="comment" rows="6" required class="w-full bg-white/[0.02] border border-white/5 p-6 rounded-2xl text-white text-xs uppercase no-scrollbar"></textarea>
                    </div>

                    <button type="submit" class="w-full py-8 bg-white text-black font-black uppercase text-[11px] tracking-[0.5em] rounded-full hover:bg-amber-600 hover:text-white transition-all shadow-2xl scale-100 active:scale-95">
                        Log Sentiment
                    </button>
                    <p class="text-[8px] text-white/10 text-center uppercase tracking-[0.3em] font-medium italic">Verified acquisitions are prioritized in our public ledger.</p>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
