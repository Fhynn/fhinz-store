<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, name, description, price, image_url 
                          FROM products 
                          WHERE status = 'active' 
                          AND (name LIKE ? OR description LIKE ?) 
                          ORDER BY name 
                          LIMIT 10");
    $stmt->execute(["%$query%", "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format results
    $formatted_results = [];
    foreach ($results as $result) {
        $formatted_results[] = [
            'id' => $result['id'],
            'name' => $result['name'],
            'description' => substr($result['description'], 0, 100) . '...',
            'price' => floatval($result['price']),
            'image_url' => $result['image_url'] ?: 'assets/images/default-product.jpg'
        ];
    }
    
    echo json_encode($formatted_results);
    
} catch (PDOException $e) {
    echo json_encode([]);
}
?>