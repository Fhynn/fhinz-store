<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-store me-2"></i>Fhinz Store
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="fas fa-boxes me-1"></i>Produk
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-list me-1"></i>Kategori
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                            $nav_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($nav_categories as $category):
                        ?>
                        <li>
                            <a class="dropdown-item" href="products.php?category=<?php echo $category['id']; ?>">
                                <i class="<?php echo $category['icon']; ?> me-2"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; } catch(PDOException $e) {} ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">
                        <i class="fas fa-info-circle me-1"></i>Tentang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">
                        <i class="fas fa-envelope me-1"></i>Kontak
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if(isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profil
                            </a></li>
                            <li><a class="dropdown-item" href="orders.php">
                                <i class="fas fa-shopping-bag me-2"></i>Pesanan Saya
                            </a></li>
                            <?php if(isAdmin()): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin/">
                                <i class="fas fa-cog me-2"></i>Admin Panel
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="cart-count">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>