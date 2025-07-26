<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success_message = '';
$error_message = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $site_name = trim($_POST['site_name']);
        $site_description = trim($_POST['site_description']);
        $contact_email = trim($_POST['contact_email']);
        $contact_phone = trim($_POST['contact_phone']);
        $address = trim($_POST['address']);
        $facebook = trim($_POST['facebook']);
        $instagram = trim($_POST['instagram']);
        $whatsapp = trim($_POST['whatsapp']);
        $theme_color = trim($_POST['theme_color']);
        $language = trim($_POST['language']);
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        $enable_registration = isset($_POST['enable_registration']) ? 1 : 0;
        
        if (empty($site_name) || empty($contact_email)) {
            $error_message = 'Nama website dan email kontak harus diisi!';
        } else {
            // Save settings to database
            try {
                $stmt = $pdo->prepare("UPDATE settings SET 
                    site_name = ?, site_description = ?, contact_email = ?, 
                    contact_phone = ?, address = ?, facebook = ?, 
                    instagram = ?, whatsapp = ?, theme_color = ?, 
                    language = ?, maintenance_mode = ?, enable_registration = ?,
                    updated_at = NOW() WHERE id = 1");
                
                $stmt->execute([
                    $site_name, $site_description, $contact_email,
                    $contact_phone, $address, $facebook,
                    $instagram, $whatsapp, $theme_color,
                    $language, $maintenance_mode, $enable_registration
                ]);
                
                $success_message = 'Pengaturan berhasil diperbarui!';
            } catch (PDOException $e) {
                $error_message = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
            }
        }
    }
    
    // Handle security update
    if (isset($_POST['update_security'])) {
        $admin_password = trim($_POST['admin_password']);
        $confirm_password = trim($_POST['confirm_password']);
        $session_timeout = intval($_POST['session_timeout']);
        
        if (!empty($admin_password)) {
            if ($admin_password !== $confirm_password) {
                $error_message = 'Password dan konfirmasi password tidak cocok!';
            } else {
                try {
                    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE settings SET admin_password_hash = ?, session_timeout = ? WHERE id = 1");
                    $stmt->execute([$hashed_password, $session_timeout]);
                    $success_message = 'Pengaturan keamanan berhasil diperbarui!';
                } catch (PDOException $e) {
                    $error_message = 'Gagal menyimpan pengaturan keamanan: ' . $e->getMessage();
                }
            }
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE settings SET session_timeout = ? WHERE id = 1");
                $stmt->execute([$session_timeout]);
                $success_message = 'Pengaturan keamanan berhasil diperbarui!';
            } catch (PDOException $e) {
                $error_message = 'Gagal menyimpan pengaturan keamanan: ' . $e->getMessage();
            }
        }
    }
    
    // Handle logo upload
    if (isset($_POST['upload_logo']) && isset($_FILES['site_logo'])) {
        $uploadDir = '../uploads/settings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['site_logo'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE settings SET site_logo = ? WHERE id = 1");
                        $stmt->execute([$filepath]);
                        $success_message = 'Logo berhasil diperbarui!';
                    } catch (PDOException $e) {
                        $error_message = 'Gagal menyimpan logo: ' . $e->getMessage();
                    }
                } else {
                    $error_message = 'Gagal mengunggah logo!';
                }
            } else {
                $error_message = 'Format file tidak valid atau ukuran file terlalu besar!';
            }
        }
    }
}

// Get current settings
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // Create default settings if not exists
        $pdo->exec("INSERT INTO settings (id, site_name, site_description, contact_email, 
                    contact_phone, address, facebook, instagram, whatsapp, theme_color, 
                    language, maintenance_mode, enable_registration, session_timeout) 
                    VALUES (1, 'Fhinz Store', 'Toko aplikasi premium terpercaya', 
                    'admin@fhinzstore.com', '+62 812-3456-7890', 'Jakarta, Indonesia', 
                    'https://facebook.com/fhinzstore', 'https://instagram.com/fhinzstore', 
                    '+62 812-3456-7890', 'blue', 'id', 0, 1, 30)");
        $settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Fallback to default settings
    $settings = [
        'site_name' => 'Fhinz Store',
        'site_description' => 'Toko aplikasi premium terpercaya dengan berbagai pilihan aplikasi berkualitas',
        'contact_email' => 'admin@fhinzstore.com',
        'contact_phone' => '+62 812-3456-7890',
        'address' => 'Jakarta, Indonesia',
        'facebook' => 'https://facebook.com/fhinzstore',
        'instagram' => 'https://instagram.com/fhinzstore',
        'whatsapp' => '+62 812-3456-7890',
        'theme_color' => 'blue',
        'language' => 'id',
        'maintenance_mode' => 0,
        'enable_registration' => 1,
        'session_timeout' => 30,
        'site_logo' => '../assets/images/default-logo.png'
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin Fhinz Store</title>
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
                        <h1 class="h3 mb-0">Pengaturan Website</h1>
                        <p class="text-muted">Kelola pengaturan umum website</p>
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
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Website</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Website *</label>
                                        <input type="text" class="form-control" name="site_name" 
                                               value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi Website</label>
                                        <textarea class="form-control" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Kontak *</label>
                                        <input type="email" class="form-control" name="contact_email" 
                                               value="<?php echo htmlspecialchars($settings['contact_email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="tel" class="form-control" name="contact_phone" 
                                               value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($settings['address']); ?></textarea>
                                    </div>
                                    
                                    <h6 class="mt-4 mb-3">Sosial Media</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Facebook URL</label>
                                        <input type="url" class="form-control" name="facebook" 
                                               value="<?php echo htmlspecialchars($settings['facebook']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Instagram URL</label>
                                        <input type="url" class="form-control" name="instagram" 
                                               value="<?php echo htmlspecialchars($settings['instagram']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">WhatsApp</label>
                                        <input type="text" class="form-control" name="whatsapp" 
                                               value="<?php echo htmlspecialchars($settings['whatsapp']); ?>">
                                    </div>
                                    
                                    <h6 class="mt-4 mb-3">Pengaturan Lanjutan</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tema Warna</label>
                                        <select class="form-select" name="theme_color">
                                            <option value="blue" <?php echo $settings['theme_color'] == 'blue' ? 'selected' : ''; ?>>Biru</option>
                                            <option value="red" <?php echo $settings['theme_color'] == 'red' ? 'selected' : ''; ?>>Merah</option>
                                            <option value="green" <?php echo $settings['theme_color'] == 'green' ? 'selected' : ''; ?>>Hijau</option>
                                            <option value="purple" <?php echo $settings['theme_color'] == 'purple' ? 'selected' : ''; ?>>Ungu</option>
                                            <option value="orange" <?php echo $settings['theme_color'] == 'orange' ? 'selected' : ''; ?>>Oranye</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Bahasa</label>
                                        <select class="form-select" name="language">
                                            <option value="id" <?php echo $settings['language'] == 'id' ? 'selected' : ''; ?>>Indonesia</option>
                                            <option value="en" <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                                   <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?> id="maintenanceMode">
                                            <label class="form-check-label" for="maintenanceMode">
                                                Mode Maintenance
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable_registration" 
                                                   <?php echo $settings['enable_registration'] ? 'checked' : ''; ?> id="enableRegistration">
                                            <label class="form-check-label" for="enableRegistration">
                                                Aktifkan Registrasi User
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="update_settings" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Logo Website</h5>
                            </div>
                            <div class="card-body text-center">
                                <?php
                                $current_logo = '../assets/images/default-logo.png';
                                if (isset($settings['site_logo']) && file_exists($settings['site_logo'])) {
                                    $current_logo = $settings['site_logo'];
                                }
                                ?>
                                <img src="<?php echo $current_logo; ?>" 
                                     alt="Logo" class="img-fluid mb-3" style="max-height: 100px;" id="logoPreview">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="site_logo" accept="image/*" id="logoInput">
                                        <small class="text-muted">Format: JPG, PNG, GIF (Max: 2MB)</small>
                                    </div>
                                    <button type="submit" name="upload_logo" class="btn btn-outline-primary">
                                        <i class="fas fa-upload me-2"></i>Ubah Logo
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Pengaturan Keamanan</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Password Admin</label>
                                        <input type="password" class="form-control" name="admin_password" placeholder="Kosongkan jika tidak ingin mengubah">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" name="confirm_password" placeholder="Ulangi password baru">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Session Timeout (menit)</label>
                                        <input type="number" class="form-control" name="session_timeout" 
                                               value="<?php echo $settings['session_timeout'] ?? 30; ?>" min="5" max="120">
                                    </div>
                                    
                                    <button type="submit" name="update_security" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-shield-alt me-2"></i>Update Keamanan
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Backup & Restore</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-success" onclick="backupDatabase()">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="restoreDatabase()">
                                        <i class="fas fa-upload me-2"></i>Restore Database
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logo preview
        document.getElementById('logoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle logo upload
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitter = e.submitter;
                if (submitter && submitter.name === 'upload_logo') {
                    const fileInput = this.querySelector('input[name="site_logo"]');
                    if (fileInput && fileInput.files.length === 0) {
                        e.preventDefault();
                        alert('Silakan pilih file logo terlebih dahulu!');
                    }
                }
            });
        });

        // Security settings
        function backupDatabase() {
            if (confirm('Apakah Anda yakin ingin membackup database?')) {
                window.location.href = 'export-data.php?type=backup';
            }
        }

        function restoreDatabase() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.sql';
            input.onchange = function(e) {
                if (e.target.files.length > 0) {
                    if (confirm('Apakah Anda yakin ingin merestore database? Data yang ada akan ditimpa.')) {
                        // Handle restore process
                        alert('Fitur restore akan segera tersedia!');
                    }
                }
            };
            input.click();
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="admin_password"]');
            const confirm = document.querySelector('input[name="confirm_password"]');
            
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
            }
        });
    </script>
</body>
</html>
