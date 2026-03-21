<?php
function log_admin_action($action, $details = '') {
    $db = DB::getInstance();
    $user = $_SESSION['admin_user'] ?? 'system';
    $stmt = $db->prepare("INSERT INTO admin_audit_logs (admin_user, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user, $action, $details]);
}
?>
