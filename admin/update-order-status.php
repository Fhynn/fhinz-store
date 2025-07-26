<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';

$valid_statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];

if (!$order_id || !in_array($new_status, $valid_statuses)) {
    echo json_encode(['error' => 'Invalid data']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    echo json_encode(['success' => true, 'message' => 'Status pesanan berhasil diperbarui']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
