<?php
require_once 'config/database.php';
requireLogin();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

try {
    // Get order details
    $stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.id = ? AND o.user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
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
    <title>Detail Pesanan #<?php echo htmlspecialchars($order['order_number']); ?> - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header py-4 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="orders.php">Pesanan</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Detail Pesanan</h1>
                    <p class="text-muted mb-0">#<?php echo htmlspecialchars($order['order_number']); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-<?php echo $current_status['class']; ?> fs-6">
                        <i class="fas fa-<?php echo $current_status['icon']; ?> me-1"></i>
                        <?php echo $current_status['text']; ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Timeline -->
    <section class="order-timeline py-4 bg-primary text-white">
        <div class="container">
            <div class="timeline">
                <div class="timeline-item <?php echo in_array($order['status'], ['pending', 'paid', 'processing', 'completed']) ? 'completed' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Pesanan Dibuat</h6>
                        <small><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></small>
                    </div>
                </div>

                <div class="timeline-item <?php echo in_array($order['status'], ['paid', 'processing', 'completed']) ? 'completed' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Pembayaran</h6>
                        <small>
                            <?php if ($order['status'] === 'pending'): ?>
                                Menunggu pembayaran
                            <?php else: ?>
                                Pembayaran dikonfirmasi
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <div class="timeline-item <?php echo in_array($order['status'], ['processing', 'completed']) ? 'completed' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Diproses</h6>
                        <small>Pesanan sedang diproses</small>
                    </div>
                </div>

                <div class="timeline-item <?php echo $order['status'] === 'completed' ? 'completed' : ''; ?>">
                    <div class="timeline-marker">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Selesai</h6>
                        <small>
                            <?php if ($order['status'] === 'completed'): ?>
                                <?php echo date('d M Y, H:i', strtotime($order['updated_at'])); ?>
                            <?php else: ?>
                                Akun akan dikirim setelah diproses
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Details -->
    <section class="order-details py-5">
        <div class="container">
            <div class="row">
                <!-- Order Info -->
                <div class="col-lg-8 mb-4">
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
                            <div class="order-item-detail p-4 <?php echo $index < count($order_items) - 1 ? 'border-bottom' : ''; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-2 col-4 mb-3 mb-md-0">
                                        <img src="<?php echo $item['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-6 col-8 mb-3 mb-md-0">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</small>
                                    </div>
                                    <div class="col-md-2 col-6 text-center">
                                        <span class="quantity-badge badge bg-light text-dark">
                                            Qty: <?php echo $item['quantity']; ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2 col-6 text-center">
                                        <div class="item-price">
                                            <div class="unit-price text-muted small">
                                                <?php echo formatCurrency($item['price']); ?>
                                            </div>
                                            <div class="total-price fw-bold text-primary">
                                                <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <?php if ($order['status'] === 'pending'): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Menunggu Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Silakan lakukan pembayaran untuk melanjutkan pesanan Anda.</p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-warning" onclick="uploadPayment(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-upload me-2"></i>Upload Bukti Bayar
                                </button>
                                <button class="btn btn-outline-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-times me-2"></i>Batalkan Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Delivery Info -->
                    <?php if ($order['status'] === 'completed'): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Pesanan Selesai
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle me-2"></i>
                                Akun premium telah dikirim ke email Anda. Silakan cek email untuk detail login.
                            </div>
                            <div class="d-grid gap-2 d-md-flex">
                                <button class="btn btn-success" onclick="downloadInvoice(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-download me-2"></i>Download Invoice
                                </button>
                                <button class="btn btn-outline-primary" onclick="showReviewModal(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-star me-2"></i>Berikan Review
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Order Notes -->
                    <?php if (!empty($order['notes'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                Catatan Pesanan
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary sticky-top" style="top: 100px;">
                        <!-- Order Info Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Ringkasan Pesanan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Order ID:</span>
                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                </div>
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Tanggal:</span>
                                    <span><?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Pembayaran:</span>
                                    <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                </div>
                                <div class="summary-row d-flex justify-content-between mb-3">
                                    <span>Status:</span>
                                    <span class="badge bg-<?php echo $current_status['class']; ?>">
                                        <?php echo $current_status['text']; ?>
                                    </span>
                                </div>
                                <hr>
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                                </div>
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Biaya Admin:</span>
                                    <span class="text-success">GRATIS</span>
                                </div>
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Pajak:</span>
                                    <span class="text-success">GRATIS</span>
                                </div>
                                <hr>
                                <div class="summary-total d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong class="text-primary"><?php echo formatCurrency($order['total_amount']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    Informasi Pemesan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="customer-info">
                                    <div class="info-row mb-2">
                                        <small class="text-muted">Nama:</small><br>
                                        <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                    </div>
                                    <div class="info-row mb-2">
                                        <small class="text-muted">Email:</small><br>
                                        <span><?php echo htmlspecialchars($order['email']); ?></span>
                                    </div>
                                    <?php if (!empty($order['phone'])): ?>
                                    <div class="info-row">
                                        <small class="text-muted">Telepon:</small><br>
                                        <span><?php echo htmlspecialchars($order['phone']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Support -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-headset me-2"></i>
                                    Butuh Bantuan?
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">Tim support kami siap membantu 24/7</p>
                                <div class="d-grid gap-2">
                                    <a href="https://wa.me/6281234567890" class="btn btn-success btn-sm" target="_blank">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </a>
                                    <a href="mailto:support@fhinzstore.com" class="btn btn-info btn-sm">
                                        <i class="fas fa-envelope me-2"></i>Email Support
                                    </a>
                                    <a href="contact.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-question-circle me-2"></i>FAQ
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function uploadPayment(orderId) {
            // Redirect to order success page with upload form
            window.location.href = `order-success.php?order=<?php echo $order['order_number']; ?>`;
        }

        function cancelOrder(orderId) {
            if (confirm('Yakin ingin membatalkan pesanan ini?')) {
                fetch('cancel-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Pesanan berhasil dibatalkan');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', data.message || 'Gagal membatalkan pesanan!');
                    }
                })
                .catch(error => {
                    showAlert('error', 'Terjadi kesalahan sistem!');
                });
            }
        }

        function downloadInvoice(orderId) {
            // Create a simple invoice download
            const invoice = generateInvoiceHTML();
            const blob = new Blob([invoice], { type: 'text/html' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `invoice-<?php echo $order['order_number']; ?>.html`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function generateInvoiceHTML() {
            return `
<!DOCTYPE html>
<html>
<head>
    <title>Invoice - <?php echo $order['order_number']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FHINZ STORE</h1>
        <h2>INVOICE</h2>
        <p>Order #<?php echo $order['order_number']; ?></p>
    </div>
    
    <div class="invoice-details">
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        <p><strong>Date:</strong> <?php echo date('d F Y', strtotime($order['created_at'])); ?></p>
        <p><strong>Status:</strong> <?php echo $current_status['text']; ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo formatCurrency($item['price']); ?></td>
                <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td colspan="3">TOTAL</td>
                <td><?php echo formatCurrency($order['total_amount']); ?></td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 30px;">
        <p>Thank you for your business!</p>
        <p>For support, contact us at support@fhinzstore.com</p>
    </div>
</body>
</html>
            `;
        }

        function showReviewModal(orderId) {
            showAlert('info', 'Fitur review akan segera tersedia!');
        }

        // Auto refresh status every 30 seconds for pending orders
        <?php if ($order['status'] === 'pending'): ?>
        setInterval(function() {
            fetch(`check-order-status.php?order=<?php echo $order['order_number']; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.status !== 'pending') {
                        location.reload();
                    }
                })
                .catch(error => {
                    // Ignore errors for status check
                });
        }, 30000);
        <?php endif; ?>
    </script>

    <style>
        .timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            z-index: 1;
        }

        .timeline-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }

        .timeline-item.completed .timeline-marker {
            background: rgba(255, 255, 255, 0.9);
            color: #007bff;
        }

        .timeline-content h6 {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .timeline-content small {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .order-item-detail {
            transition: background-color 0.3s ease;
        }

        .order-item-detail:hover {
            background-color: #f8f9fa;
        }

        .quantity-badge {
            font-size: 0.8rem;
        }

        .item-price .unit-price {
            font-size: 0.8rem;
        }

        .summary-row {
            font-size: 0.9rem;
        }

        .summary-total {
            font-size: 1.1rem;
        }

        .customer-info .info-row {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .customer-info .info-row:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .timeline {
                flex-direction: column;
                max-width: 300px;
            }

            .timeline::before {
                top: 0;
                bottom: 0;
                left: 20px;
                width: 2px;
                height: auto;
            }

            .timeline-item {
                display: flex;
                align-items: center;
                text-align: left;
                margin-bottom: 20px;
            }

            .timeline-marker {
                margin: 0 15px 0 0;
                flex-shrink: 0;
            }

            .timeline-content {
                flex: 1;
            }

            .order-summary {
                position: static !important;
            }
        }
    </style>
</body>
</html>