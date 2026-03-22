<?php
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) $cartCount += $qty;
}
?>
<nav class="hidden md:flex space-x-10">
    <a href="index.php" class="text-white hover:text-white text-[11px] font-bold tracking-[0.4em] transition-all uppercase">Home</a>
    <a href="#collection" class="text-white hover:text-white text-[11px] font-bold tracking-[0.4em] transition-all uppercase">Collection</a>
    <a href="track-order.php" class="text-white hover:text-white text-[11px] font-bold tracking-[0.4em] transition-all uppercase">Track</a>
    <a href="contact.php" class="text-white hover:text-white text-[11px] font-bold tracking-[0.4em] transition-all uppercase">Connect</a>
</nav>

<div class="flex items-center space-x-2 sm:space-x-6">
    <div class="flex items-center bg-white/5 rounded-full p-1 border border-white/5 mr-2">
        <a href="?lang=en" class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full transition-all <?php echo $lang === 'en' ? 'bg-amber-600 text-white' : 'text-white/20 hover:text-white/40'; ?>">EN</a>
        <a href="?lang=fr" class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full transition-all <?php echo $lang === 'fr' ? 'bg-amber-600 text-white' : 'text-white/20 hover:text-white/40'; ?>">FR</a>
    </div>
    
    <a href="cart.php" class="relative group p-2 text-white/60 hover:text-white transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag w-5 h-5 group-hover:text-amber-600 transition-all"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <?php if ($cartCount > 0): ?>
            <span class="absolute -top-1 -right-1 bg-amber-600 text-white text-[8px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-lg border border-black group-hover:scale-110 transition-transform"><?php echo $cartCount; ?></span>
        <?php endif; ?>
    </a>
    <a href="admin/login.php" class="p-2 text-white/60 hover:text-white transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </a>
</div>
