<?php
$pageTitle = 'Collection';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$category = $_GET['category'] ?? 'all';

$query = "SELECT * FROM products";
$params = [];
if ($category !== 'all') {
    $query .= " WHERE type = ?";
    $params[] = $category;
}
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="pt-32 pb-24 bg-[#050505]">
    <div class="max-w-7xl mx-auto px-6">
        <header class="mb-20 text-center">
            <h2 class="text-[10px] font-black uppercase tracking-[0.5em] text-amber-600 mb-4 italic">Provenance Registry</h2>
            <h1 class="text-6xl font-serif font-bold text-white uppercase tracking-tighter">Micro-Lot Selection</h1>
        </header>

        <!-- Filters -->
        <div class="flex items-center justify-center space-x-8 mb-20 overflow-x-auto no-scrollbar pb-4">
            <?php foreach (['all' => 'All Frequencies', 'Single Origin' => 'Single Origin', 'Reserve' => 'Reserve Portfolio'] as $val => $label): ?>
                <a href="?category=<?php echo $val; ?>" class="px-6 py-2 rounded-full border <?php echo $category === $val ? 'border-amber-600 text-amber-600' : 'border-white/5 text-white/20 hover:text-white/40'; ?> text-[9px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php foreach ($products as $product): ?>
            <a href="product.php?id=<?php echo $product['id']; ?>" class="group">
                <div class="relative bg-[#0a0a0a] border border-white/5 rounded-[3rem] p-10 overflow-hidden transition-all duration-700 hover:border-amber-600/30">
                    <div class="absolute top-8 right-8 text-[9px] font-black uppercase tracking-widest text-white/10 group-hover:text-amber-600 transition-colors">
                        <?php echo e($product['origin']); ?>
                    </div>
                    <div class="relative z-10 aspect-square mb-10 flex items-center justify-center">
                        <img src="<?php echo e($product['image_url']); ?>" class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-110">
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xl font-serif font-bold text-white group-hover:text-amber-600 transition-colors uppercase tracking-tight"><?php echo e($product['name']); ?></h3>
                            <span class="text-white/20 text-[10px] font-black tracking-widest mt-1 italic">$<?php echo $product['price']; ?></span>
                        </div>
                        <p class="text-white/30 text-[10px] uppercase font-medium tracking-tight leading-relaxed line-clamp-2">
                            <?php echo e($product['description']); ?>
                        </p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
