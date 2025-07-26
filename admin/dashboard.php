<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

// Get dashboard statistics
try {
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $total_products = $stmt->fetchColumn();
    
    // Total users (customers)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetchColumn();
    
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();
    
    // Total revenue
    $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ('paid', 'completed')");
    $total_revenue = $stmt->fetchColumn();
    
    // Recent orders
    $stmt = $pdo->query("SELECT o.*, u.full_name as customer_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 10");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly revenue data for chart
    $stmt = $pdo->query("SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        SUM(total_amount) as revenue
                        FROM orders 
                        WHERE status IN ('paid', 'completed')
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month");
    $monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top selling products
    $stmt = $pdo->query("SELECT p.name, COUNT(oi.product_id) as sales_count,
                        SUM(oi.price * oi.quantity) as total_revenue
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        JOIN orders o ON oi.order_id = o.id
                        WHERE o.status IN ('paid', 'completed')
                        GROUP BY p.id
                        ORDER BY sales_count DESC
                        LIMIT 5");
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $total_products = 0;
    $total_customers = 0;
    $total_orders = 0;
    $total_revenue = 0;
    $recent_orders = [];
    $monthly_revenue = [];
    $top_products = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Top Navigation -->
            <?php include 'includes/topnav.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="container-fluid py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">Dashboard</h1>
                        <p class="text-muted">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
                    </div>
                    <div>
                        <span class="text-muted">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?php echo date('d F Y'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-primary">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 class="stat-number"><?php echo number_format($total_products); ?></h3>
                                    <p class="stat-label">Total Produk</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-success">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 class="stat-number"><?php echo number_format($total_customers); ?></h3>
                                    <p class="stat-label">Total Customer</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-warning">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 class="stat-number"><?php echo number_format($total_orders); ?></h3>
                                    <p class="stat-label">Total Pesanan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-info">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 class="stat-number"><?php echo formatCurrency($total_revenue); ?></h3>
                                    <p class="stat-label">Total Revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Revenue Chart -->
                    <div class="col-xl-8 col-lg-7 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Revenue Bulanan (6 Bulan Terakhir)
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Products -->
                    <div class="col-xl-4 col-lg-5 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>
                                    Produk Terlaris
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_products)): ?>
                                <p class="text-muted text-center">Belum ada data penjualan</p>
                                <?php else: ?>
                                <?php foreach($top_products as $index => $product): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="ranking-number me-3">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo $product['sales_count']; ?> terjual • 
                                            <?php echo formatCurrency($product['total_revenue']); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Pesanan Terbaru
                                </h6>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">
                                    Lihat Semua
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_orders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    Belum ada pesanan
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach($recent_orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($order['customer_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_classes = [
                                                        'pending' => 'warning',
                                                        'paid' => 'info',
                                                        'processing' => 'primary',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $status_class = $status_classes[$order['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_revenue); ?>;
        
        const labels = monthlyData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleString('id-ID', { month: 'short', year: 'numeric' });
        });
        
        const data = monthlyData.map(item => parseFloat(item.revenue));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>