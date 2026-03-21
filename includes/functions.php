<?php
require_once __DIR__ . '/config.php';

/**
 * Translation Helper
 */
function __($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}

$translations = [
    'en' => [
        'home' => 'Home',
        'shop' => 'Collection',
        'track' => 'Track',
        'contact' => 'Connect',
        'cart' => 'Selection',
        'hero_title' => 'The Art of the Heirloom',
        'hero_subtitle' => 'Micro-lot coffee for the discerning'
    ],
    'fr' => [
        'home' => 'Accueil',
        'shop' => 'Collection',
        'track' => 'Suivi',
        'contact' => 'Contact',
        'cart' => 'Sélection',
        'hero_title' => 'L\'Art de l\'Héritage',
        'hero_subtitle' => 'Café micro-lot pour le connaisseur'
    ]
];

$lang = $_SESSION['lang'] ?? 'en';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'] === 'fr' ? 'fr' : 'en';
    $_SESSION['lang'] = $lang;
}

/**
 * Sanitization Helper
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Audit Logging
 */
function log_admin_action($action, $details = '') {
    $db = DB::getInstance();
    $user = $_SESSION['admin_user'] ?? 'system';
    $stmt = $db->prepare("INSERT INTO admin_audit_logs (admin_user, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user, $action, $details]);
}
?>
