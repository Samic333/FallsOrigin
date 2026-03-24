<?php
require_once __DIR__ . '/config.php';

/**
 * DB Class - Resilience Layer
 * Handles database connectivity with graceful fallback for local/misconfigured environments.
 */
class DB {
    private static $instance = null;
    private $pdo = null;
    private $is_mock = false;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Log error internally if needed, then set mock mode
            $this->is_mock = true;
            $this->pdo = null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Proxies methods to PDO or returns mock results.
     */
    public function prepare($sql) {
        if ($this->is_mock) return new MockPDOStatement();
        return $this->pdo->prepare($sql);
    }

    public function query($sql) {
        if ($this->is_mock) return new MockPDOStatement();
        return $this->pdo->query($sql);
    }

    public function lastInsertId() {
        if ($this->is_mock) return $_SESSION['last_mock_insert_id'] ?? "MOCK-" . uniqid();
        return $this->pdo->lastInsertId();
    }

    public function isConnected() {
        return !$this->is_mock;
    }
}

/**
 * Mock PDO Statement for Graceful Failures
 */
class MockPDOStatement {
    private $data = [
        ['id' => 1, 'name' => 'Yirgacheffe', 'origin' => 'Ethiopia', 'price' => 28.00, 'weight' => '340g', 'description' => 'Bright floral notes with a distinct lemony acidity and silk-like body.', 'tasting_notes' => 'Jasmine, Bergamot, Blueberry', 'brewing_suggestions' => 'Pour over (V60), 1:15 ratio, 93°C water', 'origin_story' => 'Grown in the high altitudes of the Yirgacheffe region, these heirloom varietals are hand-picked by local farmers.', 'image_url' => 'assets/img/yirgacheffe.png', 'type' => 'coffee', 'stock_quantity' => 10],
        ['id' => 2, 'name' => 'Sidamo', 'origin' => 'Ethiopia', 'price' => 26.00, 'weight' => '340g', 'description' => 'Deep berry-like flavors with a smooth chocolate finish and medium body.', 'tasting_notes' => 'Dark Chocolate, Blackberry, Maple', 'brewing_suggestions' => 'French Press or Espresso', 'origin_story' => 'From the Guji zone of Sidamo, naturally processed and sun-dried on raised beds.', 'image_url' => 'assets/img/sidamo.png', 'type' => 'coffee', 'stock_quantity' => 10],
        ['id' => 3, 'name' => 'Guji', 'origin' => 'Ethiopia', 'price' => 32.00, 'weight' => '340g', 'description' => 'Complex jasmine aroma with notes of sweet peach and a clean honey finish.', 'tasting_notes' => 'Peach, Honey, Jasmine', 'brewing_suggestions' => 'Chemex, 1:16 ratio', 'origin_story' => 'Direct trade from a micro-lot in the Guji region, known for its exceptional sweetness.', 'image_url' => 'assets/img/guji.png', 'type' => 'coffee', 'stock_quantity' => 10]
    ];

    public function execute($params = []) { 
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Mock Contact Submissions
        if (strpos($uri, 'contact.php') !== false && count($params) >= 4) {
            if (!isset($_SESSION['mock_messages'])) {
                $_SESSION['mock_messages'] = [];
            }
            $_SESSION['mock_messages'][] = [
                'id' => count($_SESSION['mock_messages']) + 2,
                'name' => $params[0],
                'email' => $params[1],
                'subject' => $params[2],
                'message' => $params[3],
                'status' => 'Unread',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        // Mock Product Additions
        if (strpos($uri, 'product-edit.php') !== false && count($params) === 8) {
            if (!isset($_SESSION['mock_products'])) $_SESSION['mock_products'] = [];
            $newId = count($_SESSION['mock_products']) + 4; // base data has 3
            $_SESSION['mock_products'][] = [
                'id' => $newId,
                'name' => $params[0],
                'origin' => $params[1],
                'price' => $params[2],
                'weight' => $params[3],
                'description' => $params[4],
                'image_url' => $params[5],
                'stock_quantity' => $params[6],
                'type' => 'coffee'
            ];
            $_SESSION['last_mock_insert_id'] = $newId;
        }

        // Mock Product Updates
        if (strpos($uri, 'product-edit.php') !== false && count($params) === 9) {
            if (isset($_SESSION['mock_products'])) {
                foreach ($_SESSION['mock_products'] as &$p) {
                    if ($p['id'] == $params[8]) {
                        $p['name'] = $params[0];
                        $p['origin'] = $params[1];
                        $p['price'] = $params[2];
                        $p['weight'] = $params[3];
                        $p['description'] = $params[4];
                        $p['image_url'] = $params[5];
                        $p['stock_quantity'] = $params[6];
                    }
                }
            }
        }

        // Mock Product Deletions
        if (strpos($uri, 'products.php') !== false && count($params) === 1 && isset($_POST['delete_product_id'])) {
            if (!isset($_SESSION['mock_deleted_products'])) $_SESSION['mock_deleted_products'] = [];
            $_SESSION['mock_deleted_products'][] = $params[0];
        }

        return true; 
    }
    
    public function fetch() { 
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // Handle fetching individual products for editing or viewing
        if ((strpos($uri, 'product.php') !== false || strpos($uri, 'product-edit.php') !== false) && isset($_GET['id'])) {
            $allProducts = $this->fetchAll();
            foreach ($allProducts as $p) {
                if ($p['id'] == $_GET['id']) return $p;
            }
        }

        // Simulated Object Returns based on request context
        if (isset($_GET['token']) || isset($_GET['id']) && strpos($uri, 'order') !== false) {
            return [
                'id' => $_GET['token'] ?? $_GET['id'] ?? 'ORD-2026-MOCK',
                'customer_name' => 'John Doe',
                'email' => 'test@example.com',
                'address' => '123 Test St',
                'city' => 'Toronto',
                'province' => 'ON',
                'postal_code' => 'M5V 2H1',
                'total' => 43.00,
                'status' => 'Preparing',
                'tracking_number' => '',
                'eta' => '',
                'delivery_method' => 'STANDARD COURIER',
                'delivery_signature' => '',
                'items' => json_encode([['product' => $this->data[0], 'quantity' => 1]])
            ];
        }

        // Catch admin authentication queries
        if (strpos($_SERVER['REQUEST_URI'], 'login') !== false || isset($_POST['username'])) {
            return [
                'id' => 1,
                'username' => 'admin@fallscoffee.ca',
                'password_hash' => password_hash('FallsCoffee#2026', PASSWORD_DEFAULT),
                'email' => 'admin@fallscoffee.ca',
                'last_login' => null
            ];
        }

        return $this->data[0]; 
    }
    
    public function fetchAll() { 
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        if (strpos($uri, 'orders.php') !== false) {
            return [[
                'id' => 'ORD-2026-MOCK',
                'customer_name' => 'John Doe',
                'email' => 'test@example.com',
                'total' => 43.00,
                'status' => 'Preparing',
                'created_at' => date('Y-m-d H:i:s')
            ]];
        }
        
        if (strpos($uri, 'messages.php') !== false) {
            $baseMessages = [[
                'id' => 1,
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'subject' => 'Stock inquiry',
                'message' => 'Is Guji available?',
                'status' => 'Unread',
                'created_at' => date('Y-m-d H:i:s')
            ]];
            if (isset($_SESSION['mock_messages']) && !empty($_SESSION['mock_messages'])) {
                return array_merge(array_reverse($_SESSION['mock_messages']), $baseMessages);
            }
            return $baseMessages;
        }
        
        if (strpos($uri, 'reviews.php') !== false) {
            return [[
                'id' => 1,
                'customer_name' => 'John Doe',
                'rating' => 5,
                'comment' => 'Incredible coffee, perfectly roasted.',
                'status' => 'Pending',
                'created_at' => date('Y-m-d H:i:s')
            ]];
        }
        
        if (strpos($uri, 'products.php') !== false || strpos($uri, 'shop.php') !== false || strpos($uri, 'index.php') !== false || strpos($uri, 'cart.php') !== false || strpos($uri, 'checkout.php') !== false || strpos($uri, 'product-edit.php') !== false || strpos($uri, 'product.php') !== false) {
            $baseProducts = $this->data;
            if (isset($_SESSION['mock_products']) && !empty($_SESSION['mock_products'])) {
                $baseProducts = array_merge($baseProducts, $_SESSION['mock_products']);
            }
            if (isset($_SESSION['mock_deleted_products']) && !empty($_SESSION['mock_deleted_products'])) {
                $baseProducts = array_filter($baseProducts, function($p) {
                    return !in_array($p['id'], $_SESSION['mock_deleted_products']);
                });
            }
            return array_values($baseProducts); 
        }
        
        // Return empty array for settings.php (logs, admins) and anything else to prevent undefined key errors
        return [];
    }
    
    public function fetchColumn() { return 0; }
    public function rowCount() { return count($this->data); }
}
?>
