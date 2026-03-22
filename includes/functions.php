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
        'collection' => 'Collection',
        'track_order' => 'Track Order',
        'contact_us' => 'Contact Us',
        'cart' => 'Cart',
        'cart_empty' => 'Your cart is currently empty.',
        'checkout' => 'Checkout',
        'shop_now' => 'Shop Now',
        'view_collection' => 'View Collection',
        'add_to_cart' => 'Add to Cart',
        'order_now' => 'Order Now',
        'product_details' => 'Product Details',
        'tracking_number' => 'Tracking Number',
        'order_number' => 'Order Number',
        'carrier' => 'Carrier',
        'order_status' => 'Order Status',
        'hero_title_1' => 'Premium Ethiopian',
        'hero_title_2' => 'Coffee',
        'hero_title_3' => 'Crafted for Canada',
        'hero_subtext' => 'Rich, bold, and smooth — delivered fresh from origin to your door.',
        'our_collection' => 'Our Collection',
        'experience_coffee' => 'Experience Coffee the Right Way',
        'ethiopian_origin' => 'Ethiopian Origin',
        'ethiopian_origin_desc' => 'Sourced direct from the finest Ethiopian coffee farms.',
        'freshly_roasted' => 'Freshly Roasted',
        'freshly_roasted_desc' => 'Roasted locally in Canada for peak freshness.',
        'delivered_canada' => 'Delivered in Canada',
        'delivered_canada_desc' => 'Fast reliable shipping across Canada.',
        'location' => 'Location',
        'communication' => 'Communication',
        'identity' => 'Identity',
        'electronic_mail' => 'Email Address',
        'topic' => 'Subject',
        'detailed_inquiry' => 'Message',
        'dispatch' => 'Send Message',
        'footer_desc' => 'Small batch heirloom coffee, roasted with technical precision in Niagara Falls for the discerning global palate.',
        'provenance' => 'Our Coffees',
        'micro_lot_selection' => 'The Collection',
        'transactional_transparency' => 'Order Verification',
        'order_verification' => 'Order Verification',
        'verification_token' => 'Verification Token',
        'retrieve_ledger' => 'Retrieve Details',
        'check_email_token' => 'Check your confirmation email for your secure token.',
        'order_id' => 'Order Number',
        'logistics_intel' => 'Tracking Details',
        'est_arrival' => 'Estimated Arrival',
        'settlement_data' => 'Total Amount',
        'verify_different' => 'Track Different Order',
        'secure_communication' => 'Secure Communication',
        'direct_frequency' => 'Contact Us'
    ],
    'fr' => [
        'home' => 'Accueil',
        'collection' => 'Collection',
        'track_order' => 'Suivre Commande',
        'contact_us' => 'Contactez-nous',
        'cart' => 'Panier',
        'cart_empty' => 'Votre panier est actuellement vide.',
        'checkout' => 'Paiement',
        'shop_now' => 'Acheter',
        'view_collection' => 'Voir la Collection',
        'add_to_cart' => 'Ajouter au Panier',
        'order_now' => 'Commander',
        'product_details' => 'Détails du Produit',
        'tracking_number' => 'Numéro de Suivi',
        'order_number' => 'Numéro de Commande',
        'carrier' => 'Transporteur',
        'order_status' => 'Statut de Commande',
        'hero_title_1' => 'Café Éthiopien',
        'hero_title_2' => 'Premium',
        'hero_title_3' => 'Conçu pour le Canada',
        'hero_subtext' => 'Riche, corsé et onctueux — livré frais de l\'origine à votre porte.',
        'our_collection' => 'Notre Collection',
        'experience_coffee' => 'Vivez le Café de la Bonne Façon',
        'ethiopian_origin' => 'Origine Éthiopienne',
        'ethiopian_origin_desc' => 'Provenant directement des meilleures fermes de café éthiopiennes.',
        'freshly_roasted' => 'Fraîchement Torréfié',
        'freshly_roasted_desc' => 'Torréfié localement au Canada pour une fraîcheur optimale.',
        'delivered_canada' => 'Livré au Canada',
        'delivered_canada_desc' => 'Expédition rapide et fiable partout au Canada.',
        'location' => 'Emplacement',
        'communication' => 'Communication',
        'identity' => 'Identité',
        'electronic_mail' => 'Adresse Courriel',
        'topic' => 'Sujet',
        'detailed_inquiry' => 'Message',
        'dispatch' => 'Envoyer le Message',
        'footer_desc' => 'Café patrimonial en petits lots, torréfié avec précision technique à Niagara Falls pour le palais mondial averti.',
        'provenance' => 'Nos Cafés',
        'micro_lot_selection' => 'La Collection',
        'transactional_transparency' => 'Vérification de Commande',
        'order_verification' => 'Vérification de Commande',
        'verification_token' => 'Jeton de Vérification',
        'retrieve_ledger' => 'Récupérer les Détails',
        'check_email_token' => 'Consultez votre courriel de confirmation pour votre jeton sécurisé.',
        'order_id' => 'Numéro de Commande',
        'logistics_intel' => 'Détails de Suivi',
        'est_arrival' => 'Arrivée Estimée',
        'settlement_data' => 'Montant Total',
        'verify_different' => 'Suivre une Autre Commande',
        'secure_communication' => 'Communication Sécurisée',
        'direct_frequency' => 'Contactez-nous'
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
