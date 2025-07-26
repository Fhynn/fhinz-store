<?php
// Admin helper functions

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'paid' => 'info',
        'processing' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'shipped' => 'info',
        'delivered' => 'success'
    ];
    
    $class = $badges[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . ucfirst($status) . '</span>';
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function uploadImage($file, $upload_dir = '../uploads/') {
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    return false;
}

function getAdminStats($pdo) {
    try {
        $stats = [];
        
        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
        $stats['total_products'] = $stmt->fetchColumn();
        
        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
        $stats['total_orders'] = $stmt->fetchColumn();
        
        // Total customers
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $stats['total_customers'] = $stmt->fetchColumn();
        
        // Total revenue
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('paid', 'completed')");
        $stats['total_revenue'] = $stmt->fetchColumn();
        
        return $stats;
    } catch (PDOException $e) {
        return [
            'total_products' => 0,
            'total_orders' => 0,
            'total_customers' => 0,
            'total_revenue' => 0
        ];
    }
}

function getRecentOrders($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("SELECT o.*, u.full_name as customer_name 
                              FROM orders o 
                              LEFT JOIN users u ON o.user_id = u.id 
                              ORDER BY o.created_at DESC 
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getTopProducts($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("SELECT p.name, 
                              COUNT(oi.product_id) as sales_count,
                              SUM(oi.quantity) as total_quantity,
                              SUM(oi.price * oi.quantity) as total_revenue
                              FROM products p
                              LEFT JOIN order_items oi ON p.id = oi.product_id
                              LEFT JOIN orders o ON oi.order_id = o.id
                              WHERE o.status IN ('paid', 'completed')
                              GROUP BY p.id
                              ORDER BY total_revenue DESC
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getMonthlyRevenue($pdo, $months = 12) {
    try {
        $stmt = $pdo->prepare("SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            SUM(total_amount) as revenue,
                            COUNT(*) as orders
                            FROM orders 
                            WHERE status IN ('paid', 'completed')
                            AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                            ORDER BY month");
        $stmt->execute([$months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getLowStockProducts($pdo, $threshold = 5) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.stock <= ? AND p.status = 'active'
                              ORDER BY p.stock ASC");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCustomerStats($pdo) {
    try {
        $stmt = $pdo->query("SELECT 
                            COUNT(DISTINCT user_id) as total_customers,
                            COUNT(*) as total_orders,
                            COALESCE(SUM(total_amount), 0) as total_revenue,
                            COALESCE(AVG(total_amount), 0) as avg_order_value
                            FROM orders 
                            WHERE status IN ('paid', 'completed')");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [
            'total_customers' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'avg_order_value' => 0
        ];
    }
}

function getTopCustomers($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("SELECT u.full_name, 
                              COUNT(o.id) as total_orders,
                              SUM(o.total_amount) as total_spent,
                              MAX(o.created_at) as last_order
                              FROM users u
                              LEFT JOIN orders o ON u.id = o.user_id
                              WHERE o.status IN ('paid', 'completed')
                              GROUP BY u.id
                              ORDER BY total_spent DESC
                              LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getOrderStatusBreakdown($pdo) {
    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getRevenueByCategory($pdo) {
    try {
        $stmt = $pdo->query("SELECT c.name, SUM(oi.price * oi.quantity) as revenue
                            FROM categories c
                            JOIN products p ON c.id = p.category_id
                            JOIN order_items oi ON p.id = oi.product_id
                            JOIN orders o ON oi.order_id = o.id
                            WHERE o.status IN ('paid', 'completed')
                            GROUP BY c.id
                            ORDER BY revenue DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getConversionRate($pdo) {
    try {
        $stmt = $pdo->query("SELECT 
                            (SELECT COUNT(*) FROM orders WHERE status IN ('paid', 'completed')) as completed_orders,
                            (SELECT COUNT(*) FROM orders) as total_orders");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $completed = $result['completed_orders'] ?? 0;
        $total = $result['total_orders'] ?? 0;
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

function getRepeatCustomerRate($pdo) {
    try {
        $stmt = $pdo->query("SELECT 
                            COUNT(DISTINCT user_id) as repeat_customers,
                            (SELECT COUNT(DISTINCT user_id) FROM orders WHERE status IN ('paid', 'completed')) as total_customers
                            FROM (
                                SELECT user_id
                                FROM orders 
                                WHERE status IN ('paid', 'completed')
                                GROUP BY user_id
                                HAVING COUNT(*) > 1
                            ) as repeat_customers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $repeat = $result['repeat_customers'] ?? 0;
        $total = $result['total_customers'] ?? 0;
        
        return $total > 0 ? round(($repeat / $total) * 100, 2) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}
?>
