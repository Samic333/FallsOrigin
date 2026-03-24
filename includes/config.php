<?php
// Falls Origin Coffee - Application Configuration

// Error reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fallphyl_fallscoffee');
define('DB_USER', 'fallphyl_fallscoffee');
define('DB_PASS', 'Ask#113773ask');
define('DB_CHARSET', 'utf8mb4');


// Stripe Configuration
define('STRIPE_PUBLIC_KEY', 'pk_test_your_key_here');
define('STRIPE_SECRET_KEY', 'sk_test_your_key_here');
define('STRIPE_WEBHOOK_SECRET', 'whsec_your_key_here');

// App Settings
define('APP_URL', 'http://localhost/falls-origin');
define('APP_NAME', 'Falls Origin Coffee');
define('ADMIN_EMAIL', 'admin@fallsorigincoffee.com');

// All security and session utilities moved to functions.php
?>

