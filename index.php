<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$stmt = $db->query("SELECT * FROM products LIMIT 1");
$featuredProduct = $stmt->fetch();
?>

<!-- Hero Section -->
<section class="relative h-[90vh] flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-[#050505] z-10"></div>
        <img src="assets/img/hero.jpg" class="w-full h-full object-cover scale-105" onerror="this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&q=80&w=2000'">
    </div>

    <div class="relative z-20 text-center max-w-4xl px-6">
        <h2 class="text-[10px] font-black uppercase tracking-[0.8em] text-amber-600 mb-8"><?php echo __('hero_subtitle'); ?></h2>
        <h1 class="text-6xl md:text-8xl font-serif font-bold text-white mb-12 uppercase tracking-tighter leading-[0.9] italic">
            <?php echo __('hero_title'); ?>
        </h1>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-8">
            <a href="shop.php" class="px-12 py-6 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] hover:bg-amber-600 hover:text-white transition-all rounded-full shadow-2xl">
                Explore Collection
            </a>
            <a href="track-order.php" class="text-white/40 hover:text-white text-[9px] font-black uppercase tracking-[0.5em] transition-colors border-b border-white/10 pb-2">
                Order Tracking
            </a>
        </div>
    </div>
</section>

<!-- Featured Section -->
<?php if ($featuredProduct): ?>
<section class="py-32 bg-[#050505]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
            <div class="relative group">
                <div class="absolute -inset-4 bg-amber-600/10 rounded-[3rem] blur-2xl group-hover:bg-amber-600/20 transition-all duration-700"></div>
                <img src="<?php echo e($featuredProduct['image_url']); ?>" class="relative w-full aspect-square object-contain transition-transform duration-700 group-hover:scale-105">
            </div>
            <div>
                <span class="text-amber-600 text-[10px] font-black uppercase tracking-[0.4em] mb-4 block">Product of Provenance</span>
                <h2 class="text-5xl font-serif font-bold text-white mb-8 italic uppercase tracking-tighter leading-none"><?php echo e($featuredProduct['name']); ?></h2>
                <p class="text-white/40 text-lg mb-12 leading-relaxed uppercase font-medium tracking-tight">
                    <?php echo e($featuredProduct['description']); ?>
                </p>
                <div class="flex items-center space-x-12 mb-12 border-y border-white/5 py-8">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-2">Intensity</p>
                        <div class="flex space-x-1">
                            <?php for($i=0; $i<$featuredProduct['roast_intensity']; $i++): ?>
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-600"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/20 mb-2">Complexity</p>
                        <p class="text-white text-[10px] font-bold uppercase tracking-widest">Exceptional</p>
                    </div>
                </div>
                <a href="product.php?id=<?php echo $featuredProduct['id']; ?>" class="inline-block px-12 py-5 border border-white/10 text-white font-black uppercase text-[10px] tracking-[0.4em] hover:bg-white hover:text-black transition-all rounded-full">
                    View Technical Details
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
