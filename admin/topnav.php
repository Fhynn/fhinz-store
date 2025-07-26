<?php
require_once '../config/database.php';

// Get admin info
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$admin_email = $_SESSION['email'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <button class="btn btn-link d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="d-flex align-items-center">
            <h5 class="mb-0 d-none d-md-block">
                <?php 
                $current_page = basename($_SERVER['PHP_SELF']);
                $page_titles = [
                    'dashboard.php' => 'Dashboard',
                    'products.php' => 'Kelola Produk',
                    'product-add.php' => 'Tambah Produk',
                    'product-edit.php' => 'Edit Produk',
                    'categories.php' => 'Kelola Kategori',
                    'orders.php' => 'Kelola Pesanan',
                    'order-detail.php' => 'Detail Pesanan',
                    'customers.php' => 'Kelola Customer',
                    'testimonials.php' => 'Kelola Testimoni',
                    'reports.php' => 'Laporan & Analisis',
                    'settings.php' => 'Pengaturan',
                    'profile.php' => 'Profil Admin'
                ];
                echo $page_titles[$current_page] ?? 'Admin Panel';
                ?>
            </h5>
        </div>

        <div class="d-flex align-items-center ms-auto">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <button class="btn btn-link position-relative" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell text-muted"></i>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
                        $pending_orders = $stmt->fetchColumn();
                        if ($pending_orders > 0):
                    ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $pending_orders; ?>
                    </span>
                    <?php endif; } catch(PDOException $e) {} ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT o.order_number, o.created_at, u.full_name 
                                           FROM orders o 
                                           LEFT JOIN users u ON o.user_id = u.id 
                                           WHERE o.status = 'pending' 
                                           ORDER BY o.created_at DESC 
                                           LIMIT 5");
                        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($notifications)):
                    ?>
                    <li><a class="dropdown-item text-muted" href="#">Tidak ada notifikasi baru</a></li>
                    <?php else: ?>
                    <?php foreach($notifications as $notif): ?>
                    <li>
                        <a class="dropdown-item" href="orders.php">
                            <small class="text-muted"><?php echo date('d/m H:i', strtotime($notif['created_at'])); ?></small><br>
                            <strong>Pesanan baru</strong> dari <?php echo htmlspecialchars($notif['full_name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; } catch(PDOException $e) {} ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="orders.php">Lihat semua pesanan</a></li>
                </ul>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn btn-link d-flex align-items-center text-decoration-none" type="button" data-bs-toggle="dropdown">
                    <img src="../assets/images/default-avatar.jpg" alt="Profile" class="rounded-circle me-2" width="32" height="32">
                    <span class="d-none d-md-block"><?php echo htmlspecialchars($admin_name); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Halo, <?php echo htmlspecialchars($admin_name); ?></h6></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
