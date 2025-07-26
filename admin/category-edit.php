<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success_message = '';
$error_message = '';
$category = [];

// Get category data
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($category_id <= 0) {
    header('Location: categories.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        header('Location: categories.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: categories.php');
    exit();
}

// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $icon = trim($_POST['icon']);
    
    if (empty($name)) {
        $error_message = 'Nama kategori harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
            $stmt->execute([$name, $description, $icon, $category_id]);
            $success_message = 'Kategori berhasil diperbarui!';
            header('Location: categories.php?success=1');
            exit();
        } catch (PDOException $e) {
            $error_message = 'Gagal memperbarui kategori! Nama kategori sudah ada.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Admin Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap/5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <h1 class="h3 mb-0">Edit Kategori</h1>
                        <p class="text-muted">Perbarui informasi kategori</p>
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
                                <h5 class="mb-0">Form Edit Kategori</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Kategori *</label>
                                        <input type="text" class="form-control" name="name" 
                                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Icon</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-icons"></i></span>
                                            <input type="text" class="form-control" name="icon" 
                                                   value="<?php echo htmlspecialchars($category['icon']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Preview Icon</label>
                                        <div class="border rounded p-3 text-center">
                                            <i id="iconPreview" class="<?php echo htmlspecialchars($category['icon']); ?> fa-3x text-primary"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Perbarui Kategori
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
                                <h5 class="mb-0">Statistik Kategori</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                try {
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                                    $stmt->execute([$category_id]);
                                    $product_count = $stmt->fetchColumn();
                                } catch (PDOException $e) {
                                    $product_count = 0;
                                }
                                ?>
                                <ul class="list-unstyled">
                                    <li><strong>Total Produk:</strong> <?php echo $product_count; ?></li>
                                    <li><strong>Dibuat:</strong> <?php echo date('d/m/Y', strtotime($category['created_at'])); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap/5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Icon preview
        document.querySelector('input[name="icon"]').addEventListener('input', function() {
            const iconClass = this.value || 'fas fa-folder';
            document.getElementById('iconPreview').className = iconClass + ' fa-3x text-primary';
        });
    </script>
</body>
</html>
