<?php
require_once 'config/database.php';
requireLogin();

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Get cart items with product details
$cart_items = [];
$total_amount = 0;

$product_ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($product_ids) - 1) . '?';

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        if (isset($_SESSION['cart'][$product['id']])) {
            $cart_item = $_SESSION['cart'][$product['id']];
            $cart_item['product'] = $product;
            $cart_item['subtotal'] = $product['price'] * $cart_item['quantity'];
            $cart_items[] = $cart_item;
            $total_amount += $cart_item['subtotal'];
        }
    }
} catch (PDOException $e) {
    header('Location: cart.php');
    exit();
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $user = [];
}

// Handle form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($payment_method)) {
        $error_message = 'Silakan pilih metode pembayaran!';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $order_number = generateOrderNumber();
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, notes) VALUES (?, ?, ?, 'pending', ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $order_number, $total_amount, $payment_method, $notes]);
            $order_id = $pdo->lastInsertId();
            
            // Create order items
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product']['id'], $item['quantity'], $item['product']['price']]);
            }
            
            $pdo->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            // Redirect to order success page
            header("Location: order-success.php?order=$order_number");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'Terjadi kesalahan saat memproses pesanan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Checkout Header -->
    <section class="checkout-header py-4 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-credit-card me-2"></i>Checkout
                    </h1>
                    <p class="mb-0 opacity-75">Selesaikan pembelian Anda</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <!-- Checkout Steps -->
                    <div class="checkout-steps">
                        <span class="step completed">
                            <i class="fas fa-shopping-cart"></i>
                            <small>Keranjang</small>
                        </span>
                        <span class="step-divider"></span>
                        <span class="step active">
                            <i class="fas fa-credit-card"></i>
                            <small>Checkout</small>
                        </span>
                        <span class="step-divider"></span>
                        <span class="step">
                            <i class="fas fa-check-circle"></i>
                            <small>Selesai</small>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Content -->
    <section class="checkout-section py-5">
        <div class="container">
            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="checkoutForm">
                <div class="row">
                    <!-- Billing Information -->
                    <div class="col-lg-8">
                        <div class="checkout-form">
                            <!-- Customer Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user me-2"></i>
                                        Informasi Customer
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">No. Telepon</label>
                                            <input type="tel" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informasi ini akan digunakan untuk pengiriman akun premium. 
                                        <a href="profile.php" class="alert-link">Edit profil</a> jika diperlukan.
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card me-2"></i>
                                        Metode Pembayaran
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="payment-methods">
                                        <!-- Bank Transfer -->
                                        <div class="payment-method mb-3">
                                            <input type="radio" name="payment_method" value="bank_transfer" 
                                                   id="bank_transfer" class="form-check-input">
                                            <label for="bank_transfer" class="payment-label">
                                                <div class="payment-info">
                                                    <div class="payment-icon">
                                                        <i class="fas fa-university"></i>
                                                    </div>
                                                    <div class="payment-details">
                                                        <h6>Transfer Bank</h6>
                                                        <p>BCA, BNI, BRI, Mandiri</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- E-Wallet -->
                                        <div class="payment-method mb-3">
                                            <input type="radio" name="payment_method" value="ewallet" 
                                                   id="ewallet" class="form-check-input">
                                            <label for="ewallet" class="payment-label">
                                                <div class="payment-info">
                                                    <div class="payment-icon">
                                                        <i class="fas fa-mobile-alt"></i>
                                                    </div>
                                                    <div class="payment-details">
                                                        <h6>E-Wallet</h6>
                                                        <p>GoPay, OVO, DANA, ShopeePay</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- QRIS -->
                                        <div class="payment-method mb-3">
                                            <input type="radio" name="payment_method" value="qris" 
                                                   id="qris" class="form-check-input">
                                            <label for="qris" class="payment-label">
                                                <div class="payment-info">
                                                    <div class="payment-icon">
                                                        <i class="fas fa-qrcode"></i>
                                                    </div>
                                                    <div class="payment-details">
                                                        <h6>QRIS</h6>
                                                        <p>Scan QR Code untuk pembayaran</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Cryptocurrency -->
                                        <div class="payment-method mb-3">
                                            <input type="radio" name="payment_method" value="crypto" 
                                                   id="crypto" class="form-check-input">
                                            <label for="crypto" class="payment-label">
                                                <div class="payment-info">
                                                    <div class="payment-icon">
                                                        <i class="fab fa-bitcoin"></i>
                                                    </div>
                                                    <div class="payment-details">
                                                        <h6>Cryptocurrency</h6>
                                                        <p>Bitcoin, Ethereum, USDT</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Payment Details (shown based on selection) -->
                                    <div id="paymentDetails" class="payment-details-container mt-4" style="display: none;">
                                        <!-- Content will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-sticky-note me-2"></i>
                                        Catatan Pesanan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <textarea name="notes" class="form-control" rows="3" 
                                              placeholder="Catatan tambahan untuk pesanan Anda (optional)"></textarea>
                                    <small class="text-muted">
                                        Contoh: Kirim ke email alternatif, request aktivasi cepat, dll.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="order-summary sticky-top" style="top: 100px;">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt me-2"></i>
                                        Ringkasan Pesanan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Order Items -->
                                    <div class="order-items mb-3">
                                        <?php foreach ($cart_items as $item): ?>
                                        <div class="order-item d-flex align-items-center mb-3">
                                            <img src="<?php echo $item['product']['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product']['name']); ?>" 
                                                 class="order-item-image rounded me-3">
                                            <div class="order-item-info flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['product']['name']); ?></h6>
                                                <small class="text-muted">
                                                    Qty: <?php echo $item['quantity']; ?> × 
                                                    <?php echo formatCurrency($item['product']['price']); ?>
                                                </small>
                                            </div>
                                            <div class="order-item-price">
                                                <strong><?php echo formatCurrency($item['subtotal']); ?></strong>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <hr>

                                    <!-- Price Breakdown -->
                                    <div class="price-breakdown">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal</span>
                                            <span><?php echo formatCurrency($total_amount); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Biaya Admin</span>
                                            <span class="text-success">GRATIS</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Pajak</span>
                                            <span class="text-success">GRATIS</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total</strong>
                                            <strong class="text-primary"><?php echo formatCurrency($total_amount); ?></strong>
                                        </div>
                                    </div>

                                    <!-- Checkout Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg" id="checkoutBtn">
                                            <i class="fas fa-lock me-2"></i>
                                            Bayar Sekarang
                                        </button>
                                        <a href="cart.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Kembali ke Keranjang
                                        </a>
                                    </div>

                                    <!-- Security Badges -->
                                    <div class="security-badges mt-4 text-center">
                                        <div class="row">
                                            <div class="col-4">
                                                <i class="fas fa-shield-alt text-success mb-1"></i>
                                                <small class="d-block">SSL Secure</small>
                                            </div>
                                            <div class="col-4">
                                                <i class="fas fa-lock text-primary mb-1"></i>
                                                <small class="d-block">Encrypted</small>
                                            </div>
                                            <div class="col-4">
                                                <i class="fas fa-check-circle text-info mb-1"></i>
                                                <small class="d-block">Verified</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trust Indicators -->
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-award me-2 text-warning"></i>
                                        Jaminan Kami
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Akun premium asli 100%</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Garansi penggantian gratis</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Support 24/7</small>
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <small>Pengiriman instan</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                showPaymentDetails(this.value);
            });
        });

        function showPaymentDetails(method) {
            const detailsContainer = document.getElementById('paymentDetails');
            let content = '';

            switch(method) {
                case 'bank_transfer':
                    content = `
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Informasi Transfer Bank</h6>
                            <div class="bank-accounts">
                                <div class="bank-account mb-2">
                                    <strong>BCA:</strong> 1234567890 a.n. Fhinz Store
                                </div>
                                <div class="bank-account mb-2">
                                    <strong>BNI:</strong> 0987654321 a.n. Fhinz Store
                                </div>
                                <div class="bank-account">
                                    <strong>BRI:</strong> 1122334455 a.n. Fhinz Store
                                </div>
                            </div>
                            <small class="text-muted">
                                Transfer sesuai nominal exact, lalu upload bukti pembayaran setelah checkout.
                            </small>
                        </div>
                    `;
                    break;
                    
                case 'ewallet':
                    content = `
                        <div class="alert alert-info">
                            <h6><i class="fas fa-mobile-alt me-2"></i>E-Wallet Payment</h6>
                            <p>Anda akan diberikan QR Code atau nomor tujuan setelah checkout.</p>
                            <div class="ewallet-options">
                                <span class="badge bg-success me-2">GoPay</span>
                                <span class="badge bg-primary me-2">OVO</span>
                                <span class="badge bg-info me-2">DANA</span>
                                <span class="badge bg-warning">ShopeePay</span>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'qris':
                    content = `
                        <div class="alert alert-info">
                            <h6><i class="fas fa-qrcode me-2"></i>QRIS Payment</h6>
                            <p>Scan QR Code menggunakan aplikasi mobile banking atau e-wallet Anda.</p>
                            <small class="text-muted">
                                QR Code akan ditampilkan setelah Anda melakukan checkout.
                            </small>
                        </div>
                    `;
                    break;
                    
                case 'crypto':
                    content = `
                        <div class="alert alert-warning">
                            <h6><i class="fab fa-bitcoin me-2"></i>Cryptocurrency Payment</h6>
                            <p>Transfer ke wallet address yang akan diberikan setelah checkout.</p>
                            <div class="crypto-options">
                                <span class="badge bg-warning me-2">Bitcoin</span>
                                <span class="badge bg-secondary me-2">Ethereum</span>
                                <span class="badge bg-success">USDT</span>
                            </div>
                            <small class="text-muted">
                                Rate akan dikunci selama 15 menit setelah checkout.
                            </small>
                        </div>
                    `;
                    break;
            }

            if (content) {
                detailsContainer.innerHTML = content;
                detailsContainer.style.display = 'block';
            } else {
                detailsContainer.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                e.preventDefault();
                showAlert('error', 'Silakan pilih metode pembayaran!');
                return;
            }

            // Show loading state
            const checkoutBtn = document.getElementById('checkoutBtn');
            checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            checkoutBtn.disabled = true;
        });

        // Auto-select first payment method
        document.addEventListener('DOMContentLoaded', function() {
            const firstPaymentMethod = document.querySelector('input[name="payment_method"]');
            if (firstPaymentMethod) {
                firstPaymentMethod.checked = true;
                showPaymentDetails(firstPaymentMethod.value);
            }
        });
    </script>

    <style>
        .checkout-steps {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        .step.active,
        .step.completed {
            opacity: 1;
        }

        .step i {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }

        .step small {
            font-size: 0.75rem;
        }

        .step-divider {
            width: 30px;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
            margin: 0 1rem;
        }

        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .payment-method:hover {
            border-color: #007bff;
            background-color: rgba(0, 123, 255, 0.05);
        }

        .payment-method input:checked + .payment-label {
            border-color: #007bff;
            background-color: rgba(0, 123, 255, 0.1);
        }

        .payment-label {
            display: block;
            margin: 0;
            cursor: pointer;
            width: 100%;
        }

        .payment-info {
            display: flex;
            align-items: center;
        }

        .payment-icon {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #007bff;
        }

        .payment-details h6 {
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .payment-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }

        .order-item-info h6 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .security-badges i {
            font-size: 1.5rem;
        }

        .bank-account {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .checkout-steps {
                justify-content: center;
                margin-top: 1rem;
            }

            .payment-info {
                flex-direction: column;
                text-align: center;
            }

            .payment-icon {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }

            .order-summary {
                position: static !important;
                top: auto !important;
            }
        }
    </style>
</body>
</html>