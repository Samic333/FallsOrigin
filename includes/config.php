<?php
// Falls Origin Coffee - Application Configuration

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'falls_origin_coffee');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Stripe Configuration
define('STRIPE_PUBLIC_KEY', 'pk_test_your_key_here');
define('STRIPE_SECRET_KEY', 'sk_test_your_key_here');
define('STRIPE_WEBHOOK_SECRET', 'whsec_your_key_here');

// App Settings
define('APP_URL', 'http://localhost/falls-origin');
define('APP_NAME', 'Falls Origin Coffee');
define('ADMIN_EMAIL', 'admin@fallsorigincoffee.com');

// Session config
session_start();

// Security Utility
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
