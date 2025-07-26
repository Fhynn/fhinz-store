<div class="admin-sidebar">
    <div class="sidebar-header">
        <a href="../index.php" class="sidebar-brand">
            <i class="fas fa-store me-2"></i>
            <span>Fhinz Store</span>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : ''; ?>" 
                   href="products.php">
                    <i class="fas fa-boxes me-2"></i>
                    Produk
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'category-add.php', 'category-edit.php']) ? 'active' : ''; ?>" 
                   href="categories.php">
                    <i class="fas fa-tags me-2"></i>
                    Kategori
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['orders.php', 'order-detail.php']) ? 'active' : ''; ?>" 
                   href="orders.php">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Pesanan
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
                        $pending_orders = $stmt->fetchColumn();
                        if ($pending_orders > 0):
                    ?>
                    <span class="badge bg-warning rounded-pill ms-auto"><?php echo $pending_orders; ?></span>
                    <?php endif; } catch(PDOException $e) {} ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" 
                   href="customers.php">
                    <i class="fas fa-users me-2"></i>
                    Customer
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>" 
                   href="testimonials.php">
                    <i class="fas fa-star me-2"></i>
                    Testimoni
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" 
                   href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Laporan
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <div class="nav-link-header">
                    <small class="text-muted">PENGATURAN</small>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" 
                   href="settings.php">
                    <i class="fas fa-cog me-2"></i>
                    Pengaturan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" 
                   href="profile.php">
                    <i class="fas fa-user me-2"></i>
                    Profil
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>