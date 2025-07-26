<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success_message = '';
$error_message = '';

// Get admin info
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admin = [];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        if (empty($full_name) || empty($email)) {
            $error_message = 'Nama lengkap dan email harus diisi!';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $_SESSION['user_id']]);
                
                // Update session
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                
                $success_message = 'Profil berhasil diperbarui!';
                
                // Refresh admin data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                $error_message = 'Gagal memperbarui profil!';
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Semua field password harus diisi!';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Password baru dan konfirmasi tidak cocok!';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'Password minimal 6 karakter!';
        } else {
            try {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!password_verify($current_password, $user['password'])) {
                    $error_message = 'Password saat ini salah!';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    $success_message = 'Password berhasil diubah!';
                }
            } catch (PDOException $e) {
                $error_message = 'Gagal mengubah password!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="admin-content">
            <?php include 'includes/topnav.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">Profil Admin</h1>
                        <p class="text-muted">Kelola informasi profil Anda</p>
                    </div>
                </div>

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

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Profil</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                                        <small class="text-muted">Username tidak dapat diubah</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($admin['role']); ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Bergabung Sejak</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('d F Y', strtotime($admin['created_at'])); ?>" readonly>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Perbarui Profil
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Ubah Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Password Saat Ini *</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Password Baru *</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password Baru *</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Foto Profil</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php
                                $current_avatar = '../assets/images/default-avatar.jpg';
                                if (isset($admin['avatar']) && file_exists($admin['avatar'])) {
                                    $current_avatar = $admin['avatar'];
                                }
                                ?>
                                <img src="<?php echo $current_avatar; ?>" 
                                     alt="Profile" class="rounded-circle mb-3" width="150" height="150" id="avatarPreview">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="avatar" accept="image/*" id="avatarInput">
                                        <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                    </div>
                                    <button type="submit" name="upload_avatar" class="btn btn-outline-primary">
                                        <i class="fas fa-upload me-2"></i>Ubah Foto
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Akun</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <strong>Total Login:</strong> 
                                        <span class="text-muted">Coming soon</span>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Terakhir Login:</strong> 
                                        <span class="text-muted">Coming soon</span>
                                    </li>
                                    <li class="mb-2">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-success">Aktif</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Avatar preview
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle avatar upload
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (e.submitter && e.submitter.name === 'upload_avatar') {
                    const fileInput = document.getElementById('avatarInput');
                    if (fileInput.files.length === 0) {
                        e.preventDefault();
                        alert('Silakan pilih file foto terlebih dahulu!');
                    }
                }
            });
        });
    </script>
</body>
</html>
