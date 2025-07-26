<?php
require_once 'config/database.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Build query
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_clause = implode(' AND ', $where_conditions);

// Sort options
$sort_options = [
    'name' => 'p.name ASC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'newest' => 'p.created_at DESC'
];
$order_by = isset($sort_options[$sort_by]) ? $sort_options[$sort_by] : $sort_options['name'];

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM products p WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // Get products
    $sql = "SELECT p.*, c.name as category_name, c.icon as category_icon 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $where_clause 
            ORDER BY $order_by 
            LIMIT $items_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $products = [];
    $categories = [];
    $total_pages = 1;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                            <li class="breadcrumb-item active">Produk</li>
                        </ol>
                    </nav>
                    <h1 class="page-title">Produk Kami</h1>
                    <p class="page-subtitle">Temukan aplikasi premium terbaik dengan harga terjangkau</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section py-5">
        <div class="container">
            <!-- Filters and Search -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <form method="GET" action="" class="d-flex flex-wrap gap-3">
                        <!-- Search -->
                        <div class="search-box flex-grow-1">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari produk..." 
                                       value="<?php echo htmlspecialchars($search_query); ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Category Filter -->
                        <select name="category" class="form-select" style="width: auto;" onchange="this.form.submit()">
                            <option value="0">Semua Kategori</option>
                            <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Sort -->
                        <select name="sort" class="form-select" style="width: auto;" onchange="this.form.submit()">
                            <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                            <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        </select>
                        
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>
                <div class="col-lg-4 text-end">
                    <p class="results-count mb-0">
                        Menampilkan <?php echo count($products); ?> dari <?php echo $total_items; ?> produk
                    </p>
                </div>
            </div>
            
            <!-- Products Grid -->
            <?php if (empty($products)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>Produk tidak ditemukan</h4>
                        <p class="text-muted">Silakan coba dengan kata kunci atau filter yang berbeda.</p>
                        <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach($products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card h-100">
                        <div class="product-image">
                            <img src="<?php echo $product['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                            <div class="product-overlay">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                        class="btn btn-success btn-sm">
                                    <i class="fas fa-cart-plus me-1"></i>Beli
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="product-category">
                                    <i class="<?php echo $product['category_icon']; ?> me-1"></i>
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                                <span class="badge bg-primary"><?php echo $product['duration_days']; ?> hari</span>
                            </div>
                            <h6 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price"><?php echo formatCurrency($product['price']); ?></span>
                                <div class="product-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <small class="text-muted ms-1">(4.9)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="row">
                <div class="col-12">
                    <nav aria-label="Products pagination">
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
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function addToCart(productId) {
            <?php if (isLoggedIn()): ?>
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
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;
                    
                    // Show success message
                    showAlert('success', 'Produk berhasil ditambahkan ke keranjang!');
                } else {
                    showAlert('error', data.message || 'Gagal menambahkan produk ke keranjang!');
                }
            })
            .catch(error => {
                showAlert('error', 'Terjadi kesalahan sistem!');
            });
            <?php else: ?>
            window.location.href = 'login.php';
            <?php endif; ?>
        }
        
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const alertHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', alertHTML);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert:last-of-type');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }
    </script>
</body>
</html>