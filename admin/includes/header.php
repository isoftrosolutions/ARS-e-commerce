<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Admin Panel' ?> | ARS E-Commerce</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            900: '#1e3a8a',
                        },
                        sidebar: '#0f172a',
                        sidebarActive: '#1e293b',
                        sidebarText: '#94a3b8',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .soft-shadow { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); }
    </style>
</head>
<body class="text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-sidebar text-sidebarText flex flex-col h-full flex-shrink-0 transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-slate-800">
            <a href="dashboard.php" class="text-white font-bold text-xl flex items-center gap-2">
                <i data-lucide="shopping-bag" class="text-brand-500"></i> ARS Shop
            </a>
        </div>

        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 px-3 mt-4">Main Menu</div>
            <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'dashboard.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="package" class="w-5 h-5"></i> Products
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'categories.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="layers" class="w-5 h-5"></i> Categories
            </a>
            <a href="bulk-actions.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'bulk-actions.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="zap" class="w-5 h-5"></i> Bulk Actions
            </a>
            <a href="orders.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= in_array($current_page, ['orders.php', 'order-details.php']) ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i> Orders
            </a>
            <a href="customers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'customers.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="users" class="w-5 h-5"></i> Customers
            </a>
            <a href="analytics.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'analytics.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="line-chart" class="w-5 h-5"></i> Analytics (Beta)
            </a>
            <a href="reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'reports.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="bar-chart-2" class="w-5 h-5"></i> Reports
            </a>
            
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 px-3 mt-6">Settings</div>
            <a href="settings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'settings.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="settings" class="w-5 h-5"></i> Store Settings
            </a>
            <a href="coupons.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?= $current_page == 'coupons.php' ? 'bg-sidebarActive text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="ticket" class="w-5 h-5"></i> Coupons
            </a>
        </div>

        <div class="p-4 border-t border-slate-800 space-y-2">
            <a href="../index.php" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors hover:bg-slate-800 hover:text-white text-sm">
                <i data-lucide="external-link" class="w-4 h-4"></i> View Store
            </a>
            <a href="../auth/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors hover:bg-red-900/30 text-red-400 hover:text-red-300 text-sm">
                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0 z-10">
            <div class="flex items-center gap-4 flex-1">
                <button class="text-slate-500 hover:text-slate-700 lg:hidden focus:outline-none">
                    <i data-lucide="menu"></i>
                </button>
                <!-- Search Bar -->
                <div class="relative w-full max-w-md hidden md:block">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" placeholder="Search orders, products, or customers..." class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 sm:text-sm transition-colors">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Notifications -->
                <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                </button>
                
                <div class="w-px h-6 bg-slate-200"></div>

                <!-- Profile Dropdown -->
                <div class="flex items-center gap-3 cursor-pointer group">
                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold border border-brand-200">
                        <?= substr($_SESSION['user_name'] ?? 'A', 0, 1) ?>
                    </div>
                    <div class="hidden md:block">
                        <div class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin User') ?></div>
                        <div class="text-xs text-slate-500">Administrator</div>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-slate-600"></i>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-6 bg-[#f8fafc]">
