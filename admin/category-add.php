<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success_message = '';
$error_message = '';

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $icon = trim($_POST['icon']);
    
    if (empty($name)) {
        $error_message = 'Nama kategori harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $icon]);
            $success_message = 'Kategori berhasil ditambahkan!';
            header('Location: categories.php?success=1');
            exit();
        } catch (PDOException $e) {
            $error_message = 'Gagal menambahkan kategori! Nama kategori sudah ada.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - Admin Fhinz Store</title>
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
                        <h1 class="h3 mb-0">Tambah Kategori Baru</h1>
                        <p class="text-muted">Tambahkan kategori produk baru</p>
                    </div>
                    <div>
                        <a href="categories.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
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
                                <h5 class="mb-0">Form Tambah Kategori</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Kategori *</label>
                                        <input type="text" class="form-control" name="name" 
                                               placeholder="Contoh: Aplikasi Musik" required>
                                        <small class="text-muted">Nama kategori akan ditampilkan di website</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="description" rows="3" 
                                                  placeholder="Deskripsi singkat tentang kategori ini"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Icon</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-icons"></i></span>
                                            <input type="text" class="form-control" name="icon" 
                                                   placeholder="fas fa-music" 
                                                   value="fas fa-folder">
                                        </div>
                                        <small class="text-muted">Gunakan icon dari Font Awesome (contoh: fas fa-music)</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Preview Icon</label>
                                        <div class="border rounded p-3 text-center">
                                            <i id="iconPreview" class="fas fa-folder fa-3x text-primary"></i>
                                            <p class="mt-2 mb-0 text-muted">Preview icon akan muncul di sini</p>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Simpan Kategori
                                        </button>
                                        <a href="categories.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi</h5>
                            </div>
                            <div class="card-body">
                                <h6>Icon Font Awesome</h6>
                                <p class="text-muted">Gunakan icon dari Font Awesome untuk kategori. Contoh:</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-music me-2"></i>fas fa-music</li>
                                    <li><i class="fas fa-video me-2"></i>fas fa-video</li>
                                    <li><i class="fas fa-camera me-2"></i>fas fa-camera</li>
                                    <li><i class="fas fa-edit me-2"></i>fas fa-edit</li>
                                </ul>
                                <a href="https://fontawesome.com/icons" target="_blank" class="btn btn-sm btn-outline-primary">
                                    Lihat Semua Icon
                                </a>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Tips</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li>• Gunakan nama kategori yang jelas dan mudah dipahami</li>
                                    <li>• Tambahkan deskripsi untuk membantu customer</li>
                                    <li>• Pilih icon yang merepresentasikan kategori</li>
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
        // Icon preview
        document.querySelector('input[name="icon"]').addEventListener('input', function() {
            const iconClass = this.value || 'fas fa-folder';
            document.getElementById('iconPreview').className = iconClass + ' fa-3x text-primary';
        });
    </script>
</body>
</html>
