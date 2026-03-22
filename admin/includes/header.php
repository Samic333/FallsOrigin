<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = $pageTitle ?? 'Admin Console';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Origin OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="h-screen w-screen bg-[#050505] text-[#f5f5f4] flex overflow-hidden">
    
    <!-- Sidebar -->
    <aside class="w-80 bg-[#0a0a0a] border-r border-white/5 flex flex-col h-screen">
        <div class="p-12 pb-8 border-b border-white/5">
            <h1 class="text-2xl font-black uppercase tracking-[0.3em] text-white">Origin <span class="text-amber-600">OS</span></h1>
            <p class="text-[9px] font-black uppercase tracking-[0.4em] text-white/20 mt-4">Command Center</p>
        </div>
        
        <nav class="flex-1 overflow-y-auto no-scrollbar py-8 px-6 space-y-2">
            <a href="dashboard.php" class="block px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all <?php echo $currentPage === 'dashboard.php' ? 'bg-amber-600 text-white' : 'text-white/40 hover:bg-white/5 hover:text-white'; ?>">
                Dashboard
            </a>
            <a href="products.php" class="block px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all <?php echo in_array($currentPage, ['products.php', 'product-edit.php']) ? 'bg-amber-600 text-white' : 'text-white/40 hover:bg-white/5 hover:text-white'; ?>">
                Catalog
            </a>
            <a href="orders.php" class="block px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all <?php echo in_array($currentPage, ['orders.php', 'order-view.php']) ? 'bg-amber-600 text-white' : 'text-white/40 hover:bg-white/5 hover:text-white'; ?>">
                Ledger (Orders)
            </a>
            <a href="messages.php" class="block px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all <?php echo $currentPage === 'messages.php' ? 'bg-amber-600 text-white' : 'text-white/40 hover:bg-white/5 hover:text-white'; ?>">
                Comms (Messages)
            </a>
            <a href="reviews.php" class="block px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all <?php echo $currentPage === 'reviews.php' ? 'bg-amber-600 text-white' : 'text-white/40 hover:bg-white/5 hover:text-white'; ?>">
                Testimonials
            </a>
            <a href="settings.php" class="block px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all <?php echo $currentPage === 'settings.php' ? 'bg-amber-600 text-white' : 'text-white/40 hover:bg-white/5 hover:text-white'; ?>">
                System Matrix
            </a>
        </nav>
        
        <div class="p-8 border-t border-white/5">
            <a href="logout.php" class="block w-full text-center px-6 py-4 rounded-2xl border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-[0.3em] hover:bg-red-500 hover:text-white transition-all">
                Terminate Session
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto no-scrollbar bg-[#050505]">
        <header class="sticky top-0 z-50 bg-[#050505]/90 backdrop-blur-xl border-b border-white/5 px-16 py-8 flex justify-between items-center">
            <h2 class="text-xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo htmlspecialchars($pageTitle); ?></h2>
            <div class="flex items-center gap-6">
                <a href="../index.php" target="_blank" class="text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-amber-500 transition-colors">View Live Site</a>
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            </div>
        </header>

        <main class="p-16 max-w-7xl mx-auto">
