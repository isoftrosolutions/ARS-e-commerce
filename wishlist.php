<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to view your wishlist.", "info");
}

try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Wishlist Error: " . $e->getMessage());
}

$page_title = "My Wishlist";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Account</a></li>
                <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Wishlist Content -->
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row g-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush rounded-0">
                            <a href="dashboard.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a href="orders.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-box-seam me-2"></i>My Orders
                            </a>
                            <a href="wishlist.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-heart me-2"></i>Wishlist
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                            <a href="auth/logout.php" class="list-group-item list-group-item-action text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">My Wishlist</h3>
                    <span class="badge bg-light text-dark"><?= count($wishlist_items) ?> items</span>
                </div>
                
                <?php if(empty($wishlist_items)): ?>
                    <div class="card border-0 shadow-sm rounded-3 text-center py-5">
                        <div class="card-body py-5">
                            <i class="bi bi-heart display-1 text-muted opacity-25"></i>
                            <h4 class="mt-4 mb-2">Your wishlist is empty</h4>
                            <p class="text-muted mb-4">Save items you love for later.</p>
                            <a href="shop.php" class="btn btn-primary px-4">
                                <i class="bi bi-bag me-2"></i>Browse Products
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach($wishlist_items as $item): ?>
                            <div class="col-6 col-md-4 col-lg-4">
                                <div class="product-card card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                    <div class="position-relative">
                                        <?php if($item['discount_price']): ?>
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                                -<?= round(($item['price'] - $item['discount_price']) / $item['price'] * 100) ?>%
                                            </span>
                                        <?php endif; ?>
                                        <a href="wishlist-action.php?action=remove&id=<?= $item['id'] ?>" 
                                           class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow-sm">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                        <a href="product.php?slug=<?= $item['slug'] ?>">
                                            <img src="<?= !empty($item['image']) ? UPLOAD_DIR . $item['image'] : 'https://via.placeholder.com/300' ?>" 
                                                 class="card-img-top p-4" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 style="height: 180px; object-fit: contain;">
                                        </a>
                                    </div>
                                    <div class="card-body text-center p-3">
                                        <span class="badge bg-light text-primary mb-2"><?= htmlspecialchars($item['category_name'] ?? 'General') ?></span>
                                        <a href="product.php?slug=<?= $item['slug'] ?>" class="text-decoration-none">
                                            <h5 class="h6 fw-bold text-dark mb-2 line-clamp-1"><?= htmlspecialchars($item['name']) ?></h5>
                                        </a>
                                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                            <span class="fw-bold text-dark"><?= formatPrice($item['discount_price'] ?: $item['price']) ?></span>
                                            <?php if($item['discount_price']): ?>
                                                <small class="text-muted text-decoration-line-through"><?= formatPrice($item['price']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <button onclick="addToCart(<?= $item['id'] ?>)" class="btn btn-dark btn-sm w-100 rounded-2">
                                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
