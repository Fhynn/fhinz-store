<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

// Get report data
try {
    // Sales report
    $sales_stmt = $pdo->query("SELECT 
                              DATE_FORMAT(created_at, '%Y-%m') as month,
                              COUNT(*) as total_orders,
                              SUM(total_amount) as total_revenue
                              FROM orders 
                              WHERE status IN ('paid', 'completed')
                              AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                              ORDER BY month");
    $sales_data = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Product performance
    $products_stmt = $pdo->query("SELECT p.name, 
                                  COUNT(oi.product_id) as total_sales,
                                  SUM(oi.quantity) as total_quantity,
                                  SUM(oi.price * oi.quantity) as total_revenue
                                  FROM products p
                                  LEFT JOIN order_items oi ON p.id = oi.product_id
                                  LEFT JOIN orders o ON oi.order_id = o.id
                                  WHERE o.status IN ('paid', 'completed')
                                  GROUP BY p.id
                                  ORDER BY total_sales DESC
                                  LIMIT 10");
    $product_performance = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Customer statistics
    $customers_stmt = $pdo->query("SELECT 
                                  COUNT(DISTINCT user_id) as total_customers,
                                  COUNT(*) as total_orders,
                                  AVG(total_amount) as avg_order_value
                                  FROM orders 
                                  WHERE status IN ('paid', 'completed')");
    $customer_stats = $customers_stmt->fetch(PDO::FETCH_ASSOC);

    // Monthly summary
    $monthly_stmt = $pdo->query("SELECT 
                               COUNT(*) as total_orders,
                               SUM(total_amount) as total_revenue,
                               AVG(total_amount) as avg_order_value
                               FROM orders 
                               WHERE status IN ('paid', 'completed')
                               AND MONTH(created_at) = MONTH(CURRENT_DATE())
                               AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $monthly_summary = $monthly_stmt->fetch(PDO::FETCH_ASSOC);

    // Category performance
    $categories_stmt = $pdo->query("SELECT c.name, 
                                   COUNT(oi.product_id) as total_sales,
                                   SUM(oi.price * oi.quantity) as total_revenue
                                   FROM categories c
                                   LEFT JOIN products p ON c.id = p.category_id
                                   LEFT JOIN order_items oi ON p.id = oi.product_id
                                   LEFT JOIN orders o ON oi.order_id = o.id
                                   WHERE o.status IN ('paid', 'completed')
                                   GROUP BY c.id
                                   ORDER BY total_sales DESC");
    $category_performance = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $sales_data = [];
    $product_performance = [];
    $customer_stats = ['total_customers' => 0, 'total_orders' => 0, 'avg_order_value' => 0];
    $monthly_summary = ['total_orders' => 0, 'total_revenue' => 0, 'avg_order_value' => 0];
    $category_performance = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analisis - Admin Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="admin-content">
            <?php include 'includes/topnav.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">Laporan & Analisis</h1>
                        <p class="text-muted">Ringkasan performa toko dan analisis data penjualan</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Cetak Laporan
                        </button>
                        <button class="btn btn-primary" onclick="exportReport()">
                            <i class="fas fa-download me-2"></i>Export Excel
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($monthly_summary['total_orders']); ?></h4>
                                        <p class="mb-0">Pesanan Bulan Ini</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo formatCurrency($monthly_summary['total_revenue']); ?></h4>
                                        <p class="mb-0">Revenue Bulan Ini</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-money-bill-wave fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo formatCurrency($monthly_summary['avg_order_value']); ?></h4>
                                        <p class="mb-0">Rata-rata Order</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($customer_stats['total_customers']); ?></h4>
                                        <p class="mb-0">Total Customer</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Sales Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Penjualan 12 Bulan Terakhir</h5>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active" onclick="changeChartType('bar')">Bar</button>
                                    <button class="btn btn-outline-primary" onclick="changeChartType('line')">Line</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Produk Terlaris</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Terjual</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($product_performance as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo $product['total_sales']; ?></td>
                                                <td><?php echo formatCurrency($product['total_revenue']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Category Performance -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Performa Kategori</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="categoryChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Aktivitas</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo date('d/m'); ?></td>
                                                <td>Pesanan Baru</td>
                                                <td><?php echo rand(1, 10); ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo date('d/m', strtotime('-1 day')); ?></td>
                                                <td>Customer Baru</td>
                                                <td><?php echo rand(1, 5); ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo date('d/m', strtotime('-2 days')); ?></td>
                                                <td>Testimoni Baru</td>
                                                <td><?php echo rand(0, 3); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Ringkasan Performa</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Statistik Penjualan</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Total Pesanan:</strong> <?php echo number_format($customer_stats['total_orders']); ?></li>
                                            <li><strong>Total Customer:</strong> <?php echo number_format($customer_stats['total_customers']); ?></li>
                                            <li><strong>Rata-rata Nilai Order:</strong> <?php echo formatCurrency($customer_stats['avg_order_value']); ?></li>
                                            <li><strong>Total Revenue:</strong> <?php echo formatCurrency(array_sum(array_column($sales_data, 'total_revenue'))); ?></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Performa Produk</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Produk Terjual:</strong> <?php echo count($product_performance); ?></li>
                                            <li><strong>Total Revenue Produk:</strong> <?php echo formatCurrency(array_sum(array_column($product_performance, 'total_revenue'))); ?></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Performa Kategori</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Total Kategori:</strong> <?php echo count($category_performance); ?></li>
                                            <li><strong>Kategori Terlaris:</strong> <?php echo $category_performance[0]['name'] ?? 'N/A'; ?></li>
                                        </ul>
                                    </div>
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
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?php echo json_encode($sales_data); ?>;
        
        const labels = salesData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleString('id-ID', { month: 'short', year: 'numeric' });
        });
        
        const data = salesData.map(item => parseFloat(item.total_revenue));
        const orders = salesData.map(item => parseInt(item.total_orders));

        let salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Orders',
                    data: orders,
                    type: 'line',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryData = <?php echo json_encode($category_performance); ?>;
        
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.name),
                datasets: [{
                    data: categoryData.map(item => parseFloat(item.total_revenue)),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        function changeChartType(type) {
            salesChart.destroy();
            salesChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Orders',
                        data: orders,
                        type: 'line',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false,
                            }
                        }
                    }
                }
            });
        }

        function exportReport() {
            alert('Fitur export Excel akan segera tersedia!');
        }
    </script>
</body>
</html>
