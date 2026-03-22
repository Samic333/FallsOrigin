<?php
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) $cartCount += $qty;
}
?>
<nav class="hidden md:flex space-x-8 items-center">
    <a href="index.php" class="text-white hover:text-amber-600 text-[10px] font-black tracking-[0.5em] transition-all uppercase no-underline"><?php echo __('home'); ?></a>
    <a href="index.php#collection" class="text-white hover:text-amber-600 text-[10px] font-black tracking-[0.5em] transition-all uppercase no-underline"><?php echo __('collection'); ?></a>
    <a href="track-order.php" class="text-white hover:text-amber-600 text-[10px] font-black tracking-[0.5em] transition-all uppercase no-underline whitespace-nowrap"><?php echo __('track_order'); ?></a>
    <a href="contact.php" class="text-white hover:text-amber-600 text-[10px] font-black tracking-[0.5em] transition-all uppercase no-underline whitespace-nowrap"><?php echo __('contact_us'); ?></a>
</nav>
