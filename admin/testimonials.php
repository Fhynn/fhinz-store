<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

$success_message = '';
$error_message = '';

// Handle testimonial actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        try {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $success_message = 'Status testimonial berhasil diperbarui!';
        } catch (PDOException $e) {
            $error_message = 'Gagal memperbarui status testimonial!';
        }
    }
}

// Get testimonials
try {
    $stmt = $pdo->query("SELECT t.*, u.full_name, u.email 
                        FROM testimonials t 
                        LEFT JOIN users u ON t.user_id = u.id 
                        ORDER BY t.created_at DESC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $testimonials = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimoni - Admin Fhinz Store</title>
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
                        <h1 class="h3 mb-0">Testimoni Customer</h1>
                        <p class="text-muted">Kelola testimoni dari customer</p>
                    </div>
                </div>

                <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Testimoni</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($testimonials)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada testimoni dari customer</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Testimoni</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($testimonials as $testimonial): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($testimonial['full_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($testimonial['email']); ?></small>
                                        </td>
                                        <td>
                                            <div>
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <br>
                                                <small><?php echo $testimonial['rating']; ?>/5</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 300px;">
                                                <?php echo htmlspecialchars(substr($testimonial['testimonial'], 0, 100)); ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($testimonial['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $testimonial['status'] == 'approved' ? 'success' : ($testimonial['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($testimonial['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $testimonial['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="approved" <?php echo $testimonial['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="rejected" <?php echo $testimonial['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
