<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

$success_message = '';
$error_message = '';

// Get product data
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: products.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: products.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $duration_days = intval($_POST['duration_days']);
    $image_url = trim($_POST['image_url']);
    $status = $_POST['status'];
    
    if (empty($name) || empty($description) || $price <= 0 || $duration_days <= 0) {
        $error_message = 'Semua field wajib diisi dengan benar!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, duration_days = ?, image_url = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$category_id, $name, $description, $price, $duration_days, $image_url, $status, $product_id]);
            $success_message = 'Produk berhasil diperbarui!';
            
            // Refresh product data
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error_message = 'Gagal memperbarui produk!';
        }
    }
}

// Get categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Admin Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Top Navigation -->
            <?php include 'includes/topnav.php'; ?>
            
            <!-- Page Content -->
            <div class="container-fluid py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">Edit Produk</h1>
                        <p class="text-muted">Edit produk: <?php echo htmlspecialchars($product['name']); ?></p>
                    </div>
                    <div>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
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

                <!-- Product Form -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    Form Edit Produk
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   placeholder="Contoh: Netflix Premium" required
                                                   value="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Pilih Kategori</option>
                                                <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"
                                                        <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Deskripsi detail produk..." required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price" 
                                                   placeholder="25000" min="1" step="1000" required
                                                   value="<?php echo $product['price']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="duration_days" class="form-label">Durasi (Hari) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="duration_days" name="duration_days" 
                                                   placeholder="30" min="1" required
                                                   value="<?php echo $product['duration_days']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($product['status'] === 'active') ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="inactive" <?php echo ($product['status'] === 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image_url" class="form-label">URL Gambar</label>
                                        <input type="url" class="form-control" id="image_url" name="image_url" 
                                               placeholder="https://example.com/image.jpg"
                                               value="<?php echo htmlspecialchars($product['image_url']); ?>">
                                        <small class="text-muted">Kosongkan jika menggunakan gambar default</small>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="products.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Produk
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-eye me-2"></i>
                                    Preview Produk
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="product-preview">
                                    <div class="preview-image mb-3">
                                        <img id="previewImg" src="<?php echo $product['image_url'] ?: '../assets/images/default-product.jpg'; ?>" 
                                             alt="Preview" class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover;">
                                    </div>
                                    <div class="preview-content">
                                        <h6 id="previewName"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <p id="previewCategory" class="text-muted small">
                                            <?php
                                            foreach($categories as $cat) {
                                                if ($cat['id'] == $product['category_id']) {
                                                    echo htmlspecialchars($cat['name']);
                                                    break;
                                                }
                                            }
                                            ?>
                                        </p>
                                        <p id="previewDescription" class="small"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong id="previewPrice" class="text-success"><?php echo formatCurrency($product['price']); ?></strong>
                                            <small id="previewDuration" class="text-muted"><?php echo $product['duration_days']; ?> hari</small>
                                        </div>
                                        <div class="mt-2">
                                            <span id="previewStatus" class="badge <?php echo $product['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $product['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informasi Produk
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-item d-flex justify-content-between mb-2">
                                    <span>ID Produk:</span>
                                    <strong>#<?php echo $product['id']; ?></strong>
                                </div>
                                <div class="info-item d-flex justify-content-between mb-2">
                                    <span>Dibuat:</span>
                                    <span><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></span>
                                </div>
                                <div class="info-item d-flex justify-content-between">
                                    <span>Diupdate:</span>
                                    <span><?php echo date('d/m/Y H:i', strtotime($product['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const categories = <?php echo json_encode($categories); ?>;

        // Live preview
        function updatePreview() {
            const name = document.getElementById('name').value || 'Nama Produk';
            const description = document.getElementById('description').value || 'Deskripsi produk akan muncul di sini...';
            const price = document.getElementById('price').value || '0';
            const duration = document.getElementById('duration_days').value || '0';
            const imageUrl = document.getElementById('image_url').value;
            const categorySelect = document.getElementById('category_id');
            const categoryText = categorySelect.options[categorySelect.selectedIndex].text || 'Kategori';
            const status = document.getElementById('status').value;

            document.getElementById('previewName').textContent = name;
            document.getElementById('previewDescription').textContent = description.substring(0, 100) + (description.length > 100 ? '...' : '');
            document.getElementById('previewPrice').textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
            document.getElementById('previewDuration').textContent = duration + ' hari';
            document.getElementById('previewCategory').textContent = categoryText !== 'Pilih Kategori' ? categoryText : 'Kategori';

            // Update status badge
            const statusBadge = document.getElementById('previewStatus');
            if (status === 'active') {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Aktif';
            } else {
                statusBadge.className = 'badge bg-danger';
                statusBadge.textContent = 'Tidak Aktif';
            }

            if (imageUrl) {
                document.getElementById('previewImg').src = imageUrl;
            } else {
                document.getElementById('previewImg').src = '../assets/images/default-product.jpg';
            }
        }

        // Add event listeners
        document.getElementById('name').addEventListener('input', updatePreview);
        document.getElementById('description').addEventListener('input', updatePreview);
        document.getElementById('price').addEventListener('input', updatePreview);
        document.getElementById('duration_days').addEventListener('input', updatePreview);
        document.getElementById('image_url').addEventListener('input', updatePreview);
        document.getElementById('category_id').addEventListener('change', updatePreview);
        document.getElementById('status').addEventListener('change', updatePreview);

        // Image error handling
        document.getElementById('previewImg').addEventListener('error', function() {
            this.src = '../assets/images/default-product.jpg';
        });
    </script>
</body>
</html>