<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mail.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

if (!validate_csrf_token($input['csrf_token'] ?? '')) {
    echo json_encode(['error' => 'CSRF Token mismatch']);
    exit;
}

$db = DB::getInstance();

// Generate unique order ID
$orderId = 'ORD-' . date('Y') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));

// Extract customer data
$email = $input['email'] ?? '';
$customerName = $input['customerName'] ?? '';
$addressFull = ($input['address'] ?? '') . ', ' . ($input['city'] ?? '') . ' ' . ($input['postalCode'] ?? '');
$total = $input['total'] ?? 0;
$pi = $input['paymentIntentId'] ?? 'pi_mock_' . time();

try {
    $db->beginTransaction();

    // Insert Order
    $stmt = $db->prepare("INSERT INTO orders (id, customer_name, email, address, city, province, postal_code, total, status, payment_intent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $orderId, 
        $customerName, 
        $email, 
        $input['address'] ?? '', 
        $input['city'] ?? '', 
        'ON', // hardcoded for this demo, usually collected
        $input['postalCode'] ?? '', 
        $total, 
        'Paid', 
        $pi
    ]);

    // Insert Items
    $cartItems = [];
    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $prodStmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $prodStmt->execute($ids);
        $products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['id']];
            
            
            // Build items JSON for the admin panel legacy column if still used:
            $cartItems[] = [
                'product' => $p,
                'quantity' => $qty
            ];

            // Deplete stock
            $updateStock = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $updateStock->execute([$qty, $p['id']]);
        }
        
        // Save items as JSON logic to keep compatibility with admin/order-view.php which uses $order['items']
        $itemsJson = json_encode($cartItems);
        $updateItems = $db->prepare("UPDATE orders SET items = ? WHERE id = ?");
        $updateItems->execute([$itemsJson, $orderId]);

        // Fix status to standardized lowercase
        $db->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")->execute([$orderId]);
    }

    $db->commit();
    $_SESSION['cart'] = []; // Clear Cart

    // Dispatch receipt email
    require_once __DIR__ . '/includes/classes/Mailer.php';
    $mailer = Mailer::getInstance();
    $site_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
    $body = "Greetings {$customerName},\n\nYour order {$orderId} has been successfully received.\nTotal: $" . number_format($total, 2) . " CAD\n\nTrack your order here:\n" . $site_url . "/track-order.php?token=" . $orderId . "\n\nThank you for trusting Falls Origin.";
    $mailer->send($email, "Order Received - {$orderId}", $body, $orderId);

    echo json_encode(['trackingToken' => $orderId]);

} catch (Exception $e) {
    $db->rollBack();
    error_log($e->getMessage());
    echo json_encode(['error' => 'Database failure during order creation.']);
}
?>
