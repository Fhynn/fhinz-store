<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success_message = '';
$error_message = '';

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
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, duration_days, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $description, $price, $duration_days, $image_url, $status]);
            $success_message = 'Produk berhasil ditambahkan!';
            
            // Clear form
            $_POST = array();
        } catch (PDOException $e) {
            $error_message = 'Gagal menambahkan produk!';
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
    <title>Tambah Produk - Admin Fhinz Store</title>
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
                        <h1 class="h3 mb-0">Tambah Produk Baru</h1>
                        <p class="text-muted">Tambahkan produk aplikasi premium</p>
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
                                    <i class="fas fa-plus me-2"></i>
                                    Form Tambah Produk
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   placeholder="Contoh: Netflix Premium" required
                                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Pilih Kategori</option>
                                                <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"
                                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Deskripsi detail produk..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price" 
                                                   placeholder="25000" min="1" step="1000" required
                                                   value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="duration_days" class="form-label">Durasi (Hari) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="duration_days" name="duration_days" 
                                                   placeholder="30" min="1" required
                                                   value="<?php echo isset($_POST['duration_days']) ? $_POST['duration_days'] : '30'; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : 'selected'; ?>>Aktif</option>
                                                <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image_url" class="form-label">URL Gambar</label>
                                        <input type="url" class="form-control" id="image_url" name="image_url" 
                                               placeholder="https://example.com/image.jpg"
                                               value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>">
                                        <small class="text-muted">Kosongkan jika menggunakan gambar default</small>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="products.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Simpan Produk
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
                                        <img id="previewImg" src="../assets/images/default-product.jpg" 
                                             alt="Preview" class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover;">
                                    </div>
                                    <div class="preview-content">
                                        <h6 id="previewName">Nama Produk</h6>
                                        <p id="previewCategory" class="text-muted small">Kategori</p>
                                        <p id="previewDescription" class="small">Deskripsi produk akan muncul di sini...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong id="previewPrice" class="text-success">Rp 0</strong>
                                            <small id="previewDuration" class="text-muted">0 hari</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tips Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Tips Menambah Produk
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Gunakan nama produk yang jelas dan mudah dicari</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Tulis deskripsi yang detail dan menarik</small>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Set harga yang kompetitif</small>
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Gunakan gambar berkualitas tinggi</small>
                                    </li>
                                </ul>
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
        // Live preview
        function updatePreview() {
            const name = document.getElementById('name').value || 'Nama Produk';
            const description = document.getElementById('description').value || 'Deskripsi produk akan muncul di sini...';
            const price = document.getElementById('price').value || '0';
            const duration = document.getElementById('duration_days').value || '0';
            const imageUrl = document.getElementById('image_url').value;
            const categorySelect = document.getElementById('category_id');
            const categoryText = categorySelect.options[categorySelect.selectedIndex].text || 'Kategori';

            document.getElementById('previewName').textContent = name;
            document.getElementById('previewDescription').textContent = description.substring(0, 100) + (description.length > 100 ? '...' : '');
            document.getElementById('previewPrice').textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
            document.getElementById('previewDuration').textContent = duration + ' hari';
            document.getElementById('previewCategory').textContent = categoryText;

            if (imageUrl) {
                document.getElementById('previewImg').src = imageUrl;
            }
        }

        // Add event listeners
        document.getElementById('name').addEventListener('input', updatePreview);
        document.getElementById('description').addEventListener('input', updatePreview);
        document.getElementById('price').addEventListener('input', updatePreview);
        document.getElementById('duration_days').addEventListener('input', updatePreview);
        document.getElementById('image_url').addEventListener('input', updatePreview);
        document.getElementById('category_id').addEventListener('change', updatePreview);

        // Image error handling
        document.getElementById('previewImg').addEventListener('error', function() {
            this.src = '../assets/images/default-product.jpg';
        });

        // Initialize preview
        updatePreview();
    </script>
</body>
</html>