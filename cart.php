<?php
require_once 'config/database.php';
requireLogin();

// Get cart items with product details
$cart_items = [];
$total_amount = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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
        // Handle error
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Fhinz Store</title>
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
                            <li class="breadcrumb-item active">Keranjang</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Keranjang Belanja</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="cart-section py-5">
        <div class="container">
            <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                        <h3>Keranjang Anda Kosong</h3>
                        <p class="text-muted mb-4">Sepertinya Anda belum menambahkan produk apapun ke keranjang belanja.</p>
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Cart Items -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Item dalam Keranjang (<?php echo count($cart_items); ?>)
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cart_items as $index => $item): ?>
                                <div class="cart-item" data-product-id="<?php echo $item['product']['id']; ?>">
                                    <div class="row align-items-center p-3 <?php echo $index < count($cart_items) - 1 ? 'border-bottom' : ''; ?>">
                                        <!-- Product Image -->
                                        <div class="col-md-2 col-4">
                                            <img src="<?php echo $item['product']['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product']['name']); ?>" 
                                                 class="img-fluid rounded">
                                        </div>
                                        
                                        <!-- Product Info -->
                                        <div class="col-md-4 col-8">
                                            <h6 class="mb-1">
                                                <a href="product-detail.php?id=<?php echo $item['product']['id']; ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo htmlspecialchars($item['product']['name']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?php echo $item['product']['duration_days']; ?> hari
                                            </small>
                                        </div>
                                        
                                        <!-- Quantity -->
                                        <div class="col-md-2 col-6 text-center">
                                            <div class="quantity-controls">
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        onclick="updateQuantity(<?php echo $item['product']['id']; ?>, -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="form-control form-control-sm text-center mx-2" 
                                                       style="width: 60px; display: inline-block;" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="10"
                                                       onchange="updateQuantity(<?php echo $item['product']['id']; ?>, this.value, true)">
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        onclick="updateQuantity(<?php echo $item['product']['id']; ?>, 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Price -->
                                        <div class="col-md-2 col-4 text-center">
                                            <div class="item-price">
                                                <div class="current-price fw-bold text-success">
                                                    <?php echo formatCurrency($item['subtotal']); ?>
                                                </div>
                                                <?php if ($item['quantity'] > 1): ?>
                                                <small class="text-muted">
                                                    <?php echo formatCurrency($item['product']['price']); ?> x <?php echo $item['quantity']; ?>
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Remove Button -->
                                        <div class="col-md-2 col-2 text-center">
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="removeFromCart(<?php echo $item['product']['id']; ?>)"
                                                    data-bs-toggle="tooltip" title="Hapus dari keranjang">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <button class="btn btn-outline-secondary" onclick="clearCart()">
                                            <i class="fas fa-trash me-2"></i>Kosongkan Keranjang
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <button class="btn btn-outline-primary" onclick="updateAllCart()">
                                            <i class="fas fa-sync me-2"></i>Update Keranjang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Ringkasan Pesanan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Subtotal (<?php echo count($cart_items); ?> item)</span>
                                    <span id="subtotal"><?php echo formatCurrency($total_amount); ?></span>
                                </div>
                                
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Biaya Admin</span>
                                    <span class="text-success">GRATIS</span>
                                </div>
                                
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Diskon</span>
                                    <span class="text-success">-<?php echo formatCurrency(0); ?></span>
                                </div>
                                
                                <hr>
                                
                                <div class="summary-total d-flex justify-content-between mb-3">
                                    <strong>Total</strong>
                                    <strong class="text-primary" id="total"><?php echo formatCurrency($total_amount); ?></strong>
                                </div>
                                
                                <!-- Promo Code -->
                                <div class="promo-section mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Kode promo" id="promoCode">
                                        <button class="btn btn-outline-secondary" type="button" onclick="applyPromo()">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="checkout.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-credit-card me-2"></i>
                                        Lanjut ke Checkout
                                    </a>
                                    <a href="products.php" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Lanjut Belanja
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Trust Badges -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-shield-alt me-2 text-success"></i>
                                    Jaminan Keamanan
                                </h6>
                                <div class="trust-features">
                                    <div class="trust-item d-flex align-items-center mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <small>Transaksi 100% aman</small>
                                    </div>
                                    <div class="trust-item d-flex align-items-center mb-2">
                                        <i class="fas fa-sync-alt text-primary me-2"></i>
                                        <small>Garansi penggantian</small>
                                    </div>
                                    <div class="trust-item d-flex align-items-center mb-2">
                                        <i class="fas fa-headset text-info me-2"></i>
                                        <small>Support 24/7</small>
                                    </div>
                                    <div class="trust-item d-flex align-items-center">
                                        <i class="fas fa-medal text-warning me-2"></i>
                                        <small>Kualitas terjamin</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recommended Products -->
    <?php if (!empty($cart_items)): ?>
    <section class="recommended-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-4">
                <h3>Mungkin Anda Juga Suka</h3>
                <p class="text-muted">Produk lain yang sering dibeli bersamaan</p>
            </div>
            
            <div class="row">
                <?php
                // Get recommended products (same category as cart items)
                try {
                    $category_ids = array_unique(array_column(array_column($cart_items, 'product'), 'category_id'));
                    if (!empty($category_ids)) {
                        $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
                        $cart_product_ids = array_column(array_column($cart_items, 'product'), 'id');
                        $cart_placeholders = str_repeat('?,', count($cart_product_ids) - 1) . '?';
                        
                        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                                              FROM products p 
                                              LEFT JOIN categories c ON p.category_id = c.id 
                                              WHERE p.category_id IN ($placeholders) 
                                              AND p.id NOT IN ($cart_placeholders)
                                              AND p.status = 'active' 
                                              ORDER BY RAND() 
                                              LIMIT 4");
                        $stmt->execute(array_merge($category_ids, $cart_product_ids));
                        $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($recommended_products as $product):
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card h-100">
                        <div class="product-image">
                            <img src="<?php echo $product['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                            <div class="product-overlay">
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                        class="btn btn-primary btn-sm">
                                    <i class="fas fa-cart-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            <h6 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <p class="product-price"><?php echo formatCurrency($product['price']); ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                        endforeach;
                    }
                } catch (PDOException $e) {
                    // Handle error silently
                }
                ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function updateQuantity(productId, change, absolute = false) {
            const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
            const quantityInput = cartItem.querySelector('input[type="number"]');
            
            let newQuantity;
            if (absolute) {
                newQuantity = parseInt(change);
            } else {
                newQuantity = parseInt(quantityInput.value) + parseInt(change);
            }
            
            if (newQuantity < 1) {
                if (confirm('Hapus produk ini dari keranjang?')) {
                    removeFromCart(productId);
                }
                return;
            }
            
            if (newQuantity > 10) {
                showAlert('warning', 'Maksimal 10 item per produk');
                return;
            }
            
            quantityInput.value = newQuantity;
            
            // Update cart via AJAX
            fetch('cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartDisplay();
                    updateCartCount();
                } else {
                    showAlert('error', data.message || 'Gagal mengupdate keranjang!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }
        
        function removeFromCart(productId) {
            if (!confirm('Yakin ingin menghapus produk ini dari keranjang?')) {
                return;
            }
            
            fetch('cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
                    cartItem.remove();
                    
                    updateCartCount();
                    
                    // Check if cart is empty
                    const remainingItems = document.querySelectorAll('.cart-item').length;
                    if (remainingItems === 0) {
                        location.reload();
                    } else {
                        updateCartDisplay();
                    }
                    
                    showAlert('success', 'Produk berhasil dihapus dari keranjang');
                } else {
                    showAlert('error', data.message || 'Gagal menghapus produk!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }
        
        function clearCart() {
            if (!confirm('Yakin ingin mengosongkan keranjang belanja?')) {
                return;
            }
            
            fetch('cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'clear'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showAlert('error', 'Gagal mengosongkan keranjang!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }
        
        function updateCartDisplay() {
            // Recalculate totals
            let subtotal = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const productId = item.dataset.productId;
                const quantity = parseInt(item.querySelector('input[type="number"]').value);
                const priceElement = item.querySelector('.current-price');
                
                // Get product price from backend or calculate
                // This is a simplified version - in production, get from server
                fetch(`get-product-price.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        const itemTotal = data.price * quantity;
                        priceElement.textContent = formatCurrency(itemTotal);
                        subtotal += itemTotal;
                        
                        // Update summary
                        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
                        document.getElementById('total').textContent = formatCurrency(subtotal);
                    });
            });
        }
        
        function updateCartCount() {
            const cartCount = document.querySelectorAll('.cart-item').length;
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
            }
        }
        
        function applyPromo() {
            const promoCode = document.getElementById('promoCode').value.trim();
            
            if (!promoCode) {
                showAlert('warning', 'Masukkan kode promo terlebih dahulu');
                return;
            }
            
            // Apply promo code via AJAX
            fetch('promo-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    code: promoCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', `Kode promo berhasil diterapkan! Diskon ${data.discount}%`);
                    updateCartDisplay();
                } else {
                    showAlert('error', data.message || 'Kode promo tidak valid');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }
        
        function addToCart(productId) {
            fetch('cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Produk berhasil ditambahkan ke keranjang!');
                    updateCartCount();
                } else {
                    showAlert('error', data.message || 'Gagal menambahkan produk!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
        }
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });
        });
    </script>
    
    <style>
        .cart-item {
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-controls .btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .empty-cart {
            padding: 3rem 2rem;
        }
        
        .summary-row {
            padding: 0.5rem 0;
        }
        
        .summary-total {
            font-size: 1.1rem;
            padding: 1rem 0 0.5rem 0;
        }
        
        .trust-item {
            padding: 0.25rem 0;
        }
        
        @media (max-width: 768px) {
            .quantity-controls {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .quantity-controls .form-control {
                width: 60px !important;
                margin: 0 !important;
            }
            
            .cart-item .row > div {
                margin-bottom: 1rem;
            }
            
            .cart-item .row > div:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</body>
</html>