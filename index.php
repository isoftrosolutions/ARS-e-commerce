<?php
$page_title    = 'Online Shopping in Nepal | Buy Electronics, Fashion & More';
$page_meta_desc= 'Easy Shopping A.R.S — Nepal\'s trusted online store. Shop electronics, fashion, home goods & more with fast delivery to Birgunj, Parsa and across Nepal. eSewa & COD accepted.';
require_once __DIR__ . '/includes/header-bootstrap.php';

// Fetch featured products
try {
    $stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8");
    $latest_products = $stmt->fetchAll();
    
    $categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();
} catch (PDOException $e) {
    $latest_products = [];
    $categories = [];
}
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="hero-bg"></div>
    <div class="container position-relative py-5 py-md-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10 col-xl-8">
                <span class="badge bg-primary px-3 py-2 mb-3 mb-md-4 animate-bounce-in">
                    <i class="bi bi-stars me-1"></i> New Season Arrival
                </span>
                <h1 class="display-5 display-md-4 display-lg-3 fw-bold text-white mb-3 mb-md-4 lh-base">
                    UPGRADE YOUR <span class="text-warning">LIFESTYLE</span> WITH ARS
                </h1>
                <p class="lead text-light opacity-75 mb-4 mb-md-5 mx-auto" style="max-width: 600px;">
                    Discover the latest trends in electronics, fashion, and home essentials. Premium quality, delivered fast to your doorstep.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="shop.php" class="btn btn-primary btn-lg px-4 px-md-5 py-3 fw-semibold shadow-lg">
                        <i class="bi bi-bag me-2"></i>Shop Collection
                    </a>
                    <a href="#featured" class="btn btn-outline-light btn-lg px-4 px-md-5 py-3 fw-semibold">
                        <i class="bi bi-percent me-2"></i>View Deals
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Bar -->
<section class="features-section bg-white shadow-sm py-4 py-md-5">
    <div class="container">
        <div class="row g-4 text-center text-md-start">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="feature-icon feature-icon-blue">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold small">Free Shipping</h6>
                        <small class="text-muted">Orders over Rs. 1,000</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="feature-icon feature-icon-green">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold small">Secure Payment</h6>
                        <small class="text-muted">100% Protected</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="feature-icon feature-icon-orange">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold small">Easy Returns</h6>
                        <small class="text-muted">7-Day Replacement</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                    <div class="feature-icon feature-icon-purple">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold small">24/7 Support</h6>
                        <small class="text-muted">Expert Help</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Grid -->
<section class="py-5 py-md-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4 mb-md-5">
            <div>
                <h2 class="h3 h2-md fw-bold text-dark mb-1">Shop by Category</h2>
                <p class="text-muted mb-0">Handpicked categories for your needs</p>
            </div>
            <a href="shop.php" class="btn btn-outline-primary btn-sm d-none d-md-inline-flex align-items-center gap-1">
                All Categories <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-3 g-md-4">
            <?php foreach($categories as $cat): ?>
                <div class="col-4 col-md-2">
                    <a href="shop.php?category=<?= $cat['id'] ?>" class="text-decoration-none">
                        <div class="category-card text-center p-3 p-md-4 rounded-3 bg-white shadow-sm h-100">
                            <div class="category-icon mb-2 mb-md-3">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold small"><?= htmlspecialchars($cat['name']) ?></h6>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section id="featured" class="bg-light py-5 py-md-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4 mb-md-5">
            <div>
                <h2 class="h3 h2-md fw-bold text-dark mb-1">Featured Products</h2>
                <p class="text-muted mb-0">Trending items that people love right now</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-icon rounded-circle" type="button">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="btn btn-outline-secondary btn-icon rounded-circle" type="button">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach($latest_products as $p): ?>
                <div class="col-6 col-lg-3">
                    <div class="product-card card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="position-relative">
                            <?php if($p['discount_price']): ?>
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 m-md-3">
                                    -<?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2 m-md-3 wishlist-badge">
                                <i class="bi bi-heart"></i>
                            </span>
                            <a href="product.php?slug=<?= $p['slug'] ?>">
                                <img src="<?= getProductImage($p['image']) ?>" 
                                     class="card-img-top p-3 p-md-4" 
                                     alt="<?= $p['name'] ?>" 
                                     loading="lazy"
                                     style="height: 180px; object-fit: contain;">
                            </a>
                        </div>
                        <div class="card-body text-center p-3 p-md-4">
                            <span class="badge bg-light text-primary mb-2"><?= htmlspecialchars($p['cat_name'] ?? 'General') ?></span>
                            <a href="product.php?slug=<?= $p['slug'] ?>" class="text-decoration-none">
                                <h5 class="card-title h6 fw-bold text-dark mb-2 line-clamp-1"><?= htmlspecialchars($p['name']) ?></h5>
                            </a>
                            <div class="text-warning mb-2 small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="text-muted ms-1">(24)</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fw-bold text-dark h5 mb-0"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></span>
                                    <?php if($p['discount_price']): ?>
                                        <small class="text-muted text-decoration-line-through d-block"><?= formatPrice($p['price']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-dark btn-sm rounded-2">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="shop.php" class="btn btn-outline-dark btn-lg px-5">
                View All Products <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 py-md-5">
    <div class="container">
        <div class="cta-section rounded-4 p-4 p-md-5 p-lg-5 position-relative overflow-hidden">
            <div class="cta-decoration cta-decoration-1"></div>
            <div class="cta-decoration cta-decoration-2"></div>
            <div class="row align-items-center justify-content-between position-relative">
                <div class="col-lg-7 text-center text-lg-start mb-4 mb-lg-0">
                    <h2 class="h2 h1-lg fw-bold text-white mb-3">READY TO EXPERIENCE PREMIUM SHOPPING?</h2>
                    <p class="text-white opacity-75 mb-0 d-none d-md-block">Join 10,000+ satisfied customers across Nepal. Get exclusive deals delivered to your inbox.</p>
                </div>
                <div class="col-lg-4 text-center">
                    <a href="auth/signup.php" class="btn btn-light btn-lg px-5 fw-bold mb-2">
                        <i class="bi bi-person-plus me-2 text-dark"></i>
                        <span class="text-dark" style="color: #0f172a !important;">Create Account</span><i class="bi bi-arrow-right ms-2 text-dark"></i>
                    </a>
                    <small class="text-white opacity-50 d-block">No Credit Card Required</small>
                </div>
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
