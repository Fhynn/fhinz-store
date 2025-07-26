<?php
require_once 'config/database.php';
requireLogin();

$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    header('Location: index.php');
    exit();
}

try {
    // Get order details
    $stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.order_number = ? AND o.user_id = ?");
    $stmt->execute([$order_number, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: index.php');
        exit();
    }
    
    // Get order items
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                          FROM order_items oi 
                          LEFT JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$order['id']]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Success Header -->
    <section class="success-header py-5 bg-success text-white">
        <div class="container text-center">
            <div class="success-icon mb-4">
                <i class="fas fa-check-circle fa-5x"></i>
            </div>
            <h1 class="display-4 mb-3">Pesanan Berhasil Dibuat!</h1>
            <p class="lead mb-0">Terima kasih atas kepercayaan Anda kepada Fhinz Store</p>
        </div>
    </section>

    <!-- Order Details -->
    <section class="order-details py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Order Summary Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Detail Pesanan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Informasi Pesanan</h6>
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="40%">Order ID:</td>
                                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal:</td>
                                            <td><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB</td>
                                        </tr>
                                        <tr>
                                            <td>Status:</td>
                                            <td><span class="badge bg-warning">Menunggu Pembayaran</span></td>
                                        </tr>
                                        <tr>
                                            <td>Total:</td>
                                            <td><strong class="text-primary"><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Informasi Customer</h6>
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="40%">Nama:</td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Email:</td>
                                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Pembayaran:</td>
                                            <td><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                        </tr>
                                        <?php if (!empty($order['notes'])): ?>
                                        <tr>
                                            <td>Catatan:</td>
                                            <td><?php echo htmlspecialchars($order['notes']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <h6>Produk yang Dibeli</h6>
                            <div class="order-items">
                                <?php foreach ($order_items as $item): ?>
                                <div class="order-item d-flex align-items-center p-3 border rounded mb-3">
                                    <img src="<?php echo $item['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="order-item-image rounded me-3">
                                    <div class="order-item-info flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <small class="text-muted">
                                            Quantity: <?php echo $item['quantity']; ?> × 
                                            <?php echo formatCurrency($item['price']); ?>
                                        </small>
                                    </div>
                                    <div class="order-item-total">
                                        <strong><?php echo formatCurrency($item['price'] * $item['quantity']); ?></strong>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Instruksi Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $payment_method = $order['payment_method'];
                            switch($payment_method):
                                case 'bank_transfer':
                            ?>
                            <div class="payment-instructions">
                                <h6><i class="fas fa-university me-2"></i>Transfer Bank</h6>
                                <p>Silakan transfer ke salah satu rekening berikut dengan nominal <strong>EXACT</strong>:</p>
                                
                                <div class="bank-accounts">
                                    <div class="bank-account mb-3 p-3 bg-light rounded">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <strong>Bank BCA</strong><br>
                                                <span class="account-number">1234567890</span><br>
                                                <span class="account-name">a.n. Fhinz Store</span>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('1234567890')">
                                                    <i class="fas fa-copy me-1"></i>Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bank-account mb-3 p-3 bg-light rounded">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <strong>Bank BNI</strong><br>
                                                <span class="account-number">0987654321</span><br>
                                                <span class="account-name">a.n. Fhinz Store</span>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('0987654321')">
                                                    <i class="fas fa-copy me-1"></i>Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Penting:</h6>
                                    <ul class="mb-0">
                                        <li>Transfer dengan nominal <strong><?php echo formatCurrency($order['total_amount']); ?></strong></li>
                                        <li>Setelah transfer, upload bukti pembayaran</li>
                                        <li>Pesanan akan diproses maksimal 2 jam setelah pembayaran dikonfirmasi</li>
                                        <li>Akun premium akan dikirim via email yang terdaftar</li>
                                    </ul>
                                </div>
                            </div>
                            <?php break; case 'ewallet': ?>
                            <div class="payment-instructions">
                                <h6><i class="fas fa-mobile-alt me-2"></i>E-Wallet Payment</h6>
                                <p>Silakan transfer ke nomor e-wallet berikut dengan nominal <strong>EXACT</strong>:</p>
                                
                                <div class="ewallet-accounts">
                                    <div class="ewallet-account mb-3 p-3 bg-light rounded">
                                        <strong>GoPay:</strong> 0812-3456-7890 (Fhinz Store)<br>
                                        <strong>OVO:</strong> 0812-3456-7890<br>
                                        <strong>DANA:</strong> 0812-3456-7890
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Cara Pembayaran:</h6>
                                    <ul class="mb-0">
                                        <li>Buka aplikasi e-wallet Anda</li>
                                        <li>Transfer ke nomor di atas dengan nominal <?php echo formatCurrency($order['total_amount']); ?></li>
                                        <li>Screenshot bukti transfer</li>
                                        <li>Upload bukti pembayaran di website</li>
                                    </ul>
                                </div>
                            </div>
                            <?php break; case 'qris': ?>
                            <div class="payment-instructions text-center">
                                <h6><i class="fas fa-qrcode me-2"></i>QRIS Payment</h6>
                                <p>Scan QR Code berikut dengan aplikasi mobile banking atau e-wallet Anda:</p>
                                
                                <div class="qr-code mb-3">
                                    <img src="assets/images/qris-sample.png" alt="QRIS Code" class="img-fluid" style="max-width: 300px;">
                                </div>

                                <div class="alert alert-info text-start">
                                    <h6><i class="fas fa-info-circle me-2"></i>Cara Pembayaran:</h6>
                                    <ul class="mb-0">
                                        <li>Buka aplikasi mobile banking atau e-wallet</li>
                                        <li>Pilih menu Scan QR</li>
                                        <li>Scan QR Code di atas</li>
                                        <li>Pastikan nominal <?php echo formatCurrency($order['total_amount']); ?></li>
                                        <li>Konfirmasi pembayaran</li>
                                    </ul>
                                </div>
                            </div>
                            <?php break; case 'crypto': ?>
                            <div class="payment-instructions">
                                <h6><i class="fab fa-bitcoin me-2"></i>Cryptocurrency Payment</h6>
                                <p>Transfer ke wallet address berikut:</p>
                                
                                <div class="crypto-addresses">
                                    <div class="crypto-address mb-3 p-3 bg-light rounded">
                                        <strong>Bitcoin (BTC):</strong><br>
                                        <code class="crypto-addr">1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa</code>
                                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Perhatian:</h6>
                                    <ul class="mb-0">
                                        <li>Rate: 1 BTC = $43,000 (dikunci 15 menit)</li>
                                        <li>Kirim exact amount sesuai rate saat ini</li>
                                        <li>Minimal konfirmasi: 3 blocks</li>
                                        <li>Upload screenshot transaksi</li>
                                    </ul>
                                </div>
                            </div>
                            <?php break; endswitch; ?>
                        </div>
                    </div>

                    <!-- Upload Payment Proof -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-upload me-2"></i>
                                Upload Bukti Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="uploadForm" enctype="multipart/form-data">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Pilih File Bukti Pembayaran</label>
                                    <input type="file" class="form-control" name="payment_proof" 
                                           accept="image/*" required>
                                    <small class="text-muted">
                                        Format: JPG, PNG, PDF. Maksimal 5MB.
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Catatan Tambahan (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="3" 
                                              placeholder="Catatan tambahan mengenai pembayaran..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>
                                    Upload Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center">
                        <a href="orders.php" class="btn btn-outline-primary me-3">
                            <i class="fas fa-list me-2"></i>
                            Lihat Semua Pesanan
                        </a>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Lanjut Belanja
                        </a>
                    </div>

                    <!-- Support Contact -->
                    <div class="text-center mt-4">
                        <div class="alert alert-light">
                            <h6><i class="fas fa-headset me-2"></i>Butuh Bantuan?</h6>
                            <p class="mb-2">Tim support kami siap membantu 24/7</p>
                            <div class="support-contacts">
                                <a href="https://wa.me/6281234567890" class="btn btn-success btn-sm me-2" target="_blank">
                                    <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                </a>
                                <a href="mailto:support@fhinzstore.com" class="btn btn-info btn-sm">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </a>
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
        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showAlert('success', 'Nomor rekening berhasil disalin!');
            }).catch(function(err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showAlert('success', 'Nomor rekening berhasil disalin!');
            });
        }

        // Upload payment proof
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...';
            submitBtn.disabled = true;
            
            fetch('upload-payment-proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Bukti pembayaran berhasil diupload! Pesanan Anda akan segera diproses.');
                    this.reset();
                } else {
                    showAlert('error', data.message || 'Gagal mengupload bukti pembayaran!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran';
                submitBtn.disabled = false;
            });
        });

        // Auto refresh order status every 30 seconds
        setInterval(function() {
            fetch(`check-order-status.php?order=${<?php echo json_encode($order['order_number']); ?>}`)
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

        // Show countdown timer for QRIS/Crypto
        <?php if (in_array($payment_method, ['qris', 'crypto'])): ?>
        let timeLeft = 15 * 60; // 15 minutes
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            const timerElement = document.getElementById('paymentTimer');
            if (timerElement) {
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
            
            if (timeLeft <= 0) {
                showAlert('warning', 'Waktu pembayaran telah habis. Silakan buat pesanan baru.');
                return;
            }
            
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
        
        // Add timer to payment instructions
        document.addEventListener('DOMContentLoaded', function() {
            const paymentInstructions = document.querySelector('.payment-instructions');
            if (paymentInstructions) {
                const timerHTML = `
                    <div class="alert alert-warning mt-3 text-center">
                        <i class="fas fa-clock me-2"></i>
                        Waktu pembayaran tersisa: <strong id="paymentTimer">15:00</strong>
                    </div>
                `;
                paymentInstructions.insertAdjacentHTML('beforeend', timerHTML);
                updateTimer();
            }
        });
        <?php endif; ?>
    </script>

    <style>
        .success-icon {
            animation: bounceIn 1s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .account-number {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            font-weight: bold;
            color: #007bff;
        }

        .crypto-addr {
            font-size: 0.9rem;
            word-break: break-all;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .bank-account,
        .ewallet-account,
        .crypto-address {
            transition: all 0.3s ease;
        }

        .bank-account:hover,
        .ewallet-account:hover,
        .crypto-address:hover {
            background-color: #e3f2fd !important;
            border-left: 4px solid #007bff;
        }

        .support-contacts .btn {
            margin: 0.25rem;
        }

        @media (max-width: 768px) {
            .display-4 {
                font-size: 2rem;
            }

            .bank-account .row,
            .crypto-address {
                flex-direction: column;
                text-align: center;
            }

            .bank-account .col-md-4 {
                margin-top: 1rem;
            }
        }
    </style>
</body>
</html>