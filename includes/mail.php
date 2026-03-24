<?php
/**
 * Simple mailer helper for shared hosting.
 * On production, use PHPMailer if SMTP is required.
 */
function send_admin_notification($subject, $body, $from_email = null) {
    $headers = "From: " . ($from_email ?? ADMIN_EMAIL) . "\r\n";
    $headers .= "Reply-To: " . ($from_email ?? ADMIN_EMAIL) . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail(ADMIN_EMAIL, "[Falls Origin Notification] " . $subject, $body, $headers);
}

function send_review_request($order) {
    $subject = "Your acquisition from Falls Origin";
    $body = "Greetings " . $order['customer_name'] . ",\r\n\r\n";
    $body .= "We hope your recent acquisition has met our provenance standards.\r\n";
    $body .= "Please share your sentiment at: " . SITE_URL . "/reviews.php?order=" . $order['id'] . "\r\n\r\n";
    $body .= "Stay caffeinated,\r\nFalls Origin Team";
    
    return mail($order['email'], $subject, $body, "From: " . ADMIN_EMAIL);
}

function send_customer_email($to_email, $subject, $body) {
    if (!defined('ADMIN_EMAIL')) {
        define('ADMIN_EMAIL', 'admin@fallsorigincoffee.com');
    }
    
    // Explicit UTF-8 headers
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // The -f flag sets the 'Return-Path' and is mandatory on many Namecheap servers
    return mail($to_email, $subject, $body, $headers, "-f" . ADMIN_EMAIL);
}
?>
