<?php
require_once __DIR__ . '/functions.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch categories for mega menu
try {
    $stmt_cats = $pdo->query("SELECT * FROM categories LIMIT 6");
    $nav_categories = $stmt_cats->fetchAll();
} catch (PDOException $e) {
    $nav_categories = [];
}
$_base_url     = rtrim(SITE_URL, '/');
$_asset_url    = $_base_url . '/assets';
$_seo_title    = isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME;
$_seo_desc     = isset($page_meta_desc) ? $page_meta_desc : 'Easy Shopping A.R.S — Your trusted online shopping destination in Nepal. Quality products, fast delivery across Birgunj, Parsa and all of Nepal.';
$_seo_canonical= isset($page_canonical) ? $page_canonical : $_base_url . '/ARS' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_seo_image    = isset($page_og_image) ? $page_og_image : $_asset_url . '/logo.jpeg';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
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
    <link rel="manifest" href="/ARS/manifest.json">
    <meta name="theme-color" content="#1a0f0a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ARS Shop">
    <link rel="apple-touch-icon" href="/ARS/assets/logo.jpeg">

    <!-- Google Fonts: Inter with font-display swap -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap&family=Inter:wght@300&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    </noscript>
    
    <!-- Lucide Icons (from fast CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>

    <!-- ARS Custom CSS -->
    <link rel="stylesheet" href="assets/css/base.css">
    <?php 
    $page_name = str_replace('.php', '', basename($_SERVER['PHP_SELF']));
    $css_file = "assets/css/{$page_name}.css";
    if (file_exists(__DIR__ . '/../' . $css_file)): ?>
        <link rel="stylesheet" href="<?= $css_file ?>">
    <?php endif; ?>

    <style>
        /* Tailwind CSS bridges */
        .container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; }
        @media (min-width: 768px) { .container { padding: 0 1.5rem; } }
        .bg-brand-600 { background-color: var(--brand-600); }
        .bg-brand-900 { background-color: var(--brand-900); }
        .text-brand-600 { color: var(--brand-600); }
        .text-brand-500 { color: var(--brand-500); }
        .border-brand-600 { border-color: var(--brand-600); }
    </style>

    <!-- Global Schema -->
    <?php require_once __DIR__ . '/schema.php'; ?>
    <?php if (isset($page_schema)): ?>
        <?= $page_schema ?>
    <?php endif; ?>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased">

    <!-- Announcement Bar - Hide on scroll -->
    <div id="announcement-bar" class="announcement-bar text-white py-2 px-4 text-center text-xs font-semibold tracking-wide">
        <span class="hidden sm:inline">🚀 FREE DELIVERY ON ORDERS OVER RS. 1,000 | </span>USE CODE: <span class="text-brand-500 font-bold">ARS2026</span>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-50 hidden opacity-0 transition-opacity duration-300" onclick="closeMobileMenu()"></div>
    
    <!-- Mobile Menu Drawer -->
        <div id="mobile-menu" class="fixed top-0 left-0 h-full w-80 max-w-[85vw] bg-white z-50 transform -translate-x-full transition-transform duration-300 shadow-2xl">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <a href="index.php" class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center border border-slate-200 overflow-hidden">
                        <img src="assets/logo.jpeg" alt="ARS Shop Logo" class="w-full h-full object-contain p-1">
                    </div>
                    <span class="text-xl font-extrabold tracking-tighter text-slate-900">Easy Shopping<span class="text-brand-600"> A.R.S</span></span>
                </a>
                <button onclick="closeMobileMenu()" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Mobile Search -->
        <form action="shop.php" method="GET" class="p-4">
            <div class="relative">
                <input type="text" name="q" placeholder="Search products..." 
                       class="w-full pl-10 pr-4 py-3 bg-slate-100 rounded-2xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:bg-white transition-all outline-none">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
            </div>
        </form>
        
        <!-- Mobile Navigation -->
        <nav class="p-4 space-y-1">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?= $current_page == 'index.php' ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50' ?> transition-colors">
                <i data-lucide="home" class="w-5 h-5"></i> Home
            </a>
            <a href="shop.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?= $current_page == 'shop.php' && !isset($_GET['category']) ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50' ?> transition-colors">
                <i data-lucide="grid-3x3" class="w-5 h-5"></i> All Products
            </a>
            <div class="border-t border-slate-100 my-3"></div>
            <p class="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Categories</p>
            <?php foreach($nav_categories as $n_cat): ?>
                <a href="shop.php?category=<?= $n_cat['id'] ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium <?= isset($_GET['category']) && $_GET['category'] == $n_cat['id'] ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-slate-50' ?> transition-colors">
                    <i data-lucide="tag" class="w-5 h-5"></i> <?= htmlspecialchars($n_cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-100 bg-white">
            <?php if (is_logged_in()): ?>
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 mb-2">
                    <i data-lucide="user" class="w-5 h-5"></i> My Profile
                </a>
                <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50">
                    <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="block w-full py-3 bg-brand-600 text-white text-center rounded-xl text-sm font-bold hover:bg-brand-700 transition-colors">
                    Login / Sign Up
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Navigation -->
    <header id="main-header" class="main-header sticky top-0 z-40 border-b border-slate-100/80">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex items-center justify-between h-16 md:h-[72px] gap-3 md:gap-4">
                
                <!-- Mobile Menu Button -->
                <button onclick="openMobileMenu()" class="md:hidden header-icon -ml-2" aria-label="Open menu">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <!-- Logo -->
                <a href="index.php" class="header-logo flex items-center gap-2.5 flex-shrink-0">
                    <div class="w-10 h-10 md:w-11 md:h-11 bg-white rounded-xl flex items-center justify-center border border-slate-200 overflow-hidden">
                        <img src="assets/logo.jpeg" alt="ARS Shop Logo" class="w-full h-full object-contain p-1">
                    </div>
                    <span class="text-lg md:text-xl font-bold tracking-tight text-slate-900 hidden sm:block">
                        ARS<span class="text-brand-600">SHOP</span>
                    </span>
                </a>

                <!-- Desktop Search -->
                <div class="hidden lg:flex flex-1 max-w-xl">
                    <form action="shop.php" method="GET" class="relative w-full group">
                        <input type="text" name="q" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" 
                               placeholder="Search for electronics, fashion, home..." 
                               class="w-full pl-12 pr-4 py-3 bg-slate-100 border-2 border-transparent rounded-2xl text-sm focus:bg-white focus:border-brand-500/30 transition-all outline-none">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 transition-colors group-focus-within:text-brand-600"></i>
                        <button type="submit" class="hidden"></button>
                    </form>
                </div>

                <!-- Icons & Auth -->
                <div class="flex items-center gap-1 md:gap-2 lg:gap-3">
                    
                    <!-- Search Icon (Mobile) -->
                    <button onclick="openMobileMenu(); setTimeout(() => document.querySelector('#mobile-menu input[name=q]')?.focus(), 100);" class="md:hidden header-icon" aria-label="Search">
                        <i data-lucide="search" class="w-6 h-6"></i>
                    </button>

                    <a href="wishlist.php" class="header-icon" aria-label="Wishlist">
                        <i data-lucide="heart" class="w-5 h-5 md:w-6 md:h-6"></i>
                    </a>

                    <a href="cart.php" class="header-icon relative" aria-label="Shopping cart">
                        <i data-lucide="shopping-cart" class="w-5 h-5 md:w-6 md:h-6"></i>
                        <?php if (get_cart_count() > 0): ?>
                            <span class="cart-badge"><?= get_cart_count() ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="h-6 w-px bg-slate-200 hidden md:block"></div>

                    <?php if (is_logged_in()): ?>
                        <div class="relative group hidden md:block">
                            <button class="flex items-center gap-2 p-2 hover:bg-slate-100 rounded-xl transition-colors">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center text-xs font-bold text-brand-700">
                                    <?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
                                </div>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                            </button>
                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100/50 py-2 hidden group-hover:block animate-in fade-in zoom-in duration-200">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
                                    <p class="text-xs text-slate-500"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                                </div>
                                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                                    <i data-lucide="user" class="w-4 h-4"></i> My Profile
                                </a>
                                <a href="orders.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                                    <i data-lucide="package" class="w-4 h-4"></i> My Orders
                                </a>
                                <?php if(is_admin()): ?>
                                    <a href="admin/dashboard.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-brand-600 font-semibold hover:bg-brand-50 transition-colors">
                                        <i data-lucide="settings" class="w-4 h-4"></i> Admin Panel
                                    </a>
                                <?php endif; ?>
                                <div class="border-t border-slate-100 mt-2 pt-2">
                                    <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="auth/login.php" class="hidden md:flex items-center gap-2 px-4 md:px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mega Menu / Nav Categories (Desktop only) -->
            <nav class="hidden lg:flex items-center gap-1 py-1.5 overflow-x-auto no-scrollbar">
                <a href="index.php" class="nav-link whitespace-nowrap px-4 py-2 text-sm <?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a>
                <a href="shop.php" class="nav-link whitespace-nowrap px-4 py-2 text-sm <?= $current_page == 'shop.php' && !isset($_GET['category']) ? 'active' : '' ?>">All Products</a>
                <?php foreach($nav_categories as $n_cat): ?>
                    <a href="shop.php?category=<?= $n_cat['id'] ?>" class="nav-link whitespace-nowrap px-4 py-2 text-sm <?= isset($_GET['category']) && $_GET['category'] == $n_cat['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($n_cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <main class="flex-grow">
