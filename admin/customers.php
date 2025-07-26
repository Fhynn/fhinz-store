<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

// Get customers with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$items_per_page = 15;
$offset = ($page - 1) * $items_per_page;

$where_conditions = ["u.role = 'customer'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM users u $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_customers = $count_stmt->fetchColumn();
    $total_pages = ceil($total_customers / $items_per_page);
    
    // Get customers with stats
    $sql = "SELECT u.*,
                   COUNT(o.id) as total_orders,
                   COALESCE(SUM(CASE WHEN o.status IN ('paid', 'completed') THEN o.total_amount ELSE 0 END), 0) as total_spent,
                   COUNT(CASE WHEN o.status = 'completed' THEN 1 END) as completed_orders,
                   MAX(o.created_at) as last_order_date
            FROM users u 
            LEFT JOIN orders o ON u.id = o.user_id 
            $where_clause 
            GROUP BY u.id
            ORDER BY u.created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customer statistics
    $stats_stmt = $pdo->query("SELECT 
                              COUNT(*) as total_customers,
                              COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers,
                              COUNT(CASE WHEN id IN (SELECT DISTINCT user_id FROM orders WHERE status = 'completed') THEN 1 END) as active_customers
                              FROM users WHERE role = 'customer'");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $customers = [];
    $total_pages = 1;
    $stats = ['total_customers' => 0, 'new_customers' => 0, 'active_customers' => 0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Customer - Admin Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Top Navigation -->
            <?php include 'includes/topnav.php'; ?>
            
            <!-- Page Content -->
            <div class="container-fluid py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">Kelola Customer</h1>
                        <p class="text-muted">Manage data customer</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="exportCustomers()">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                    </div>
                </div>

                <!-- Customer Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <h3><?php echo number_format($stats['total_customers']); ?></h3>
                                <p class="mb-0">Total Customer</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-3x mb-3"></i>
                                <h3><?php echo number_format($stats['new_customers']); ?></h3>
                                <p class="mb-0">Customer Baru (30 hari)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-user-check fa-3x mb-3"></i>
                                <h3><?php echo number_format($stats['active_customers']); ?></h3>
                                <p class="mb-0">Customer Aktif</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari nama, email, atau username..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Cari</button>
                            </div>
                            <div class="col-md-2">
                                <a href="customers.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            Daftar Customer (<?php echo $total_customers; ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Bergabung</th>
                                        <th>Total Orders</th>
                                        <th>Total Spent</th>
                                        <th>Last Order</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>Tidak ada customer ditemukan</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3">
                                                    <img src="../assets/images/default-avatar.jpg" 
                                                         alt="Avatar" class="rounded-circle" width="40" height="40">
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($customer['full_name']); ?></h6>
                                                    <small class="text-muted">@<?php echo htmlspecialchars($customer['username']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="mb-1">
                                                    <i class="fas fa-envelope text-primary me-1"></i>
                                                    <small><?php echo htmlspecialchars($customer['email']); ?></small>
                                                </div>
                                                <?php if (!empty($customer['phone'])): ?>
                                                <div>
                                                    <i class="fas fa-phone text-success me-1"></i>
                                                    <small><?php echo htmlspecialchars($customer['phone']); ?></small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?>
                                                <br>
                                                <span class="text-muted">
                                                    <?php 
                                                    $days = floor((time() - strtotime($customer['created_at'])) / (60 * 60 * 24));
                                                    echo $days; ?> hari lalu
                                                </span>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <h6 class="mb-1"><?php echo $customer['total_orders']; ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $customer['completed_orders']; ?> selesai
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatCurrency($customer['total_spent']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($customer['last_order_date']): ?>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($customer['last_order_date'])); ?>
                                            </small>
                                            <?php else: ?>
                                            <small class="text-muted">Belum pernah</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['total_orders'] > 0): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Baru</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewCustomer(<?php echo $customer['id']; ?>)" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="contactCustomer('<?php echo $customer['email']; ?>')" title="Contact">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Customers pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left me-1"></i>Sebelumnya
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        Selanjutnya<i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Detail Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="customerModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewCustomer(customerId) {
            // Load customer details via AJAX
            fetch(`customer-detail.php?id=${customerId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('customerModalBody').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('customerModal'));
                    modal.show();
                })
                .catch(error => {
                    alert('Error loading customer details');
                });
        }

        function contactCustomer(email) {
            window.location.href = `mailto:${email}`;
        }

        function exportCustomers() {
            // Generate CSV export
            const csvContent = generateCustomerCSV();
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `customers-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function generateCustomerCSV() {
            let csv = 'Name,Email,Username,Phone,Join Date,Total Orders,Total Spent,Status\n';
            
            // Get data from table
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 1) {
                    const name = cells[0].querySelector('h6').textContent.trim();
                    const email = cells[1].querySelector('small').textContent.trim();
                    // Add more fields as needed
                    csv += `"${name}","${email}"\n`;
                }
            });
            
            return csv;
        }
    </script>
</body>
</html>