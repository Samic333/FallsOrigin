<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php'; // Admin auth check
?>
<!DOCTYPE html>
<html lang="en" class="no-scrollbar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Falls Origin Secure</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Playfair+Display:ital,wght@0,400;0,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #f5f5f4; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-[#050505]">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-80 border-r border-white/5 bg-[#0a0a0a] flex flex-col fixed h-full z-50">
            <div class="p-10 border-b border-white/5">
                <a href="index.php" class="flex items-center space-x-4 group">
                    <svg viewBox="0 0 100 100" fill="currentColor" class="w-8 h-8 text-amber-600 transition-colors duration-500">
                        <path d="M48 5h4v55c0 10-1.5 20-3 30h-1c-1.5-10-3-20-3-30V5z" />
                    </svg>
                    <span class="text-lg font-black tracking-widest uppercase text-white">Origin <span class="text-amber-600">OS</span></span>
                </a>
            </div>
            
            <nav class="flex-grow p-8 space-y-4">
                <a href="dashboard.php" class="flex items-center space-x-4 p-4 rounded-xl <?php echo $pageTitle == 'Operational Dashboard' ? 'bg-white/5 text-white border border-white/5' : 'text-white/20 hover:text-white transition-all'; ?>">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Dashboard</span>
                </a>
                <a href="orders.php" class="flex items-center space-x-4 p-4 rounded-xl <?php echo ($pageTitle == 'Order Registry' || $pageTitle == 'Order Control') ? 'bg-white/5 text-white border border-white/5' : 'text-white/20 hover:text-white transition-all'; ?>">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Orders</span>
                </a>
                <a href="messages.php" class="flex items-center space-x-4 p-4 rounded-xl <?php echo $pageTitle == 'Message Influx' ? 'bg-white/5 text-white border border-white/5' : 'text-white/20 hover:text-white transition-all'; ?>">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Messages</span>
                </a>
                <a href="reviews.php" class="flex items-center space-x-4 p-4 rounded-xl <?php echo $pageTitle == 'Public Sentiment' ? 'bg-white/5 text-white border border-white/5' : 'text-white/20 hover:text-white transition-all'; ?>">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Reviews</span>
                </a>
                <a href="settings.php" class="flex items-center space-x-4 p-4 rounded-xl <?php echo $pageTitle == 'Control Settings' ? 'bg-white/5 text-white border border-white/5' : 'text-white/20 hover:text-white transition-all'; ?>">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Settings</span>
                </a>
            </nav>

            <div class="p-8 border-t border-white/5">
                <a href="logout.php" class="flex items-center space-x-4 p-4 text-red-500/40 hover:text-red-500 transition-all font-black uppercase tracking-[0.3em] text-[9px]">
                    <span>Terminate Session</span>
                </a>
            </div>
        </aside>

        <!-- Content -->
        <main class="flex-grow ml-80 p-16">
            <header class="mb-20 flex justify-between items-end">
                <div>
                    <h2 class="text-[9px] font-black uppercase tracking-[0.4em] text-amber-600 mb-2">Secure Management</h2>
                    <h1 class="text-4xl font-serif font-bold text-white uppercase tracking-tighter"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-white/20 mb-2">Authenticated as</p>
                    <p class="text-white text-xs font-bold uppercase tracking-widest"><?php echo $_SESSION['admin_user']; ?></p>
                </div>
            </header>
