<?php
require_once __DIR__ . '/includes/db.php';
header("Content-Type: application/xml; charset=utf-8");

$site_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
$db = DB::getInstance();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?php echo $site_url; ?>/index.php</loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo $site_url; ?>/shop.php</loc>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?php echo $site_url; ?>/contact.php</loc>
        <priority>0.5</priority>
    </url>
    <url>
        <loc><?php echo $site_url; ?>/track-order.php</loc>
        <priority>0.3</priority>
    </url>

    <!-- Product URLs -->
    <?php
    $products = $db->query("SELECT id, slug, updated_at FROM products WHERE is_active = 1")->fetchAll();
    foreach ($products as $p):
        $url = $site_url . "/product.php?id=" . $p['id'];
        // Note: Replace with slug version once .htaccess is active
        //$url = $site_url . "/product/" . $p['slug'];
    ?>
    <url>
        <loc><?php echo $url; ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($p['updated_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
</urlset>
