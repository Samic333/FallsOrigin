<?php
// Translation strings (extracted from React LanguageContext)
$translations = [
    'en' => [
        'home' => 'Home',
        'shop' => 'Shop',
        'track' => 'Track Order',
        'contact' => 'Contact',
        'footerDesc' => 'Premium Ethiopian coffee, hand-roasted to perfection. Experience the soul of Yirgacheffe.',
        'discovery' => 'Discovery',
        'ourRoast' => 'Our Roast',
        'orderStatus' => 'Order Status',
        'legal' => 'Legal',
        'shipping' => 'Shipping Policy',
        'refunds' => 'Refunds & Returns',
        'terms' => 'Terms of Service',
        'footerCopyright' => '© ' . date('Y') . ' Falls Origin Coffee. All rights reserved.',
        'footerLocation' => 'Small Batch • Roasted in Canada',
        // Add more as needed during page porting
    ],
    'fr' => [
        'home' => 'Accueil',
        'shop' => 'Boutique',
        'track' => 'Suivi',
        'contact' => 'Contact',
        'footerDesc' => 'Café éthiopien de qualité supérieure, torréfié à la main à la perfection.',
        'discovery' => 'Découverte',
        'ourRoast' => 'Notre Torréfaction',
        'orderStatus' => 'État de la commande',
        'legal' => 'Juridique',
        'shipping' => 'Livraison',
        'refunds' => 'Remboursements',
        'terms' => 'Conditions',
        'footerCopyright' => '© ' . date('Y') . ' Falls Origin Coffee. Tous droits réservés.',
        'footerLocation' => 'Petit Lot • Torréfié au Canada',
    ]
];

// Determine language
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$t = $translations[$lang];

function __($key) {
    global $t;
    return isset($t[$key]) ? $t[$key] : $key;
}
?>
