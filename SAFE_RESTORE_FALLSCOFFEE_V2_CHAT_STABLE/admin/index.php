<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
} else {
    header('Location: dashboard.php');
}
exit;
?>
