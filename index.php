<?php
require_once 'config/database.php';

// Fetch categories and featured products
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.status = 'active' 
                        ORDER BY p.created_at DESC LIMIT 8");
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = [];
    $featured_products = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fhinz Store - Jual Beli Aplikasi Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Selamat Datang di <span class="text-primary">Fhinz Store</span></h1>
                        <p class="hero-description">Platform terpercaya untuk membeli aplikasi premium dengan harga terjangkau. Dapatkan akses ke berbagai aplikasi favorit Anda!</p>
                        <div class="hero-buttons">
                            <a href="products.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-shopping-cart me-2"></i>Lihat Produk
                            </a>
                            <a href="#categories" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-list me-2"></i>Kategori
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="assets/images/hero-illustration.jpg" alt="Hero" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="stat-number">20+</h3>
                        <p class="stat-label">Aplikasi Premium</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-number">500+</h3>
                        <p class="stat-label">Customer Puas</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="stat-number">24/7</h3>
                        <p class="stat-label">Support</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="stat-number">100%</h3>
                        <p class="stat-label">Terpercaya</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="categories-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Kategori Produk</h2>
                <p class="section-subtitle">Pilih kategori aplikasi premium yang Anda butuhkan</p>
            </div>
            <div class="row">
                <?php foreach($categories as $category): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="<?php echo $category['icon']; ?>"></i>
                        </div>
                        <h5 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                            Lihat Produk <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Produk Unggulan</h2>
                <p class="section-subtitle">Aplikasi premium terpopuler dengan harga terbaik</p>
            </div>
            <div class="row">
                <?php foreach($featured_products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $product['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                            <div class="product-overlay">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-2"></i>Detail
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            <h6 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <p class="product-price"><?php echo formatCurrency($product['price']); ?></p>
                            <div class="product-duration">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo $product['duration_days']; ?> hari
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary btn-lg">
                    Lihat Semua Produk <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Testimoni Customer</h2>
                <p class="section-subtitle">Apa kata mereka tentang Fhinz Store</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Pelayanan sangat memuaskan, aplikasi yang dibeli berfungsi dengan baik dan harga sangat terjangkau!"</p>
                        <div class="testimonial-author">
                            <h6>Andi Pratama</h6>
                            <span>Customer</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Proses pembelian mudah dan cepat. Support nya juga responsif. Recommended banget!"</p>
                        <div class="testimonial-author">
                            <h6>Sari Dewi</h6>
                            <span>Customer</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Sudah berlangganan Netflix dan Spotify dari sini. Harga murah, kualitas bagus!"</p>
                        <div class="testimonial-author">
                            <h6>Rudi Hartono</h6>
                            <span>Customer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="cta-title">Siap untuk mulai berlangganan aplikasi premium?</h3>
                    <p class="cta-description">Dapatkan akses ke berbagai aplikasi premium dengan harga terjangkau sekarang juga!</p>
                </div>
                <div class="col-lg-4 text-end">
                    <a href="products.php" class="btn btn-light btn-lg">
                        <i class="fas fa-rocket me-2"></i>Mulai Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>