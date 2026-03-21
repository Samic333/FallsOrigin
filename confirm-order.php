<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'No data received']);
    exit;
}

$db = DB::getInstance();
$orderId = 'FOC-' . strtoupper(substr(uniqid(), -8));
$trackingToken = bin2hex(random_bytes(16));
$itemsJson = json_encode($_SESSION['cart'] ?? []);

$stmt = $db->prepare("INSERT INTO orders (id, email, customer_name, address, city, province, postal_code, items, total, delivery_method, stripe_payment_intent_id, tracking_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $orderId,
    $data['email'],
    $data['customerName'],
    $data['address'],
    $data['city'],
    'Ontario',
    $data['postalCode'],
    $itemsJson,
    $data['total'],
    'Standard Logistics',
    $data['paymentIntentId'],
    $trackingToken
]);

// Clear cart
unset($_SESSION['cart']);

echo json_encode(['success' => true, 'trackingToken' => $trackingToken]);
?>
