<?php
/**
 * Falls Origin Coffee - Sample Config
 * Rename this to config.php and update with your cPanel environment variables.
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'falls_origin_db');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');

// Stripe API Keys (Testing)
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');

// Administrative Settings
define('ADMIN_EMAIL', 'admin@fallsorigin.coffee');
define('SITE_URL', 'https://fallsorigin.coffee');

// Application Secret (for CSRF/Sessions)
define('APP_SECRET', 'replace_with_long_random_string');
?>
