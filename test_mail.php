<?php
require_once __DIR__ . '/includes/config.php';

echo "🔍 Email Diagnostic Tool\n";
echo "========================\n";

$to = $_GET['to'] ?? 'admin@fallscoffee.ca';
$subject = "Falls Coffee Diagnostic - " . date('H:i:s');
$body = "This is a diagnostic message to test server mail capability.";

// Test 1: Absolute Minimal
$h1 = "From: " . ADMIN_EMAIL;
$r1 = mail($to, $subject . " (Minimal)", $body, $h1);
echo "Test 1 (Minimal): " . ($r1 ? "✅ SUCCESS" : "❌ FAILED") . "\n";

// Test 2: Standard Headers
$h2 = "From: " . ADMIN_EMAIL . "\r\n";
$h2 .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
$h2 .= "X-Mailer: PHP/" . phpversion();
$r2 = mail($to, $subject . " (Standard)", $body, $h2);
echo "Test 2 (Standard): " . ($r2 ? "✅ SUCCESS" : "❌ FAILED") . "\n";

// Test 3: Envelope Flag
$r3 = mail($to, $subject . " (Envelope)", $body, $h2, "-f" . ADMIN_EMAIL);
echo "Test 3 (Envelope -f): " . ($r3 ? "✅ SUCCESS" : "❌ FAILED") . "\n";

// Test 4: Different Domain (fallscoffee.ca)
$altEmail = 'admin@fallscoffee.ca';
$h4 = "From: " . $altEmail;
$r4 = mail($to, $subject . " (Alt Domain)", $body, $h4);
echo "Test 4 (Alt Domain): " . ($r4 ? "✅ SUCCESS" : "❌ FAILED") . "\n";

echo "\n💡 Recommendation: If all fail, Namecheap has blocked mail() for this account. PHPMailer (SMTP) is required.\n";
?>
