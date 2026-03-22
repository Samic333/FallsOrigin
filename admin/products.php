<?php
$pageTitle = 'Product Catalog';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_product_id'])) {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_POST['delete_product_id']]);
        $msg = "Product deleted.";
    }
}

$products = $db->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();
?>

<div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] overflow-hidden mb-12">
    <div class="p-10 border-b border-white/5 flex justify-between items-center">
        <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white">Product Catalog</h3>
        <a href="product-edit.php" class="px-6 py-3 bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full transition-colors">Add New Product</a>
    </div>

    <?php if (isset($msg)): ?>
        <div class="p-6 bg-green-900/20 text-green-500 text-center text-xs uppercase tracking-widest font-bold border-b border-green-500/20">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto no-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[9px] font-black uppercase tracking-[0.3em] text-white/20 border-b border-white/5">
                    <th class="px-10 py-6">ID</th>
                    <th class="px-10 py-6">Product</th>
                    <th class="px-10 py-6">Price</th>
                    <th class="px-10 py-6">Stock</th>
                    <th class="px-10 py-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr class="hover:bg-white/[0.01] transition-colors group border-b border-white/[0.02]">
                    <td class="px-10 py-8">
                        <span class="text-[11px] font-bold font-mono tracking-tighter text-white/50"><?php echo $product['id']; ?></span>
                    </td>
                    <td class="px-10 py-8">
                        <div class="flex items-center gap-4">
                            <?php if ($product['image_url']): ?>
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="" class="w-10 h-10 rounded object-cover border border-white/10">
                            <?php endif; ?>
                            <span class="text-xs font-bold uppercase tracking-tight text-white"><?php echo htmlspecialchars($product['name']); ?></span>
                        </div>
                    </td>
                    <td class="px-10 py-8 font-serif font-bold text-white">
                        $<?php echo number_format($product['price'], 2); ?>
                    </td>
                    <td class="px-10 py-8">
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/5 <?php echo $product['stock_quantity'] > 0 ? 'text-green-500' : 'text-red-500'; ?> border border-white/5">
                            <?php echo $product['stock_quantity']; ?> in stock
                        </span>
                    </td>
                    <td class="px-10 py-8 text-right flex justify-end gap-4">
                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="text-[10px] font-black uppercase tracking-widest text-amber-600 hover:text-amber-500 transition-colors">Edit</a>
                        <form action="products.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline">
                            <input type="hidden" name="delete_product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-red-500 hover:text-red-400 transition-colors">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" class="px-10 py-12 text-center text-white/40 text-xs font-bold uppercase tracking-widest">No products found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main></div></body></html>
