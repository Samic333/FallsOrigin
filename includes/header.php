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
    <link rel="stylesheet" href="assets/css/premium.css">
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
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; height: 80px; width: 100%; gap: 2rem;">
            <!-- Logo Left -->
            <div style="flex: 0 0 auto; display: flex; justify-content: flex-start;">
                <a href="index.php" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start; min-width: 180px;">
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
            <div class="header-center" style="flex: 1; display: flex; justify-content: center; overflow: hidden;">
                <?php require_once __DIR__ . '/nav.php'; ?>
            </div>

            <!-- Icons/Lang Right -->
            <div class="header-right" style="flex: 0 0 auto; display: flex; justify-content: flex-end; align-items: center; gap: 1.5rem;">
                <!-- Language Toggle -->
                <div style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border-radius: 99px; padding: 2px; border: 1px solid rgba(255,255,255,0.05);">
                    <a href="?lang=en" style="text-decoration: none; font-size: 9px; font-weight: 900; padding: 4px 8px; border-radius: 99px; transition: all 0.3s; <?php echo $lang === 'en' ? 'background: #D4A373; color: white;' : 'color: rgba(255,255,255,0.3);'; ?>">EN</a>
                    <a href="?lang=fr" style="text-decoration: none; font-size: 9px; font-weight: 900; padding: 4px 8px; border-radius: 99px; transition: all 0.3s; <?php echo $lang === 'fr' ? 'background: #D4A373; color: white;' : 'color: rgba(255,255,255,0.3);'; ?>">FR</a>
                </div>
                
                <!-- Tools -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="cart.php" style="color: rgba(255,255,255,0.4); transition: color 0.3s;" onmouseover="this.style.color='#D4A373'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    </a>
                    <a href="admin/login.php" style="color: rgba(255,255,255,0.4); transition: color 0.3s;" onmouseover="this.style.color='#D4A373'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <main style="padding-top: 80px;">
