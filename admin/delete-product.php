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

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if (!$product_id) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit();
}

try {
    // Check if product exists
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['error' => 'Produk tidak ditemukan']);
        exit();
    }
    
    // Soft delete (update status to inactive)
    $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$product_id]);
    
    echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
