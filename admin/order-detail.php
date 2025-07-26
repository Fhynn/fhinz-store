<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success_message = 'Status pesanan berhasil diperbarui!';
    } catch (PDOException $e) {
        $error_message = 'Gagal memperbarui status pesanan!';
    }
}

try {
    // Get order details
    $stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.username 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: orders.php');
        exit();
    }
    
    // Get order items
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image_url, p.description, c.name as category_name 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id 
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: orders.php');
    exit();
}

$status_info = [
    'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu Pembayaran'],
    'paid' => ['class' => 'info', 'icon' => 'credit-card', 'text' => 'Sudah Bayar'],
    'processing' => ['class' => 'primary', 'icon' => 'cog', 'text' => 'Diproses'],
    'completed' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Selesai'],
    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Dibatalkan']
];

$current_status = $status_info[$order['status']] ?? $status_info['pending'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo htmlspecialchars($order['order_number']); ?> - Admin</title>
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
                        <h1 class="h3 mb-0">Detail Pesanan</h1>
                        <p class="text-muted">#<?php echo htmlspecialchars($order['order_number']); ?></p>
                    </div>
                    <div>
                        <a href="orders.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button class="btn btn-primary" onclick="printOrder()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Order Status Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon me-3">
                                        <i class="fas fa-<?php echo $current_status['icon']; ?> fa-2x text-<?php echo $current_status['class']; ?>"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Status: <?php echo $current_status['text']; ?></h5>
                                        <small class="text-muted">
                                            Terakhir diupdate: <?php echo date('d F Y, H:i', strtotime($order['updated_at'])); ?> WIB
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <button class="btn btn-outline-primary" onclick="updateOrderStatus()">
                                    <i class="fas fa-edit me-2"></i>Update Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Order Details -->
                    <div class="col-lg-8">
                        <!-- Order Items -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-box me-2"></i>
                                    Produk yang Dibeli
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($order_items as $index => $item): ?>
                                <div class="item-row p-4 <?php echo $index < count($order_items) - 1 ? 'border-bottom' : ''; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 col-4 mb-3 mb-md-0">
                                            <img src="<?php echo $item['image_url'] ?: '../assets/images/default-product.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 class="img-fluid rounded" style="width: 100%; height: 80px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-6 col-8 mb-3 mb-md-0">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>...</small>
                                        </div>
                                        <div class="col-md-2 col-6 text-center">
                                            <span class="badge bg-light text-dark">
                                                Qty: <?php echo $item['quantity']; ?>
                                            </span>
                                        </div>
                                        <div class="col-md-2 col-6 text-center">
                                            <div class="price-info">
                                                <small class="text-muted d-block"><?php echo formatCurrency($item['price']); ?> each</small>
                                                <strong class="text-primary"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Payment Proof -->
                        <?php if (!empty($order['payment_proof'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Bukti Pembayaran
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <img src="../<?php echo $order['payment_proof']; ?>" 
                                             alt="Bukti Pembayaran" class="img-fluid rounded shadow">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="payment-info">
                                            <h6>Informasi Pembayaran</h6>
                                            <div class="info-item mb-2">
                                                <strong>Metode:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?>
                                            </div>
                                            <div class="info-item mb-2">
                                                <strong>Total:</strong> <?php echo formatCurrency($order['total_amount']); ?>
                                            </div>
                                            <div class="info-item mb-2">
                                                <strong>Upload:</strong> <?php echo date('d/m/Y H:i', strtotime($order['updated_at'])); ?>
                                            </div>
                                            <a href="../<?php echo $order['payment_proof']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-external-link-alt me-2"></i>Lihat Full Size
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Order Notes -->
                        <?php if (!empty($order['notes'])): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    Catatan Pesanan
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <!-- Order Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informasi Pesanan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-item d-flex justify-content-between mb-2">
                                    <span>Order ID:</span>
                                    <strong><?php echo $order['order_number']; ?></strong>
                                </div>
                                <div class="info-item d-flex justify-content-between mb-2">
                                    <span>Tanggal:</span>
                                    <span><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="info-item d-flex justify-content-between mb-2">
                                    <span>Waktu:</span>
                                    <span><?php echo date('H:i', strtotime($order['created_at'])); ?> WIB</span>
                                </div>
                                <div class="info-item d-flex justify-content-between mb-3">
                                    <span>Pembayaran:</span>
                                    <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                </div>
                                <hr>
                                <div class="price-breakdown">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span><?php echo formatCurrency($order['total_amount']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Biaya Admin:</span>
                                        <span class="text-success">GRATIS</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pajak:</span>
                                        <span class="text-success">GRATIS</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong class="text-primary"><?php echo formatCurrency($order['total_amount']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    Informasi Customer
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="customer-info">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="../assets/images/default-avatar.jpg" alt="Avatar" 
                                             class="rounded-circle me-3" width="50" height="50">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($order['full_name']); ?></h6>
                                            <small class="text-muted">@<?php echo htmlspecialchars($order['username']); ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="contact-info">
                                        <div class="info-item mb-2">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            <a href="mailto:<?php echo $order['email']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($order['email']); ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($order['phone'])): ?>
                                        <div class="info-item mb-2">
                                            <i class="fas fa-phone text-success me-2"></i>
                                            <a href="tel:<?php echo $order['phone']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($order['phone']); ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-cog me-2"></i>
                                    Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" onclick="updateOrderStatus()">
                                        <i class="fas fa-edit me-2"></i>Update Status
                                    </button>
                                    <button class="btn btn-outline-info" onclick="sendEmail()">
                                        <i class="fas fa-envelope me-2"></i>Kirim Email
                                    </button>
                                    <button class="btn btn-outline-success" onclick="contactWhatsApp()">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Pilih Status Baru</label>
                            <select name="status" id="statusSelect" class="form-select" required>
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Sudah Bayar</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Status akan otomatis terlihat oleh customer dan email notifikasi akan dikirim.</small>
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
        function updateOrderStatus() {
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        function printOrder() {
            window.print();
        }

        function sendEmail() {
            const email = '<?php echo $order['email']; ?>';
            const subject = 'Update Pesanan #<?php echo $order['order_number']; ?>';
            const body = 'Halo <?php echo htmlspecialchars($order['full_name']); ?>,\n\nBerikut update untuk pesanan Anda:\n\nOrder ID: <?php echo $order['order_number']; ?>\nStatus: <?php echo $current_status['text']; ?>\nTotal: <?php echo formatCurrency($order['total_amount']); ?>\n\nTerima kasih,\nFhinz Store Team';
            
            window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        }

        function contactWhatsApp() {
            const phone = '<?php echo $order['phone'] ?: '6281234567890'; ?>';
            const message = 'Halo <?php echo htmlspecialchars($order['full_name']); ?>, ini update untuk pesanan #<?php echo $order['order_number']; ?>. Status: <?php echo $current_status['text']; ?>. Terima kasih telah berbelanja di Fhinz Store!';
            
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
        }
    </script>

    <style>
        .item-row:hover {
            background-color: #f8f9fa;
        }
        
        .price-info small {
            font-size: 0.8rem;
        }
        
        .customer-info .contact-info a {
            color: inherit;
        }
        
        .customer-info .contact-info a:hover {
            text-decoration: underline !important;
        }
        
        @media print {
            .admin-sidebar,
            .admin-topnav,
            .btn,
            .modal {
                display: none !important;
            }
            
            .admin-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</body>
</html>