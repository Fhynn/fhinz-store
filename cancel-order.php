<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = intval($input['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

try {
    // Check if order exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    
    // Check if order can be cancelled
    if ($order['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
        exit();
    }
    
    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>