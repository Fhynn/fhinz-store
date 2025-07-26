<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    echo json_encode(['success' => false, 'message' => 'Order number required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT status, updated_at FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'status' => $order['status'],
        'updated_at' => $order['updated_at']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>