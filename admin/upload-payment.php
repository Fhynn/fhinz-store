<?php
require_once '../config/database.php';

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

header('Content-Type: application/json');

$order_id = intval($_POST['order_id'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validate order ownership
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    
    if ($order['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Order sudah diproses']);
        exit();
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Handle file upload
if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please select a file']);
    exit();
}

$file = $_FILES['payment_proof'];
$file_name = $file['name'];
$file_size = $file['size'];
$file_tmp = $file['tmp_name'];
$file_type = $file['type'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed. Use JPG, PNG, or PDF']);
    exit();
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file_size > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB']);
    exit();
}

// Create upload directory if not exists
$upload_dir = '../uploads/payment-proofs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique file name
$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
$new_file_name = 'payment_' . $order['order_number'] . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $new_file_name;

// Move uploaded file
if (!move_uploaded_file($file_tmp, $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    exit();
}

// Update order with payment proof
try {
    $update_notes = $order['notes'];
    if (!empty($notes)) {
        $update_notes = $order['notes'] . "\n\nCatatan pembayaran: " . $notes;
    }
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_proof = ?, notes = ?, status = 'paid', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$upload_path, $update_notes, $order_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payment proof uploaded successfully',
        'file_path' => $upload_path
    ]);
    
} catch (PDOException $e) {
    // Delete uploaded file if database update fails
    if (file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
</create_file>
