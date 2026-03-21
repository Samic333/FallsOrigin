<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" class="no-scrollbar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo get_csrf_token(); ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>Falls Origin Coffee</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #f5f5f4; -webkit-font-smoothing: antialiased; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        ::selection { background: #d97706; color: white; }
    </style>

</head>
<body class="min-h-screen flex flex-col bg-[#050505]">
    <header class="fixed top-0 w-full z-50 bg-[#0a0a0a]/90 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-24">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-4 group">
                        <svg viewBox="0 0 100 100" fill="currentColor" class="w-10 h-10 text-white group-hover:text-amber-600 transition-colors duration-500">
                            <path d="M48 5h4v55c0 10-1.5 20-3 30h-1c-1.5-10-3-20-3-30V5z" />
                            <path d="M36 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                            <path d="M60.5 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                            <path d="M25 28h3v35c0 6-1.5 12-3 18h-1c-1.5-6-3-12-3-18V28z" opacity="0.6" />
                            <path d="M72 28h3v35c0 6-1.5 12-3 18h-1c-1.5-6-3-12-3-18V28z" opacity="0.6" />
                        </svg>
                        <div class="hidden sm:flex flex-col items-center text-center text-white">
                            <span class="text-2xl font-black tracking-[0.25em] uppercase leading-none">Falls Origin</span>
                            <div class="flex items-center w-full mt-2">
                                <div class="h-[1px] flex-grow bg-white/20"></div>
                                <span class="text-[10px] font-black tracking-[0.5em] uppercase px-3 opacity-60">Coffee</span>
                                <div class="h-[1px] flex-grow bg-white/20"></div>
                            </div>
                        </div>
                    </a>
                </div>

                <?php include __DIR__ . '/nav.php'; ?>
            </div>
        </div>
    </header>
    <main class="flex-grow pt-24">
