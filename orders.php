<?php
require_once 'config/database.php';
requireLogin();

// Get user orders
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Build query
$where_conditions = ["o.user_id = ?"];
$params = [$_SESSION['user_id']];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM orders o WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetchColumn();
    $total_pages = ceil($total_orders / $items_per_page);
    
    // Get orders
    $sql = "SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
            FROM orders o 
            WHERE $where_clause 
            ORDER BY o.created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $orders = [];
    $total_pages = 1;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Fhinz Store</title>
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
                            <li class="breadcrumb-item active">Pesanan Saya</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Pesanan Saya</h1>
                    <p class="text-muted mb-0">Kelola dan pantau status pesanan Anda</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Pesan Lagi
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders Content -->
    <section class="orders-section py-5">
        <div class="container">
            <!-- Filter Tabs -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-tabs justify-content-center">
                        <li class="nav-item">
                            <a class="nav-link <?php echo empty($status_filter) ? 'active' : ''; ?>" 
                               href="orders.php">
                                <i class="fas fa-list me-2"></i>Semua
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status_filter === 'pending' ? 'active' : ''; ?>" 
                               href="orders.php?status=pending">
                                <i class="fas fa-clock me-2"></i>Menunggu Pembayaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status_filter === 'paid' ? 'active' : ''; ?>" 
                               href="orders.php?status=paid">
                                <i class="fas fa-credit-card me-2"></i>Sudah Bayar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status_filter === 'processing' ? 'active' : ''; ?>" 
                               href="orders.php?status=processing">
                                <i class="fas fa-cog me-2"></i>Diproses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status_filter === 'completed' ? 'active' : ''; ?>" 
                               href="orders.php?status=completed">
                                <i class="fas fa-check-circle me-2"></i>Selesai
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>" 
                               href="orders.php?status=cancelled">
                                <i class="fas fa-times-circle me-2"></i>Dibatalkan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Orders List -->
            <?php if (empty($orders)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="empty-orders text-center py-5">
                        <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
                        <h4>Belum Ada Pesanan</h4>
                        <p class="text-muted mb-4">
                            <?php if (!empty($status_filter)): ?>
                                Tidak ada pesanan dengan status "<?php echo ucfirst($status_filter); ?>"
                            <?php else: ?>
                                Anda belum pernah melakukan pemesanan. Mulai berbelanja sekarang!
                            <?php endif; ?>
                        </p>
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>Mulai Belanja
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">
                                            <i class="fas fa-receipt me-2"></i>
                                            Order #<?php echo htmlspecialchars($order['order_number']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <?php
                                        $status_classes = [
                                            'pending' => 'warning',
                                            'paid' => 'info',
                                            'processing' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $status_labels = [
                                            'pending' => 'Menunggu Pembayaran',
                                            'paid' => 'Sudah Bayar',
                                            'processing' => 'Diproses',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        $status_class = $status_classes[$order['status']] ?? 'secondary';
                                        $status_label = $status_labels[$order['status']] ?? ucfirst($order['status']);
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?> mb-2">
                                            <?php echo $status_label; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="order-info">
                                            <div class="order-summary mb-2">
                                                <strong><?php echo $order['item_count']; ?> produk</strong>
                                                <span class="text-muted mx-2">•</span>
                                                <strong class="text-primary"><?php echo formatCurrency($order['total_amount']); ?></strong>
                                                <span class="text-muted mx-2">•</span>
                                                <span class="text-muted"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                            </div>
                                            
                                            <?php if (!empty($order['notes'])): ?>
                                            <div class="order-notes">
                                                <small class="text-muted">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    <?php echo htmlspecialchars(substr($order['notes'], 0, 100)); ?>
                                                    <?php if (strlen($order['notes']) > 100): ?>...<?php endif; ?>
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="order-actions">
                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm me-2">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                            
                                            <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-warning btn-sm me-2" 
                                                    onclick="uploadPayment(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-upload me-1"></i>Bayar
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-times me-1"></i>Batal
                                            </button>
                                            <?php elseif ($order['status'] === 'completed'): ?>
                                            <button class="btn btn-success btn-sm me-2" 
                                                    onclick="showReviewModal(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-star me-1"></i>Review
                                            </button>
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="reorder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-redo me-1"></i>Pesan Lagi
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Orders pagination">
                        <ul class="pagination justify-content-center">
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
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Payment Upload Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentUploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="order_id" id="modalOrderId">
                        <div class="mb-3">
                            <label class="form-label">Pilih File Bukti Pembayaran</label>
                            <input type="file" class="form-control" name="payment_proof" 
                                   accept="image/*,application/pdf" required>
                            <small class="text-muted">Format: JPG, PNG, PDF. Maksimal 5MB.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="submitPaymentUpload()">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-star me-2"></i>Berikan Review
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <input type="hidden" name="order_id" id="reviewOrderId">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating-input">
                                <input type="radio" name="rating" value="5" id="r5">
                                <label for="r5"><i class="fas fa-star"></i></label>
                                <input type="radio" name="rating" value="4" id="r4">
                                <label for="r4"><i class="fas fa-star"></i></label>
                                <input type="radio" name="rating" value="3" id="r3">
                                <label for="r3"><i class="fas fa-star"></i></label>
                                <input type="radio" name="rating" value="2" id="r2">
                                <label for="r2"><i class="fas fa-star"></i></label>
                                <input type="radio" name="rating" value="1" id="r1">
                                <label for="r1"><i class="fas fa-star"></i></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Review</label>
                            <textarea name="review" class="form-control" rows="4" 
                                      placeholder="Bagikan pengalaman Anda..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="submitReview()">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Review
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function uploadPayment(orderId) {
            document.getElementById('modalOrderId').value = orderId;
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }

        function submitPaymentUpload() {
            const form = document.getElementById('paymentUploadForm');
            const formData = new FormData(form);
            
            fetch('upload-payment-proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Bukti pembayaran berhasil diupload!');
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message || 'Gagal mengupload bukti pembayaran!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }

        function cancelOrder(orderId) {
            if (!confirm('Yakin ingin membatalkan pesanan ini?')) {
                return;
            }

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

        function showReviewModal(orderId) {
            document.getElementById('reviewOrderId').value = orderId;
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }

        function submitReview() {
            const form = document.getElementById('reviewForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            if (!data.rating) {
                showAlert('error', 'Silakan pilih rating!');
                return;
            }

            fetch('submit-review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Review berhasil dikirim!');
                    bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                    form.reset();
                } else {
                    showAlert('error', data.message || 'Gagal mengirim review!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }

        function reorder(orderId) {
            if (confirm('Tambahkan produk dari pesanan ini ke keranjang?')) {
                fetch('reorder.php', {
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
                        showAlert('success', 'Produk berhasil ditambahkan ke keranjang!');
                        updateCartCount();
                    } else {
                        showAlert('error', data.message || 'Gagal mengulangi pesanan!');
                    }
                })
                .catch(error => {
                    showAlert('error', 'Terjadi kesalahan sistem!');
                });
            }
        }

        // Rating input functionality
        document.querySelectorAll('.rating-input input').forEach(input => {
            input.addEventListener('change', function() {
                const rating = this.value;
                const labels = document.querySelectorAll('.rating-input label');
                
                labels.forEach((label, index) => {
                    if (index >= (5 - rating)) {
                        label.classList.add('selected');
                    } else {
                        label.classList.remove('selected');
                    }
                });
            });
        });
    </script>

    <style>
        .order-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .order-actions .btn {
            margin-bottom: 0.5rem;
        }

        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
            gap: 0.25rem;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            color: #ddd;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .rating-input label:hover,
        .rating-input label.selected,
        .rating-input input:checked ~ label {
            color: #ffc107;
        }

        .empty-orders {
            padding: 3rem 2rem;
        }

        @media (max-width: 768px) {
            .order-actions {
                margin-top: 1rem;
            }

            .order-actions .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .nav-tabs .nav-link {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
            }

            .nav-tabs .nav-link i {
                display: none;
            }
        }
    </style>
</body>
</html>