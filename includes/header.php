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
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316', // Orange-ish primary
                            600: '#ea580c',
                            700: '#c2410c',
                            900: '#7c2d12',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .nav-link-active { color: #f97316; font-weight: 700; border-bottom: 2px solid #f97316; }
        .soft-shadow { box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05); }
        /* Smooth transitions */
        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">

    <!-- Announcement Bar -->
    <div class="bg-brand-900 text-white py-2 px-4 text-center text-xs font-bold tracking-wide uppercase">
        🚀 FREE DELIVERY ON ORDERS OVER RS. 1,000 | USE CODE: <span class="text-brand-500">ARS2026</span>
    </div>

    <!-- Main Navigation -->
    <header class="bg-white sticky top-0 z-50 soft-shadow border-b border-slate-100">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex items-center justify-between h-16 md:h-20 gap-4">
                
                <!-- Logo -->
                <a href="index.php" class="flex items-center gap-2 flex-shrink-0 group">
                    <div class="w-10 h-10 bg-brand-600 text-white rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                        <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                    </div>
                    <span class="text-xl font-extrabold tracking-tighter text-slate-900 hidden sm:block">ARS<span class="text-brand-600">SHOP</span></span>
                </a>

                <!-- Desktop Search -->
                <div class="hidden md:flex flex-1 max-w-xl">
                    <form action="shop.php" method="GET" class="relative w-full group">
                        <input type="text" name="q" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" 
                               placeholder="Search for electronics, fashion, home..." 
                               class="w-full pl-12 pr-4 py-2.5 bg-slate-100 border-none rounded-2xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:bg-white transition-all outline-none">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 transition-colors group-focus-within:text-brand-500"></i>
                        <button type="submit" class="hidden"></button>
                    </form>
                </div>

                <!-- Icons & Auth -->
                <div class="flex items-center gap-2 md:gap-5">
                    
                    <!-- Search Icon (Mobile) -->
                    <button class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                        <i data-lucide="search" class="w-6 h-6"></i>
                    </button>

                    <a href="wishlist.php" class="p-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-colors relative group">
                        <i data-lucide="heart" class="w-6 h-6"></i>
                        <!-- Wishlist count could be added here -->
                    </a>

                    <a href="cart.php" class="p-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-full transition-colors relative group">
                        <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                        <?php if (get_cart_count() > 0): ?>
                            <span class="absolute top-1 right-1 w-5 h-5 bg-brand-600 text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-white"><?= get_cart_count() ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                    <?php if (is_logged_in()): ?>
                        <div class="relative group">
                            <button class="flex items-center gap-2 p-1.5 hover:bg-slate-100 rounded-xl transition-colors">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600 uppercase border border-slate-300">
                                    <?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
                                </div>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                            </button>
                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-2xl soft-shadow border border-slate-100 py-2 hidden group-hover:block animate-in fade-in zoom-in duration-200">
                                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                                    <i data-lucide="user" class="w-4 h-4"></i> My Profile
                                </a>
                                <a href="orders.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                                    <i data-lucide="package" class="w-4 h-4"></i> My Orders
                                </a>
                                <?php if(is_admin()): ?>
                                    <a href="admin/dashboard.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-brand-600 font-bold hover:bg-brand-50 transition-colors">
                                        <i data-lucide="settings" class="w-4 h-4"></i> Admin Panel
                                    </a>
                                <?php endif; ?>
                                <hr class="my-2 border-slate-100">
                                <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="auth/login.php" class="hidden sm:flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition-all hover:shadow-lg hover:shadow-slate-200">
                            Login
                        </a>
                        <a href="auth/login.php" class="sm:hidden p-2 text-slate-600">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mega Menu / Nav Categories -->
            <nav class="hidden md:flex items-center gap-8 py-2 overflow-x-auto no-scrollbar">
                <a href="index.php" class="text-sm font-semibold whitespace-nowrap transition-colors <?= $current_page == 'index.php' ? 'text-brand-600 border-b-2 border-brand-600' : 'text-slate-500 hover:text-brand-600' ?> py-2">Home</a>
                <a href="shop.php" class="text-sm font-semibold whitespace-nowrap transition-colors <?= $current_page == 'shop.php' && !isset($_GET['category']) ? 'text-brand-600 border-b-2 border-brand-600' : 'text-slate-500 hover:text-brand-600' ?> py-2">All Products</a>
                <?php foreach($nav_categories as $n_cat): ?>
                    <a href="shop.php?category=<?= $n_cat['id'] ?>" class="text-sm font-semibold whitespace-nowrap transition-colors <?= isset($_GET['category']) && $_GET['category'] == $n_cat['id'] ? 'text-brand-600 border-b-2 border-brand-600' : 'text-slate-500 hover:text-brand-600' ?> py-2">
                        <?= htmlspecialchars($n_cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <main class="flex-grow">
