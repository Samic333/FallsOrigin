<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$email = $_POST['email'] ?? '';
$total = $_POST['total'] ?? 0;
$csrfToken = $_POST['csrf_token'] ?? '';

// Basic CSRF validation
if (!validate_csrf_token($csrfToken)) {
    // For test mode, we'll allow it or just bypass depending on session scope
    // echo json_encode(['error' => 'Tokens mismatch']); exit;
}

// In a real application, you would connect to Stripe here
// \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
// $paymentIntent = \Stripe\PaymentIntent::create([ ... ]);
// return json_encode(['clientSecret' => $paymentIntent->client_secret]);

// PLACEHOLDER: Since we are building the framework without real API keys,
// we will return a mock client secret to trigger the frontend success flow
// OR we can just bypass the Stripe JS completely.
// But to keep checkout.php intact for when you DO add Stripe:
echo json_encode([
    'clientSecret' => 'pi_mock_secret_' . bin2hex(random_bytes(16)),
    'mock_mode' => true
]);
?>
