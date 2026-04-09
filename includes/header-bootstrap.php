<?php
require_once __DIR__ . '/functions.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch categories for dropdown
try {
    $stmt_cats = $pdo->query("SELECT * FROM categories LIMIT 8");
    $nav_categories = $stmt_cats->fetchAll();
} catch (PDOException $e) {
    $nav_categories = [];
}

$base_url  = rtrim(SITE_URL, '/');
$asset_url = $base_url . '/assets';

// Portable site path: '' on live root, '/ARS' on local subdirectory
$_site_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// SEO defaults — override per-page before including this header
$_seo_title    = isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME;
$_seo_desc     = isset($page_meta_desc) ? $page_meta_desc : 'Easy Shopping A.R.S — Your trusted online shopping destination in Nepal. Quality products, fast delivery across Birgunj, Parsa and all of Nepal.';
$_seo_canonical= isset($page_canonical) ? $page_canonical : $base_url . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_seo_image    = isset($page_og_image) ? $page_og_image : $asset_url . '/logo.jpeg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($_seo_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($_seo_desc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($_seo_canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="<?= SITE_NAME ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($_seo_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($_seo_desc) ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($_seo_canonical) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($_seo_image) ?>">
    <meta property="og:locale"      content="en_NP">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= htmlspecialchars($_seo_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($_seo_desc) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($_seo_image) ?>">

    <!-- PWA -->
    <link rel="manifest" href="<?= $_site_path ?>/manifest.json">
    <meta name="theme-color" content="#1a0f0a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="ARS Shop">
    <link rel="apple-touch-icon" href="<?= $asset_url ?>/logo.jpeg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $asset_url ?>/css/bootstrap-header.css">

    <!-- Global Schema -->
    <?php require_once __DIR__ . '/schema.php'; ?>
    <?php if (isset($page_schema)): ?>
        <?= $page_schema ?>
    <?php endif; ?>
</head>
<body class="bg-light">

    <!-- Top Announcement Bar -->
    <div class="announcement-bar py-2">
        <div class="container text-center">
            <span class="small fw-semibold">
                <i class="bi bi-truck me-1"></i>
                FREE DELIVERY on orders over Rs. 1,000 | Use code: <strong class="text-warning">ARS2026</strong>
            </span>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header bg-white shadow-sm sticky-top" id="mainHeader">
        
        <!-- Primary Navbar -->
        <nav class="navbar navbar-expand-lg py-2">
            <div class="container">
                
                <!-- Mobile Menu Toggle -->
                <button class="btn btn-light d-lg-none me-2 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Toggle navigation">
                    <i class="bi bi-list fs-4"></i>
                </button>
                
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center text-decoration-none" href="<?= $base_url ?>/index.php">
                    <div class="brand-logo me-2">
                        <img src="<?= $asset_url ?>/logo.jpeg" alt="ARS Shop Logo" style="width:32px;height:32px;object-fit:contain;padding:4px;">
                    </div>
                    <span class="brand-text fw-bold">ARS<span class="text-primary">SHOP</span></span>
                </a>
                
                <!-- Search Bar - Desktop -->
                <form class="d-none d-lg-flex flex-grow-1 mx-4" action="<?= $base_url ?>/shop.php" method="GET">
                    <div class="search-wrapper w-100">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control search-input" 
                                   placeholder="Search for products, brands and more..."
                                   value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                            <button class="btn btn-primary search-btn" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Right Actions -->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    
                    <!-- Wishlist -->
                    <a href="<?= $base_url ?>/wishlist.php" class="btn btn-light position-relative p-2" aria-label="Wishlist">
                        <i class="bi bi-heart"></i>
                    </a>
                    
                    <!-- Cart -->
                    <a href="<?= $base_url ?>/cart.php" class="btn btn-light position-relative p-2" aria-label="Shopping Cart">
                        <i class="bi bi-cart3"></i>
                        <?php if (get_cart_count() > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= get_cart_count() ?>
                                <span class="visually-hidden">items in cart</span>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Menu -->
                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn btn-light d-none d-lg-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-avatar"><?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?></span>
                                <span class="d-none d-xl-inline"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li class="dropdown-header">
                                    <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
                                    <small class="text-muted d-block"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/dashboard.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>/orders.php"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                                <?php if(is_admin()): ?>
                                    <li><a class="dropdown-item text-primary fw-semibold" href="<?= $base_url ?>/admin/dashboard.php"><i class="bi bi-gear me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= $base_url ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $base_url ?>/auth/login.php" class="btn btn-outline-dark d-none d-lg-flex align-items-center gap-2">
                            <i class="bi bi-person"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        
        <!-- Search Bar - Mobile (Below main nav) -->
        <form class="d-lg-none px-3 pb-3" action="<?= $base_url ?>/shop.php" method="GET">
            <div class="input-group">
                <input type="text" name="q" class="form-control" 
                       placeholder="Search products..." 
                       value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        
        <!-- Secondary Navbar -->
        <nav class="nav-bar d-none d-lg-block border-top">
            <div class="container">
                <ul class="nav-menu list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="shop.php" class="nav-link <?= ($current_page == 'shop.php' && !isset($_GET['category'])) ? 'active' : '' ?>">
                            <i class="bi bi-grid me-1"></i> All Products
                        </a>
                    </li>
                    <li class="list-inline-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list me-1"></i> Categories
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 animate-dropdown">
                            <li><a class="dropdown-item" href="shop.php">All Products</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach($nav_categories as $cat): ?>
                                <li><a class="dropdown-item" href="shop.php?category=<?= $cat['id'] ?>">
                                    <i class="bi bi-chevron-right me-2 text-muted"></i><?= htmlspecialchars($cat['name']) ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="list-inline-item">
                        <a href="faq.php" class="nav-link <?= ($current_page == 'faq.php') ? 'active' : '' ?>">
                            <i class="bi bi-question-circle me-1"></i> FAQ
                        </a>
                    </li>
                    <li class="list-inline-item ms-auto">
                        <a href="contact.php" class="nav-link text-primary fw-semibold <?= ($current_page == 'contact.php') ? 'active' : '' ?>">
                            <i class="bi bi-headset me-1"></i> 24/7 Support
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Mobile Menu -->
        <div class="collapse" id="mobileMenu">
            <div class="mobile-nav bg-white border-top">
                <div class="container py-3">
                    <!-- User section for logged in users -->
                    <?php if (is_logged_in()): ?>
                        <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                            <div class="user-avatar-lg"><?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?></div>
                            <div>
                                <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
                                <small class="d-block text-muted"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex gap-2 pb-3 mb-3 border-bottom">
                            <a href="auth/login.php" class="btn btn-outline-dark flex-grow-1">Login</a>
                            <a href="auth/signup.php" class="btn btn-primary flex-grow-1">Sign Up</a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Mobile Links -->
                    <ul class="list-unstyled mb-0">
                        <li><a href="index.php" class="mobile-nav-link"><i class="bi bi-house-door me-3"></i>Home</a></li>
                        <li><a href="shop.php" class="mobile-nav-link"><i class="bi bi-grid me-3"></i>All Products</a></li>
                        <li>
                            <a class="mobile-nav-link" data-bs-toggle="collapse" href="#mobileCategories" role="button" aria-expanded="false">
                                <i class="bi bi-list me-3"></i>Categories <i class="bi bi-chevron-down float-end"></i>
                            </a>
                            <div class="collapse ps-4" id="mobileCategories">
                                <a href="shop.php" class="mobile-nav-link sub">All Products</a>
                                <?php foreach($nav_categories as $cat): ?>
                                    <a href="shop.php?category=<?= $cat['id'] ?>" class="mobile-nav-link sub"><?= htmlspecialchars($cat['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </li>
                        <li><a href="#" class="mobile-nav-link"><i class="bi bi-percent me-3"></i>Deals</a></li>
                        <li><a href="#" class="mobile-nav-link"><i class="bi bi-heart me-3"></i>Wishlist</a></li>
                        
                        <?php if (is_logged_in()): ?>
                            <li class="border-top mt-3 pt-3">
                                <a href="dashboard.php" class="mobile-nav-link"><i class="bi bi-person me-3"></i>My Profile</a>
                            </li>
                            <li><a href="orders.php" class="mobile-nav-link"><i class="bi bi-box-seam me-3"></i>My Orders</a></li>
                            <?php if(is_admin()): ?>
                                <li><a href="admin/dashboard.php" class="mobile-nav-link text-primary"><i class="bi bi-gear me-3"></i>Admin Panel</a></li>
                            <?php endif; ?>
                            <li><a href="auth/logout.php" class="mobile-nav-link text-danger"><i class="bi bi-box-arrow-right me-3"></i>Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main>
