<?php
require_once 'config/database.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

try {
    // Get product details
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.icon as category_icon 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: products.php');
        exit();
    }
    
    // Get related products
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
                          ORDER BY RAND() 
                          LIMIT 4");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get testimonials for this product
    $stmt = $pdo->prepare("SELECT t.*, u.full_name as user_name 
                          FROM testimonials t 
                          LEFT JOIN users u ON t.user_id = u.id 
                          WHERE t.product_id = ? AND t.status = 'approved' 
                          ORDER BY t.created_at DESC 
                          LIMIT 5");
    $stmt->execute([$product_id]);
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    header('Location: products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Fhinz Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <meta name="description" content="<?php echo htmlspecialchars($product['description']); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($product['description']); ?>">
    <meta property="og:image" content="<?php echo $product['image_url'] ?: 'assets/images/default-product.jpg'; ?>">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section py-3 bg-light">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
                    <li class="breadcrumb-item">
                        <a href="products.php?category=<?php echo $product['category_id']; ?>">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Product Detail Section -->
    <section class="product-detail-section py-5">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4">
                    <div class="product-image-container">
                        <img src="<?php echo $product['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="img-fluid rounded shadow-sm main-product-image" id="mainImage">
                        
                        <!-- Image Gallery (if multiple images available) -->
                        <div class="product-thumbnails mt-3">
                            <div class="row">
                                <div class="col-3">
                                    <img src="<?php echo $product['image_url'] ?: 'assets/images/default-product.jpg'; ?>" 
                                         alt="Thumbnail 1" class="img-fluid rounded thumbnail-image active"
                                         onclick="changeMainImage(this.src)">
                                </div>
                                <!-- Add more thumbnails here if needed -->
                            </div>