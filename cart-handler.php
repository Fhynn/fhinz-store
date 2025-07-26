<?php
require_once 'config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'add':
        addToCart($input['product_id'] ?? 0, $pdo);
        break;
        
    case 'update':
        updateCart($input['product_id'] ?? 0, $input['quantity'] ?? 1, $pdo);
        break;
        
    case 'remove':
        removeFromCart($input['product_id'] ?? 0);
        break;
        
    case 'clear':
        clearCart();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function addToCart($product_id, $pdo) {
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    try {
        // Check if product exists and is active
        $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already in cart
        if (isset($_SESSION['cart'][$product_id])) {
            echo json_encode(['success' => false, 'message' => 'Product already in cart']);
            return;
        }
        
        // Add to cart
        $_SESSION['cart'][$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => 1
        ];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart',
            'cart_count' => count($_SESSION['cart'])
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function updateCart($product_id, $quantity, $pdo) {
    if ($product_id <= 0 || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    if (!isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Product not in cart']);
        return;
    }
    
    if ($quantity == 0) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
    
    echo json_encode([
        'success' => true,
        'cart_count' => count($_SESSION['cart'])
    ]);
}

function removeFromCart($product_id) {
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    echo json_encode([
        'success' => true,
        'cart_count' => count($_SESSION['cart'])
    ]);
}

function clearCart() {
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'success' => true,
        'cart_count' => 0
    ]);
}
?>