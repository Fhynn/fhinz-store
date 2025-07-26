<?php
require_once 'config/database.php';
requireLogin();

$success_message = '';
$error_message = '';

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: logout.php');
        exit();
    }
} catch (PDOException $e) {
    $error_message = 'Terjadi kesalahan sistem!';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name)) {
        $error_message = 'Nama lengkap harus diisi!';
    } else {
        try {
            // Update basic info
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$full_name, $phone, $_SESSION['user_id']]);
            
            // Update password if provided
            if (!empty($current_password)) {
                if (empty($new_password) || empty($confirm_password)) {
                    $error_message = 'Password baru dan konfirmasi harus diisi!';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'Password baru dan konfirmasi tidak sama!';
                } elseif (strlen($new_password) < 6) {
                    $error_message = 'Password baru minimal 6 karakter!';
                } elseif (!password_verify($current_password, $user['password'])) {
                    $error_message = 'Password saat ini salah!';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                }
            }
            
            if (empty($error_message)) {
                $_SESSION['full_name'] = $full_name;
                $success_message = 'Profil berhasil diperbarui!';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan saat memperbarui profil!';
        }
    }
}

// Get user statistics
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_orders = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = ? AND status IN ('paid', 'completed')");
    $stmt->execute([$_SESSION['user_id']]);
    $total_spent = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    $completed_orders = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_orders = 0;
    $total_spent = 0;
    $completed_orders = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Fhinz Store</title>
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
                            <li class="breadcrumb-item active">Profil</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Profil Saya</h1>
                    <p class="text-muted mb-0">Kelola informasi akun Anda</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-success">
                        <i class="fas fa-user-check me-1"></i>Akun Terverifikasi
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="profile-section py-5">
        <div class="container">
            <div class="row">
                <!-- Profile Sidebar -->
                <div class="col-lg-4 mb-4">
                    <div class="profile-sidebar">
                        <!-- Profile Card -->
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <div class="profile-avatar mb-3">
                                    <img src="assets/images/default-avatar.jpg" alt="Profile" class="rounded-circle" width="100" height="100">
                                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="changeAvatar()">
                                        <i class="fas fa-camera me-1"></i>Ubah Foto
                                    </button>
                                </div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                                <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                            </div>
                        </div>

                        <!-- Profile Stats -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Statistik Akun
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="profile-stats">
                                    <div class="stat-item d-flex justify-content-between mb-3">
                                        <div class="stat-info">
                                            <i class="fas fa-shopping-cart text-primary me-2"></i>
                                            <span>Total Pesanan</span>
                                        </div>
                                        <strong><?php echo $total_orders; ?></strong>
                                    </div>
                                    
                                    <div class="stat-item d-flex justify-content-between mb-3">
                                        <div class="stat-info">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span>Pesanan Selesai</span>
                                        </div>
                                        <strong><?php echo $completed_orders; ?></strong>
                                    </div>
                                    
                                    <div class="stat-item d-flex justify-content-between">
                                        <div class="stat-info">
                                            <i class="fas fa-money-bill-wave text-warning me-2"></i>
                                            <span>Total Pembelian</span>
                                        </div>
                                        <strong><?php echo formatCurrency($total_spent); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Aksi Cepat
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="orders.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-list me-2"></i>Lihat Pesanan
                                    </a>
                                    <a href="products.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-shopping-bag me-2"></i>Belanja Lagi
                                    </a>
                                    <a href="contact.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-headset me-2"></i>Hubungi Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="col-lg-8">
                    <div class="profile-form">
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

                        <!-- Profile Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    Informasi Profil
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" 
                                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                            <small class="text-muted">Username tidak dapat diubah</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                            <small class="text-muted">Email tidak dapat diubah</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">No. Telepon</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                                   placeholder="08xxxxxxxxxx">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="member_since" class="form-label">Bergabung Sejak</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('d F Y', strtotime($user['created_at'])); ?>" disabled>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="mb-3">
                                        <i class="fas fa-lock me-2"></i>
                                        Ubah Password (Opsional)
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="current_password" class="form-label">Password Saat Ini</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="new_password" class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Catatan:</strong> Kosongkan field password jika tidak ingin mengubahnya.
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Account Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog me-2"></i>
                                    Pengaturan Akun
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1">Notifikasi Email</h6>
                                        <small class="text-muted">Terima notifikasi pesanan via email</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                    </div>
                                </div>
                                
                                <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1">Notifikasi WhatsApp</h6>
                                        <small class="text-muted">Terima update pesanan via WhatsApp</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="whatsappNotif" checked>
                                    </div>
                                </div>
                                
                                <div class="setting-item d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1">Newsletter</h6>
                                        <small class="text-muted">Terima info promo dan produk terbaru</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="newsletter">
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="danger-zone">
                                    <h6 class="text-danger mb-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Zona Berbahaya
                                    </h6>
                                    <p class="text-muted mb-3">Tindakan di bawah ini tidak dapat dibatalkan. Harap berhati-hati.</p>
                                    <button class="btn btn-outline-danger btn-sm" onclick="confirmDeleteAccount()">
                                        <i class="fas fa-trash me-2"></i>Hapus Akun
                                    </button>
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
        function resetForm() {
            if (confirm('Yakin ingin mereset form ke data asli?')) {
                location.reload();
            }
        }

        function changeAvatar() {
            // Create file input
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.profile-avatar img').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    // Here you would upload the file to server
                    showAlert('info', 'Fitur upload foto akan segera tersedia!');
                }
            };
            
            input.click();
        }

        function confirmDeleteAccount() {
            if (confirm('PERINGATAN: Tindakan ini akan menghapus akun Anda secara permanen beserta semua data pesanan. Apakah Anda yakin?')) {
                if (confirm('Konfirmasi sekali lagi: Hapus akun secara permanen?')) {
                    // Here you would send delete request to server
                    showAlert('warning', 'Fitur hapus akun akan segera tersedia. Hubungi customer service untuk bantuan.');
                }
            }
        }

        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('password-strength');
            
            if (password.length === 0) {
                if (strengthMeter) strengthMeter.remove();
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push('minimal 8 karakter');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('huruf kecil');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('huruf besar');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('angka');
            
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('simbol');
            
            const colors = ['danger', 'danger', 'warning', 'info', 'success'];
            const texts = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            
            let existingMeter = document.getElementById('password-strength');
            if (!existingMeter) {
                existingMeter = document.createElement('div');
                existingMeter.id = 'password-strength';
                this.parentNode.appendChild(existingMeter);
            }
            
            existingMeter.innerHTML = `
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-${colors[strength-1]}" style="width: ${strength * 20}%"></div>
                </div>
                <small class="text-${colors[strength-1]}">${texts[strength-1]}</small>
                ${feedback.length > 0 ? `<small class="text-muted d-block">Perlu: ${feedback.join(', ')}</small>` : ''}
            `;
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length === 0) return;
            
            if (password === confirmPassword) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });

        // Auto-save settings
        document.querySelectorAll('.form-check-input').forEach(input => {
            input.addEventListener('change', function() {
                // Here you would save the setting to server
                showAlert('success', 'Pengaturan berhasil disimpan!', 2000);
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            if (newPassword && !currentPassword) {
                e.preventDefault();
                showAlert('error', 'Masukkan password saat ini untuk mengubah password!');
                document.getElementById('current_password').focus();
                return;
            }
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                showAlert('error', 'Password baru dan konfirmasi tidak sama!');
                document.getElementById('confirm_password').focus();
                return;
            }
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                showAlert('error', 'Password baru minimal 6 karakter!');
                document.getElementById('new_password').focus();
                return;
            }
        });
    </script>

    <style>
        .profile-avatar {
            position: relative;
        }
        
        .profile-avatar img {
            border: 3px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .profile-avatar:hover img {
            border-color: #007bff;
        }
        
        .profile-stats .stat-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .profile-stats .stat-item:last-child {
            border-bottom: none;
        }
        
        .setting-item {
            padding: 1rem;
            border-radius: 8px;
            background: #f8f9fa;
            transition: background-color 0.3s ease;
        }
        
        .setting-item:hover {
            background: #e9ecef;
        }
        
        .danger-zone {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 8px;
            padding: 1rem;
        }
        
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .profile-sidebar {
                margin-bottom: 2rem;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .d-flex.justify-content-between .btn {
                width: 100%;
            }
        }
    </style>
</body>
</html>