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
    <meta name="description" content="<?php echo __('footer_desc'); ?>">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Store",
      "name": "Falls Origin Coffee",
      "image": "https://fallsorigincoffee.com/assets/img/og-image.jpg",
      "description": "Premium Ethiopian coffee roasted in Niagara Falls.",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Niagara Falls",
        "addressRegion": "ON",
        "addressCountry": "CA"
      }
    }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/premium.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/premium.css'); ?>">
    <style>

        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #f5f5f4; -webkit-font-smoothing: antialiased; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        ::selection { background: #d97706; color: white; }
    </style>

</head>
<body class="min-h-screen flex flex-col bg-[#050505]">
    <header>
        <?php $base_path = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : ''; ?>
        <div class="container mx-auto px-6 md:px-12 flex justify-between items-center h-20 w-full gap-8">
            <!-- Logo Left -->
            <div class="flex-none flex justify-start z-50">
                <a href="<?php echo $base_path; ?>index.php" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; min-width: 180px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg viewBox="0 0 100 100" fill="currentColor" style="width: 32px; height: 32px; color: white;">
                            <path d="M48 5h4v55c0 10-1.5 20-3 30h-1c-1.5-10-3-20-3-30V5z" />
                            <path d="M36 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                            <path d="M60.5 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                        </svg>
                        <span style="font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; font-size: 1.1rem; color: white; white-space: nowrap;">Falls Origin</span>
                    </div>
                    <span style="font-weight: 400; letter-spacing: 0.4em; text-transform: uppercase; font-size: 0.6rem; color: rgba(255,255,255,0.6); margin-top: -2px; padding-left: 38px;">Coffee</span>
                </a>
            </div>

            <!-- Menu Center -->
            <div class="hidden md:flex flex-1 justify-center overflow-hidden">
                <?php require_once __DIR__ . '/nav.php'; ?>
            </div>

            <!-- Icons/Lang Right -->
            <div class="flex-none flex justify-end items-center gap-6 z-50">
                <?php
                // Language queries
                $en_query = $_GET; $en_query['lang'] = 'en'; $en_url = '?' . http_build_query($en_query);
                $fr_query = $_GET; $fr_query['lang'] = 'fr'; $fr_url = '?' . http_build_query($fr_query);
                ?>
                <div class="hidden md:flex items-center bg-white/5 rounded-full p-[2px] border border-white/5">
                    <a href="<?php echo $en_url; ?>" class="text-[9px] font-black px-2 py-1 rounded-full transition-all <?php echo $lang === 'en' ? 'bg-[#D4A373] text-white' : 'text-white/30 hover:text-white'; ?>">EN</a>
                    <a href="<?php echo $fr_url; ?>" class="text-[9px] font-black px-2 py-1 rounded-full transition-all <?php echo $lang === 'fr' ? 'bg-[#D4A373] text-white' : 'text-white/30 hover:text-white'; ?>">FR</a>
                </div>
                
                <!-- Tools -->
                <div class="flex items-center gap-4">
                    <a href="<?php echo $base_path; ?>cart.php" class="text-white/40 hover:text-[#D4A373] transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <?php if (isset($cartCount) && $cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-amber-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="<?php echo $base_path; ?>admin/login.php" class="hidden md:block text-white/40 hover:text-[#D4A373] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>

                    <!-- Hamburger Mobile -->
                    <button id="mobileMenuBtn" class="md:hidden text-white hover:text-amber-500 focus:outline-none ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Drawer Overlay (Extracted from Header context for full viewport height) -->
    <div id="mobileMenuBackdrop" class="fixed inset-0 h-[100dvh] bg-black/80 z-[100] hidden md:hidden transition-opacity duration-300 opacity-0 pointer-events-auto"></div>
    <div id="mobileDrawer" class="fixed top-0 right-0 h-[100dvh] w-[80%] max-w-[320px] bg-[#0B0F14] border-l border-white/5 shadow-2xl z-[110] transform translate-x-full transition-transform duration-300 ease-in-out md:hidden flex flex-col pt-24 pb-8 px-8 pointer-events-auto">
        <button id="closeMenuBtn" class="absolute top-6 right-6 text-white/50 hover:text-white p-2 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <nav class="flex flex-col space-y-8 items-start text-left mt-4 w-full">
            <a href="<?php echo $base_path; ?>index.php" class="text-white hover:text-amber-600 text-sm font-black tracking-[0.4em] transition-all uppercase no-underline"><?php echo __('home'); ?></a>
            <a href="<?php echo $base_path; ?>shop.php" class="text-white hover:text-amber-600 text-sm font-black tracking-[0.4em] transition-all uppercase no-underline"><?php echo __('collection'); ?></a>
            <a href="<?php echo $base_path; ?>track-order.php" class="text-white hover:text-amber-600 text-sm font-black tracking-[0.4em] transition-all uppercase no-underline"><?php echo __('track_order'); ?></a>
            <a href="<?php echo $base_path; ?>contact.php" class="text-white hover:text-amber-600 text-sm font-black tracking-[0.4em] transition-all uppercase no-underline whitespace-nowrap"><?php echo __('contact_us'); ?></a>
            
            <div class="w-full h-px bg-white/10 my-4"></div>
            
            <!-- Mobile Language Select -->
            <div class="flex items-center gap-2 bg-white/5 rounded-full p-1 border border-white/5 self-start mt-4">
                <a href="<?php echo $en_url; ?>" class="text-[11px] font-black px-4 py-2 rounded-full transition-all <?php echo $lang === 'en' ? 'bg-[#D4A373] text-white' : 'text-white/30'; ?>">EN</a>
                <a href="<?php echo $fr_url; ?>" class="text-[11px] font-black px-4 py-2 rounded-full transition-all <?php echo $lang === 'fr' ? 'bg-[#D4A373] text-white' : 'text-white/30'; ?>">FR</a>
            </div>
        </nav>
    </div>

    <main style="padding-top: 80px;">
