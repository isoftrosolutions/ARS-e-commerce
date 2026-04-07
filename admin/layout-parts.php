<?php
// admin/layout-parts.php

function admin_header($title = 'Admin Dashboard', $active_page = 'dashboard') {
    $site_url = SITE_URL;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo SITE_NAME; ?> Admin</title>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="<?php echo $site_url; ?>/assets/css/admin-new.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo $site_url; ?>/admin/dashboard.php" class="logo">
                    <span class="logo-text">ARS</span> Admin
                </a>
            </div>
            <nav class="nav">
                <a href="<?php echo $site_url; ?>/admin/dashboard.php" class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="<?php echo $site_url; ?>/admin/products.php" class="nav-item <?php echo $active_page === 'products' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i data-lucide="shopping-bag"></i></span>
                    <span class="nav-label">Products</span>
                </a>
                <a href="<?php echo $site_url; ?>/admin/categories.php" class="nav-item <?php echo $active_page === 'categories' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i data-lucide="layers"></i></span>
                    <span class="nav-label">Categories</span>
                </a>
                <a href="<?php echo $site_url; ?>/admin/orders.php" class="nav-item <?php echo $active_page === 'orders' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i data-lucide="shopping-cart"></i></span>
                    <span class="nav-label">Orders</span>
                </a>
                <a href="<?php echo $site_url; ?>/admin/customers.php" class="nav-item <?php echo $active_page === 'customers' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i data-lucide="users"></i></span>
                    <span class="nav-label">Customers</span>
                </a>
                <a href="<?php echo $site_url; ?>/admin/settings.php" class="nav-item <?php echo $active_page === 'settings' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i data-lucide="settings"></i></span>
                    <span class="nav-label">Settings</span>
                </a>
                <div style="margin-top: auto; padding-top: 20px;">
                    <a href="<?php echo $site_url; ?>/auth/logout.php" class="nav-item">
                        <span class="nav-icon"><i data-lucide="log-out"></i></span>
                        <span class="nav-label">Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="hamburger-menu" aria-label="Toggle Sidebar">
                        <i data-lucide="menu"></i>
                    </button>
                    <h1 style="font-size: 1.25rem; margin-bottom: 0; font-weight: 600;"><?php echo $title; ?></h1>
                </div>
                <div class="header-right">
                    <button id="theme-toggle" class="btn btn-ghost" aria-label="Toggle Theme">
                        <i data-lucide="moon"></i>
                    </button>
                    <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                        <div class="avatar" style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                            A
                        </div>
                        <span class="user-name" style="font-weight: 500;">Admin</span>
                    </div>
                </div>
            </header>
            <div class="content-body">
    <?php
}

function admin_footer() {
    $site_url = SITE_URL;
    ?>
            </div>
        </main>
    </div>

    <script src="<?php echo $site_url; ?>/assets/js/admin.js"></script>
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
    <?php
}
