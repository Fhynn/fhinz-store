<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success_message = 'Status pesanan berhasil diperbarui!';
    } catch (PDOException $e) {
        $error_message = 'Gagal memperbarui status pesanan!';
    }
}

// Get orders with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$items_per_page = 15;
$offset = ($page - 1) * $items_per_page;

$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(o.order_number LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetchColumn();
    $total_pages = ceil($total_orders / $items_per_page);
    
    // Get orders
    $sql = "SELECT o.*, u.full_name, u.email,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            $where_clause 
            ORDER BY o.created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order statistics
    $stats_stmt = $pdo->query("SELECT 
                              COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                              COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid,
                              COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                              COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                              COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
                              FROM orders");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $orders = [];
    $total_pages = 1;
    $stats = ['pending' => 0, 'paid' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0];
}

$status_info = [
    'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu Pembayaran'],
    'paid' => ['class' => 'info', 'icon' => 'credit-card', 'text' => 'Sudah Bayar'],
    'processing' => ['class' => 'primary', 'icon' => 'cog', 'text' => 'Diproses'],
    'completed' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Selesai'],
    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Dibatalkan']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Fhinz Store</title>
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
                        <h1 class="h3 mb-0">Kelola Pesanan</h1>
                        <p class="text-muted">Manage pesanan customer</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="refreshOrders()">
                            <i class="fas fa-sync me-2"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Order Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4><?php echo $stats['pending']; ?></h4>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-credit-card fa-2x mb-2"></i>
                                <h4><?php echo $stats['paid']; ?></h4>
                                <small>Paid</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-cog fa-2x mb-2"></i>
                                <h4><?php echo $stats['processing']; ?></h4>
                                <small>Processing</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h4><?php echo $stats['completed']; ?></h4>
                                <small>Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h4><?php echo $stats['cancelled']; ?></h4>
                                <small>Cancelled</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card bg-dark text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-list fa-2x mb-2"></i>
                                <h4><?php echo $total_orders; ?></h4>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari order ID, nama, atau email..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="orders.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Daftar Pesanan (<?php echo $total_orders; ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                                <p>Tidak ada pesanan ditemukan</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($orders as $order): ?>
                                    <?php $current_status = $status_info[$order['status']] ?? $status_info['pending']; ?>
                                    <tr>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" 
                                                        title="Update Status">
                                                    <i class="fas fa-edit"></i>
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
                        <nav aria-label="Orders pagination">
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

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Status Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="statusOrderId">
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Pilih Status Baru</label>
                            <select name="status" id="statusSelect" class="form-select" required>
                                <option value="pending">Menunggu Pembayaran</option>
                                <option value="paid">Sudah Bayar</option>
                                <option value="processing">Diproses</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Perubahan status akan langsung terlihat oleh customer.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('statusSelect').value = currentStatus;
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        function refreshOrders() {
            location.reload();
        }

        // Auto refresh every 30 seconds
        setInterval(function() {
            // Only refresh if there are pending orders
            const pendingCount = <?php echo $stats['pending']; ?>;
            if (pendingCount > 0) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>
                                            <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($order['full_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo $order['item_count']; ?> item(s)
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo formatCurrency($order['total_amount']); ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></small>
                                            <?php if (!empty($order['payment_proof'])): ?>
                                            <br><a href="../<?php echo $order['payment_proof']; ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-file-image"></i> Bukti
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $current_status['class']; ?>">
                                                <i class="fas fa-<?php echo $current_status['icon']; ?> me-1"></i>
                                                <?php echo $current_status['text']; ?>
                                            </span>
                                        </td>
                                        <td>