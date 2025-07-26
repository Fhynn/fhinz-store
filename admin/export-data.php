<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$type = isset($_GET['type']) ? $_GET['type'] : '';

switch ($type) {
    case 'products':
        exportProducts($pdo);
        break;
    case 'orders':
        exportOrders($pdo);
        break;
    case 'customers':
        exportCustomers($pdo);
        break;
    default:
        header('Location: dashboard.php');
        exit();
}

function exportProducts($pdo) {
    try {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           ORDER BY p.created_at DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nama', 'Deskripsi', 'Harga', 'Stok', 'Kategori', 'Status', 'Dibuat']);
        
        foreach ($products as $product) {
            fputcsv($output, [
                $product['id'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock'],
                $product['category_name'],
                $product['status'],
                $product['created_at']
            ]);
        }
        fclose($output);
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function exportOrders($pdo) {
    try {
        $stmt = $pdo->query("SELECT o.*, u.full_name as customer_name 
                           FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order ID', 'Customer', 'Total', 'Status', 'Metode Pembayaran', 'Tanggal']);
        
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['order_number'],
                $order['customer_name'],
                $order['total_amount'],
                $order['status'],
                $order['payment_method'],
                $order['created_at']
            ]);
        }
        fclose($output);
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function exportCustomers($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nama Lengkap', 'Username', 'Email', 'Telepon', 'Bergabung', 'Status']);
        
        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['id'],
                $customer['full_name'],
                $customer['username'],
                $customer['email'],
                $customer['phone'],
                $customer['created_at'],
                $customer['status']
            ]);
        }
        fclose($output);
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
