<?php
require_once 'config/database.php';

// Get some statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $total_products = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
    $total_orders = $stmt->fetchColumn();
} catch(PDOException $e) {
    $total_products = 20;
    $total_customers = 500;
    $total_orders = 1000;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Beranda</a></li>
                            <li class="breadcrumb-item active text-white">Tentang Kami</li>
                        </ol>
                    </nav>
                    <h1 class="display-4 mb-3">Tentang Fhinz Store</h1>
                    <p class="lead">Platform terpercaya untuk aplikasi premium dengan harga terjangkau</p>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-store fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2 class="mb-4">Siapa Kami?</h2>
                    <p class="lead">Fhinz Store adalah platform e-commerce yang mengkhususkan diri dalam penjualan aplikasi premium dengan harga yang terjangkau untuk semua kalangan.</p>
                    
                    <p>Didirikan pada tahun 2023, kami memahami bahwa tidak semua orang mampu membeli aplikasi premium dengan harga retail yang tinggi. Oleh karena itu, kami hadir sebagai solusi untuk memberikan akses ke berbagai aplikasi premium favorit Anda dengan harga yang jauh lebih ekonomis.</p>
                    
                    <p>Tim kami terdiri dari profesional muda yang berpengalaman dalam bidang teknologi dan e-commerce, dengan komitmen untuk memberikan pelayanan terbaik kepada setiap customer.</p>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="about-image">
                        <img src="assets/images/about-us.jpg" alt="About Us" class="img-fluid rounded shadow">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-vision py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-5">
                            <div class="icon-box mb-4">
                                <i class="fas fa-eye fa-3x text-primary"></i>
                            </div>
                            <h3>Visi Kami</h3>
                            <p class="lead">Menjadi platform terdepan dalam penyediaan aplikasi premium yang terjangkau dan terpercaya di Indonesia.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-5">
                            <div class="icon-box mb-4">
                                <i class="fas fa-bullseye fa-3x text-success"></i>
                            </div>
                            <h3>Misi Kami</h3>
                            <p class="lead">Memberikan akses mudah dan terjangkau ke aplikasi premium berkualitas tinggi untuk meningkatkan produktivitas dan kreativitas pengguna.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="values-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Nilai-Nilai Kami</h2>
                <p class="lead">Prinsip yang menjadi fondasi dalam setiap layanan kami</p>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        </div>
                        <h5>Terpercaya</h5>
                        <p>Kami mengutamakan kepercayaan customer dengan memberikan produk original dan pelayanan yang transparan.</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-dollar-sign fa-3x text-success"></i>
                        </div>
                        <h5>Terjangkau</h5>
                        <p>Harga yang kompetitif dan terjangkau tanpa mengurangi kualitas produk dan layanan.</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-clock fa-3x text-warning"></i>
                        </div>
                        <h5>Cepat</h5>
                        <p>Pengiriman akun premium yang cepat dan responsif dalam menangani setiap pertanyaan customer.</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-heart fa-3x text-danger"></i>
                        </div>
                        <h5>Peduli</h5>
                        <p>Kami peduli dengan kebutuhan dan kepuasan setiap customer dengan memberikan support 24/7.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="stats-section py-5 bg-primary text-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Pencapaian Kami</h2>
                <p class="lead">Angka yang membuktikan kepercayaan customer kepada kami</p>
            </div>
            
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <i class="fas fa-boxes fa-3x mb-3"></i>
                        <h3 class="display-4"><?php echo $total_products; ?>+</h3>
                        <p class="lead">Produk Tersedia</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h3 class="display-4"><?php echo $total_customers; ?>+</h3>
                        <p class="lead">Customer Puas</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <h3 class="display-4"><?php echo $total_orders; ?>+</h3>
                        <p class="lead">Transaksi Sukses</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <i class="fas fa-star fa-3x mb-3"></i>
                        <h3 class="display-4">4.9</h3>
                        <p class="lead">Rating Customer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-us py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Mengapa Memilih Fhinz Store?</h2>
                <p class="lead">Keunggulan yang membuat kami berbeda dari yang lain</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-certificate fa-2x text-primary"></i>
                        </div>
                        <h5>Produk Original 100%</h5>
                        <p>Semua aplikasi premium yang kami jual dijamin original dan legal, bukan hasil crack atau bajakan.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-sync-alt fa-2x text-success"></i>
                        </div>
                        <h5>Garansi Penggantian</h5>
                        <p>Jika ada masalah dengan akun yang Anda beli, kami berikan garansi penggantian gratis.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-headset fa-2x text-info"></i>
                        </div>
                        <h5>Support 24/7</h5>
                        <p>Tim customer service kami siap membantu Anda kapan saja melalui WhatsApp, email, atau live chat.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bolt fa-2x text-warning"></i>
                        </div>
                        <h5>Pengiriman Instan</h5>
                        <p>Akun premium langsung dikirim ke email Anda maksimal 2 jam setelah pembayaran dikonfirmasi.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-lock fa-2x text-danger"></i>
                        </div>
                        <h5>Transaksi Aman</h5>
                        <p>Website kami menggunakan SSL encryption untuk melindungi data pribadi dan transaksi Anda.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card p-4 h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-tags fa-2x text-secondary"></i>
                        </div>
                        <h5>Harga Kompetitif</h5>
                        <p>Kami selalu memberikan harga terbaik di pasaran dengan kualitas produk yang tidak diragukan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Tim Kami</h2>
                <p class="lead">Orang-orang hebat di balik kesuksesan Fhinz Store</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="assets/images/team-1.jpg" alt="CEO" class="img-fluid rounded-circle">
                        </div>
                        <h5>Mohammad Rizky Putra</h5>
                        <p class="text-muted">CEO & Founder</p>
                        <div class="social-links">
                            <a href="#" class="text-primary me-2"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="assets/images/team-2.jpg" alt="CTO" class="img-fluid rounded-circle">
                        </div>
                        <h5>Sarah Putri</h5>
                        <p class="text-muted">Chief Technology Officer</p>
                        <div class="social-links">
                            <a href="#" class="text-primary me-2"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="assets/images/team-3.jpg" alt="Customer Service" class="img-fluid rounded-circle">
                        </div>
                        <h5>Andi Pratama</h5>
                        <p class="text-muted">Customer Service Manager</p>
                        <div class="social-links">
                            <a href="#" class="text-primary me-2"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-info me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-3">Siap Bergabung dengan Ribuan Customer Kami?</h2>
            <p class="lead mb-4">Dapatkan akses ke aplikasi premium favorit Anda dengan harga terbaik!</p>
            <a href="products.php" class="btn btn-light btn-lg me-3">
                <i class="fas fa-shopping-cart me-2"></i>Mulai Belanja
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-envelope me-2"></i>Hubungi Kami
            </a>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <style>
        .value-card, .feature-card, .team-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
        }
        
        .value-card:hover, .feature-card:hover, .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .team-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        
        .social-links a {
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }
        
        .social-links a:hover {
            opacity: 0.7;
        }
        
        .stat-item {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>