<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$count = 0;

if (isLoggedIn() && isset($_SESSION['cart'])) {
    $count = count($_SESSION['cart']);
}

echo json_encode([
    'success' => true,
    'count' => $count
]);
?>