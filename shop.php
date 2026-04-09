<?php
$page_title     = 'Shop All Products | Electronics, Fashion & More in Nepal';
$page_meta_desc = 'Browse our full collection of electronics, fashion, home goods and more. Shop online in Nepal with fast delivery to Birgunj, Parsa and nationwide. Easy payments via eSewa, FonePay & COD.';
require_once __DIR__ . '/includes/header-bootstrap.php';

$category_id = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?? 0;
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'newest';
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT) ?? 0;
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT) ?? 0;

if ($min_price < 0) $min_price = 0;
if ($max_price < 0) $max_price = 0;

try {
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0";
    $params = [];
    
    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($query) {
        $search_term = "%$query%";
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($min_price > 0) {
        $sql .= " AND (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price > 0) {
        $sql .= " AND (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) <= ?";
        $params[] = $max_price;
    }

    switch($sort) {
        case 'price_low': 
            $sql .= " ORDER BY (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) ASC"; 
            break;
        case 'price_high': 
            $sql .= " ORDER BY (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) DESC"; 
            break;
        case 'name': 
            $sql .= " ORDER BY p.name ASC"; 
            break;
        default: 
            $sql .= " ORDER BY p.created_at DESC"; 
            break;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    $categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
    
    $max_product_price = $pdo->query("SELECT MAX(CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) as max FROM products")->fetch()['max'] ?? 50000;
    
} catch (PDOException $e) {
    error_log("Shop Error: " . $e->getMessage());
    $products = [];
    $categories = [];
    $max_product_price = 50000;
}

// Get current category name
$current_cat_name = 'All Products';
if ($category_id > 0) {
    foreach($categories as $cat) {
        if($cat['id'] == $category_id) {
            $current_cat_name = $cat['name'];
            break;
        }
    }
} elseif($query) {
    $current_cat_name = "Search: '" . htmlspecialchars($query) . "'";
}
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shop</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mt-2 mb-0"><?= $current_cat_name ?></h1>
    </div>
</section>

<!-- Main Content -->
<div class="container py-4 py-md-5">
    <div class="row g-4">
        
        <!-- Sidebar Filters -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    
                    <!-- Categories -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-funnel me-2 text-primary"></i>Categories
                        </h6>
                        <div class="list-group list-group-flush">
                            <a href="shop.php<?= $query ? '?q=' . urlencode($query) : '' ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 py-2 <?= $category_id === 0 ? 'active' : '' ?>">
                                <span>All Products</span>
                            </a>
                            <?php foreach($categories as $cat): ?>
                                <a href="shop.php?category=<?= $cat['id'] ?><?= $query ? '&q=' . urlencode($query) : '' ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 py-2 <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                                    <span><?= htmlspecialchars($cat['name']) ?></span>
                                    <span class="badge bg-light text-dark rounded-pill"><?= (int)$cat['product_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Filter -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-tag me-2 text-primary"></i>Price Range
                        </h6>
                        <form method="GET" action="shop.php">
                            <input type="hidden" name="category" value="<?= $category_id ?>">
                            <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <input type="number" name="min_price" value="<?= $min_price ?: 0 ?>" min="0"
                                           class="form-control form-control-sm" placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" value="<?= $max_price ?: '' ?>" min="0"
                                           class="form-control form-control-sm" placeholder="Max">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark btn-sm w-100">Apply Filter</button>
                            <?php if ($min_price > 0 || $max_price > 0): ?>
                                <a href="shop.php?category=<?= $category_id ?><?= $query ? '&q=' . urlencode($query) : '' ?>" 
                                   class="btn btn-outline-danger btn-sm w-100 mt-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <!-- Promo Card -->
                    <div class="bg-dark rounded-3 p-4 text-center">
                        <h6 class="text-warning fw-bold">EXTRA 15% OFF</h6>
                        <p class="text-white-50 small mb-3">On your first mobile app order</p>
                        <code class="text-warning">APP15</code>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            
            <!-- Toolbar -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <span class="text-muted">
                            <strong><?= count($products) ?></strong> products found
                        </span>
                        <form method="GET" action="shop.php" class="d-flex align-items-center gap-2">
                            <?php if($category_id > 0): ?><input type="hidden" name="category" value="<?= $category_id ?>"><?php endif; ?>
                            <?php if($query): ?><input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>"><?php endif; ?>
                            <?php if($min_price > 0): ?><input type="hidden" name="min_price" value="<?= $min_price ?>"><?php endif; ?>
                            <?php if($max_price > 0): ?><input type="hidden" name="max_price" value="<?= $max_price ?>"><?php endif; ?>
                            <label class="text-muted small">Sort by:</label>
                            <select name="sort" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto;">
                                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
                                <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Products -->
            <?php if (!empty($products)): ?>
                <div class="row g-4">
                    <?php foreach ($products as $p): ?>
                        <?php 
                        $has_wishlist = false;
                        if (is_logged_in()) {
                            $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                            $check->execute([$_SESSION['user_id'], $p['id']]);
                            $has_wishlist = (bool)$check->fetch();
                        }
                        ?>
                        <div class="col-6 col-md-4">
                            <div class="product-card card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                <div class="position-relative">
                                    <?php if($p['discount_price']): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 z-1">
                                            -<?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%
                                        </span>
                                    <?php endif; ?>
                                    <a href="wishlist-action.php?action=<?= $has_wishlist ? 'remove' : 'add' ?>&id=<?= $p['id'] ?>" 
                                       class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-2 z-1 shadow-sm <?= $has_wishlist ? 'text-danger' : '' ?>">
                                        <i class="bi bi-heart<?= $has_wishlist ? '-fill' : '' ?>"></i>
                                    </a>
                                    <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>">
                                        <img src="<?= getProductImage($p['image']) ?>" 
                                             class="card-img-top p-4" 
                                             alt="<?= htmlspecialchars($p['name']) ?>" 
                                             loading="lazy"
                                             style="height: 200px; object-fit: contain;">
                                    </a>
                                </div>
                                <div class="card-body text-center p-3">
                                    <span class="badge bg-light text-primary mb-2"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                                    <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-decoration-none">
                                        <h5 class="card-title h6 fw-bold text-dark mb-2 line-clamp-1"><?= htmlspecialchars($p['name']) ?></h5>
                                    </a>
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                        <span class="fw-bold text-dark h5 mb-0"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></span>
                                        <?php if($p['discount_price']): ?>
                                            <small class="text-muted text-decoration-line-through"><?= formatPrice($p['price']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-dark btn-sm w-100 rounded-2">
                                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3 text-center py-5">
                    <div class="card-body py-5">
                        <i class="bi bi-search display-1 text-muted opacity-25"></i>
                        <h4 class="mt-4 mb-2 text-dark">No products found</h4>
                        <p class="text-muted mb-4">We couldn't find anything matching your filters.</p>
                        <a href="shop.php" class="btn btn-primary px-4">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
