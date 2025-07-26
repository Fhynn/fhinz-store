<?php
require_once 'config/database.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid!';
    } else {
        // In real application, you would send email here
        // For now, we'll just show success message
        $success_message = 'Pesan Anda berhasil dikirim! Kami akan membalas dalam 24 jam.';
        
        // Clear form data
        $_POST = array();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - Fhinz Store</title>
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
                            <li class="breadcrumb-item active text-white">Kontak</li>
                        </ol>
                    </nav>
                    <h1 class="display-4 mb-3">Hubungi Kami</h1>
                    <p class="lead">Kami siap membantu Anda 24/7. Jangan ragu untuk menghubungi kami!</p>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-envelope fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Methods -->
    <section class="contact-methods py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="contact-card text-center p-4 h-100">
                        <div class="contact-icon mb-3">
                            <i class="fab fa-whatsapp fa-3x text-success"></i>
                        </div>
                        <h5>WhatsApp</h5>
                        <p class="text-muted">Chat langsung dengan tim support kami</p>
                        <div class="contact-info">
                            <strong>+62 896-3556-0147</strong>
                        </div>
                        <a href="https://wa.me/6289635560147" class="btn btn-success mt-3" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Chat Sekarang
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="contact-card text-center p-4 h-100">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-envelope fa-3x text-primary"></i>
                        </div>
                        <h5>Email</h5>
                        <p class="text-muted">Kirim email untuk pertanyaan detail</p>
                        <div class="contact-info">
                            <strong>support@fhinzstore.com</strong>
                        </div>
                        <a href="mailto:support@fhinzstore.com" class="btn btn-primary mt-3">
                            <i class="fas fa-envelope me-2"></i>Kirim Email
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="contact-card text-center p-4 h-100">
                        <div class="contact-icon mb-3">
                            <i class="fas fa-phone fa-3x text-info"></i>
                        </div>
                        <h5>Telepon</h5>
                        <p class="text-muted">Hubungi kami via telepon</p>
                        <div class="contact-info">
                            <strong>6289635560147</strong>
                        </div>
                        <a href="tel:+6289635560147" class="btn btn-info mt-3">
                            <i class="fas fa-phone me-2"></i>Telepon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Info -->
    <section class="contact-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-paper-plane me-2"></i>
                                Kirim Pesan
                            </h5>
                        </div>
                        <div class="card-body p-4">
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

                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="Masukkan nama Anda" required
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Masukkan email Anda" required
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subjek <span class="text-danger">*</span></label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Pilih subjek pesan</option>
                                        <option value="Pertanyaan Produk" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Pertanyaan Produk') ? 'selected' : ''; ?>>Pertanyaan Produk</option>
                                        <option value="Bantuan Pembayaran" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Bantuan Pembayaran') ? 'selected' : ''; ?>>Bantuan Pembayaran</option>
                                        <option value="Masalah Akun" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Masalah Akun') ? 'selected' : ''; ?>>Masalah Akun</option>
                                        <option value="Klaim Garansi" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Klaim Garansi') ? 'selected' : ''; ?>>Klaim Garansi</option>
                                        <option value="Kerjasama" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Kerjasama') ? 'selected' : ''; ?>>Kerjasama</option>
                                        <option value="Lainnya" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Pesan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" 
                                              placeholder="Tulis pesan Anda di sini..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4">
                    <!-- Business Hours -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Jam Operasional
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="business-hours">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Senin - Jumat</span>
                                    <span>08:00 - 22:00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sabtu</span>
                                    <span>08:00 - 20:00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Minggu</span>
                                    <span>10:00 - 18:00</span>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle me-1"></i>Online Sekarang
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Quick Links -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-question-circle me-2"></i>
                                FAQ Populer
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="faq-links">
                                <a href="#" class="d-block text-decoration-none mb-2">
                                    <i class="fas fa-chevron-right me-2"></i>Bagaimana cara pembayaran?
                                </a>
                                <a href="#" class="d-block text-decoration-none mb-2">
                                    <i class="fas fa-chevron-right me-2"></i>Berapa lama akun dikirim?
                                </a>
                                <a href="#" class="d-block text-decoration-none mb-2">
                                    <i class="fas fa-chevron-right me-2"></i>Apa ada garansi?
                                </a>
                                <a href="#" class="d-block text-decoration-none mb-2">
                                    <i class="fas fa-chevron-right me-2"></i>Cara klaim garansi?
                                </a>
                                <a href="#" class="d-block text-decoration-none">
                                    <i class="fas fa-chevron-right me-2"></i>Metode pembayaran apa saja?
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-share-alt me-2"></i>
                                Ikuti Kami
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="social-media">
                                <a href="#" class="btn btn-outline-primary w-100 mb-2" target="_blank">
                                <i class="fab fa-facebook-f me-2"></i>Facebook
                                </a>

                                <a href="https://twitter.com/Rii_zehpryn" class="btn btn-outline-info w-100 mb-2" target="_blank">
                                <i class="fab fa-twitter me-2"></i>Twitter
                                </a>

                                <a href="https://www.instagram.com/fhinz_anxiety/" class="btn btn-outline-danger w-100 mb-2" target="_blank">
                                <i class="fab fa-instagram me-2"></i>Instagram
                                </a>

                                <a href="https://www.tiktok.com/@fhinzxty" class="btn btn-outline-dark w-100" target="_blank">
                                <i class="fab fa-tiktok me-2"></i>TikTok
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="mb-3">Lokasi Kami</h2>
                <p class="lead">Kunjungi kantor kami untuk konsultasi langsung</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15955.123456789!2d101.4412345678!3d0.5343210987!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sPekanbaru%2C%20Riau!5e0!3m2!1sen!2sid!4v1234567890"
                                width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Alamat Kantor
                            </h6>
                        </div>
                        <div class="card-body">
                            <address class="mb-3">
                                <strong>Fhinz Store</strong><br>
                                Jl. Sudirman No. 123<br>
                                Pekanbaru, Riau 28117<br>
                                Indonesia
                            </address>
                            
                            <div class="contact-details">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <span>021-1234-5678</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <span>support@fhinzstore.com</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-globe text-primary me-2"></i>
                                    <span>www.fhinzstore.com</span>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="https://goo.gl/maps/xyz" class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="fas fa-directions me-2"></i>Petunjuk Arah
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Response Time -->
    <section class="response-time py-5 bg-primary text-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="response-stat">
                        <i class="fas fa-reply fa-3x mb-3"></i>
                        <h4>< 2 Jam</h4>
                        <p>Rata-rata Waktu Respon</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="response-stat">
                        <i class="fas fa-headset fa-3x mb-3"></i>
                        <h4>24/7</h4>
                        <p>Customer Support</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="response-stat">
                        <i class="fas fa-smile fa-3x mb-3"></i>
                        <h4>98%</h4>
                        <p>Tingkat Kepuasan</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="response-stat">
                        <i class="fas fa-language fa-3x mb-3"></i>
                        <h4>Multi</h4>
                        <p>Bahasa Support</p>
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
    
    <style>
        .contact-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .contact-icon {
            margin-bottom: 1rem;
        }
        
        .map-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .business-hours {
            font-size: 0.9rem;
        }
        
        .faq-links a {
            color: #495057;
            transition: color 0.3s ease;
        }
        
        .faq-links a:hover {
            color: #007bff;
        }
        
        .social-media .btn {
            transition: all 0.3s ease;
        }
        
        .social-media .btn:hover {
            transform: translateY(-2px);
        }
        
        .response-stat {
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
        
        @media (max-width: 768px) {
            .map-container iframe {
                height: 300px;
            }
        }
    </style>
</body>
</html>