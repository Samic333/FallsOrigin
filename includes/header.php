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
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; height: 80px; width: 100%;">
            <!-- Logo Left -->
            <div style="flex: 1; display: flex; justify-content: flex-start;">
                <a href="index.php" style="text-decoration: none; display: flex; flex-direction: column; align-items: flex-start;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg viewBox="0 0 100 100" fill="currentColor" style="width: 32px; height: 32px; color: white;">
                            <path d="M48 5h4v55c0 10-1.5 20-3 30h-1c-1.5-10-3-20-3-30V5z" />
                            <path d="M36 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                            <path d="M60.5 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                        </svg>
                        <span style="font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; font-size: 1.1rem; color: white;">Falls Origin</span>
                    </div>
                    <span style="font-weight: 400; letter-spacing: 0.4em; text-transform: uppercase; font-size: 0.6rem; color: rgba(255,255,255,0.6); margin-top: -2px; padding-left: 38px;">Coffee</span>
                </a>
            </div>

            <!-- Menu Center -->
            <div class="header-center" style="flex: 2; display: flex; justify-content: center;">
                <?php require_once __DIR__ . '/nav.php'; ?>
            </div>

            <!-- Icons/Lang Right -->
            <div class="header-right" style="flex: 1; display: flex; justify-content: flex-end;">
                <!-- nav.php handles icons and language toggle, ensuring they are right-aligned here -->
            </div>
        </div>
    </header>
    <main style="padding-top: 80px;">
