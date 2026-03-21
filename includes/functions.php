<?php
require_once __DIR__ . '/config.php';

// Session Security
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Lax'
    ]);
}

/**
 * CSRF Protection
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


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
