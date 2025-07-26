<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get customer details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        header('Location: customers.php');
        exit();
    }
    
    // Get customer orders
    $stmt = $pdo->prepare("SELECT o.*, 
                          COUNT(oi.id) as total_items,
                          SUM(oi.quantity * oi.price) as total_amount
                          FROM orders o
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.user_id = ?
                          GROUP BY o.id
                          ORDER BY o.created_at DESC");
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customer stats
    $stmt = $pdo->prepare("SELECT 
                          COUNT(*) as total_orders,
                          SUM(total_amount) as total_spent,
                          MAX(created_at) as last_order_date
                          FROM orders 
                          WHERE user_id = ? AND status IN ('paid', 'completed')");
    $stmt->execute([$customer_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $customer = [];
    $orders = [];
    $stats = ['total_orders' => 0, 'total_spent' => 0, 'last_order_date' => null];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Customer - <?php echo htmlspecialchars($customer['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="admin-content">
            <?php include 'includes/topnav.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">Detail Customer</h1>
                        <p class="text-muted">Informasi lengkap customer</p>
                    </div>
                    <div>
                        <a href="customers.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Customer</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="../assets/images/default-avatar.jpg" 
                                     alt="Avatar" class="rounded-circle mb-3" width="120" height="120">
                                <h5><?php echo htmlspecialchars($customer['full_name']); ?></h5>
                                <p class="text-muted">@<?php echo htmlspecialchars($customer['username']); ?></p>
                                
                                <div class="text-start">
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                                    <p><strong>Telepon:</strong> <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
                                    <p><strong>Bergabung:</strong> <?php echo date('d F Y', strtotime($customer['created_at'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $customer['status'] == 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($customer['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Pembelian</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Pesanan:</span>
                                    <strong><?php echo number_format($stats['total_orders']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Pembelian:</span>
                                    <strong><?php echo formatCurrency($stats['total_spent']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Pesanan Terakhir:</span>
                                    <strong><?php echo $stats['last_order_date'] ? date('d F Y', strtotime($stats['last_order_date'])) : 'Belum pernah'; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Riwayat Pesanan</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Customer ini belum memiliki pesanan</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Tanggal</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Items</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                                <td><?php echo getStatusBadge($order['status']); ?></td>
                                                <td><?php echo $order['total_items']; ?> item</td>
                                                <td>
                                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
