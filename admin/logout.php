<?php
require_once __DIR__ . '/../includes/functions.php';
// Log the logout action if possible
if (isset($_SESSION['admin_user'])) {
    log_admin_action('Logout', 'Admin session terminated by user.');
}
session_destroy();
header('Location: login.php');
exit;
?>
