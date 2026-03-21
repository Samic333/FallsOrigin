<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? 'unknown';
$total = floatval($_POST['total'] ?? 0);
$total_cents = round($total * 100);

// Simple curl to Stripe API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'amount' => $total_cents,
    'currency' => 'cad',
    'automatic_payment_methods[enabled]' => 'true',
    'metadata' => ['customer_email' => $email]
]));
curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');

$response = curl_exec($ch);
curl_close($ch);

$intent = json_decode($response, true);
if (isset($intent['error'])) {
    echo json_encode(['error' => $intent['error']['message']]);
} else {
    echo json_encode(['clientSecret' => $intent['client_secret']]);
}
?>
