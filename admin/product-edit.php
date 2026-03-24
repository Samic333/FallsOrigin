<?php
$pageTitle = isset($_GET['id']) ? 'Edit Product' : 'Add Product';
require_once __DIR__ . '/includes/header.php';

$db = DB::getInstance();
$product = null;
$error = '';
$success = '';

if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch();
    if (!$product) {
        die("Product not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }
    
    $name = $_POST['name'] ?? '';
    $origin = $_POST['origin'] ?? '';
    $price = $_POST['price'] ?? 0;
    $weight = $_POST['weight'] ?? '';
    $description = $_POST['description'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $id = $_POST['id'] ?? null;
    
    $tasting_notes = $_POST['tasting_notes'] ?? '';
    $brewing_suggestions = $_POST['brewing_suggestions'] ?? '';
    $origin_story = $_POST['origin_story'] ?? '';
    $category_id = empty($_POST['category_id']) ? null : $_POST['category_id'];
    
    // Handle image upload
    $image_url = $product['image_url'] ?? 'assets/img/product_front.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/img/';
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_url = 'assets/img/' . $fileName;
        } else {
            $error = 'Failed to upload image.';
        }
    }

    if (empty($error)) {
        try {
            if ($id) {
                $stmt = $db->prepare("UPDATE products SET name=?, origin=?, price=?, weight=?, description=?, image_url=?, stock_quantity=?, is_active=?, category_id=?, tasting_notes=?, brewing_suggestions=?, origin_story=? WHERE id=?");
                $stmt->execute([$name, $origin, $price, $weight, $description, $image_url, $stock_quantity, $is_active, $category_id, $tasting_notes, $brewing_suggestions, $origin_story, $id]);
                $success = "Product updated successfully.";
            } else {
                // simple slug generator
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                $stmt = $db->prepare("INSERT INTO products (slug, name, origin, price, weight, description, image_url, stock_quantity, is_active, category_id, tasting_notes, brewing_suggestions, origin_story) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$slug, $name, $origin, $price, $weight, $description, $image_url, $stock_quantity, $is_active, $category_id, $tasting_notes, $brewing_suggestions, $origin_story]);
                $id = $db->lastInsertId();
                $success = "Product created successfully.";
            }
        } catch(PDOException $e) {
            // Check if missing column in mock/DB schema:
            $error = "Legacy schema detected. " . $e->getMessage();
            try {
                if ($id) {
                    $stmt = $db->prepare("UPDATE products SET name=?, origin=?, price=?, weight=?, description=?, image_url=?, stock_quantity=?, tasting_notes=?, brewing_suggestions=?, origin_story=? WHERE id=?");
                    $stmt->execute([$name, $origin, $price, $weight, $description, $image_url, $stock_quantity, $tasting_notes, $brewing_suggestions, $origin_story, $id]);
                    $success = "Product updated (legacy fallback).";
                    $error = "";
                }
            } catch(PDOException $e2) {
                // final fallback
            }
        }
        
        // Refresh product data
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    }
}

// Fetch categories
try {
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
} catch (PDOException $e) {
    $categories = []; // Schema may not be migrated yet
}
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-[#0a0a0a] border border-white/5 rounded-[3rem] p-10">
        <div class="flex justify-between items-center mb-10 border-b border-white/5 pb-6">
            <h3 class="text-xs font-black uppercase tracking-[0.5em] text-white"><?php echo $pageTitle; ?></h3>
            <a href="products.php" class="text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-white transition-colors">Back to Catalog</a>
        </div>

        <?php if ($error): ?>
            <div class="mb-8 p-4 bg-red-900/20 text-red-500 rounded border border-red-500/20 text-xs uppercase tracking-widest font-bold">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-8 p-4 bg-green-900/20 text-green-500 rounded border border-green-500/20 text-xs uppercase tracking-widest font-bold">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="product-edit.php<?php echo $product ? '?id='.$product['id'] : ''; ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <?php if ($product): ?>
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Product Name</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Origin</label>
                    <input type="text" name="origin" required value="<?php echo htmlspecialchars($product['origin'] ?? 'Ethiopia'); ?>" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600">
                </div>
            </div>

            <div class="grid grid-cols-4 gap-8">
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Price (CAD)</label>
                    <input type="number" step="0.01" name="price" required value="<?php echo htmlspecialchars($product['price'] ?? '0.00'); ?>" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none font-serif focus:border-amber-600">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Weight</label>
                    <input type="text" name="weight" required value="<?php echo htmlspecialchars($product['weight'] ?? '340g'); ?>" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Stock</label>
                    <input type="number" name="stock_quantity" required value="<?php echo htmlspecialchars($product['stock_quantity'] ?? '0'); ?>" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Category</label>
                    <select name="category_id" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600 appearance-none">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (($product['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Main Description</label>
                    <textarea name="description" rows="3" required class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600 no-scrollbar"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Tasting Notes (comma separated)</label>
                    <input type="text" name="tasting_notes" value="<?php echo htmlspecialchars($product['tasting_notes'] ?? ''); ?>" placeholder="Cherry, Dark Chocolate, Almond" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Brewing Suggestions</label>
                    <input type="text" name="brewing_suggestions" value="<?php echo htmlspecialchars($product['brewing_suggestions'] ?? ''); ?>" placeholder="V60, Aeropress" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600">
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-3">Origin Story</label>
                    <textarea name="origin_story" rows="3" class="w-full bg-white/[0.02] border border-white/5 p-4 rounded-xl text-white text-xs outline-none focus:border-amber-600 no-scrollbar"><?php echo htmlspecialchars($product['origin_story'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="p-6 bg-white/[0.01] border border-white/5 rounded-2xl">
                <label class="text-[9px] font-black uppercase tracking-widest text-white/40 block mb-4">Product Image</label>
                <div class="flex items-center gap-6">
                    <?php if (isset($product['image_url'])): ?>
                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" class="w-24 h-24 object-cover rounded-lg border border-white/10 bg-black">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" class="text-xs text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-white/5 file:text-white hover:file:bg-white/10 cursor-pointer">
                </div>
                <p class="text-[10px] uppercase font-bold tracking-widest text-white/30 mt-4">Leave empty to keep current image.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <?php 
                $isActive = true; 
                if (isset($product)) {
                    if (array_key_exists('is_active', $product)) {
                        $isActive = (bool)$product['is_active'];
                    } elseif (array_key_exists('active', $product)) {
                        $isActive = (bool)$product['active'];
                    }
                }
                ?>
                <input type="checkbox" name="is_active" id="is_active" <?php echo $isActive ? 'checked' : ''; ?> class="accent-amber-600 w-4 h-4">
                <label for="is_active" class="text-[10px] font-black uppercase tracking-widest text-white/80 cursor-pointer">Product is Active</label>
            </div>

            <button type="submit" class="w-full py-6 bg-amber-600 hover:bg-amber-500 text-white font-black uppercase text-[11px] tracking-[0.5em] rounded-2xl transition-all">
                Save Product
            </button>
        </form>
    </div>
</div>

</main></div></body></html>
