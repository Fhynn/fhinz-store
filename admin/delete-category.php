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

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

if (!$category_id) {
    echo json_encode(['error' => 'Invalid category ID']);
    exit();
}

try {
    // Check if category has products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
    $stmt->execute([$category_id]);
    $product_count = $stmt->fetchColumn();
    
    if ($product_count > 0) {
        echo json_encode(['error' => 'Kategori masih memiliki produk aktif']);
        exit();
    }
    
    // Delete category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
