<?php
// admin/layout-parts.php

function admin_header($title = 'Admin Dashboard', $active_page = 'dashboard') {
    // Resolve base URL from SITE_URL constant (set in config/db.php)
    $site_url = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

    $user_name = $_SESSION['user_name'] ?? 'Admin';
    $initial   = strtoupper(substr($user_name, 0, 1));

    // Active nav helper using $active_page parameter
    function nav_active($page, $active) {
        return $page === $active ? 'nav-item active' : 'nav-item';
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> — ARS Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- Chart.js (needed on dashboard, harmless elsewhere) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- ARS Admin CSS — correct path from /admin/ -->
    <link rel="stylesheet" href="../assets/css/admin.css">

    <!-- Apply saved theme before first paint to prevent FOUC -->
    <script>(function(){var t=localStorage.getItem('ars_theme');if(t==='light')document.documentElement.setAttribute('data-theme','light');})()</script>
</head>
<body class="admin">

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<div class="admin-shell">

    <!-- ===== SIDEBAR ===== -->
    <aside class="admin-sidebar" id="admin-sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo">
                <?php if (file_exists(__DIR__ . '/../assets/logo.jpeg')): ?>
                    <img src="../assets/logo.jpeg" alt="ARS Logo">
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <?php endif; ?>
            </div>
            <span class="sidebar-brand-name">ARS Admin</span>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav" aria-label="Admin Navigation">

            <div class="sidebar-section-label">Main</div>

            <a href="<?php echo $site_url; ?>/admin/dashboard.php" class="<?php echo nav_active('dashboard', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
                <span class="nav-item-label">Dashboard</span>
            </a>

            <a href="<?php echo $site_url; ?>/admin/orders.php" class="<?php echo nav_active('orders', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg></span>
                <span class="nav-item-label">Orders</span>
                <?php
                try {
                    if (isset($pdo)) {
                        $pending_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE delivery_status='Pending'")->fetchColumn();
                        if ($pending_count > 0) echo '<span class="nav-badge">' . $pending_count . '</span>';
                    }
                } catch (Exception $e) {}
                ?>
            </a>

            <a href="<?php echo $site_url; ?>/admin/products.php" class="<?php echo nav_active('products', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
                <span class="nav-item-label">Products</span>
            </a>

            <a href="<?php echo $site_url; ?>/admin/customers.php" class="<?php echo nav_active('customers', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></span>
                <span class="nav-item-label">Customers</span>
            </a>

            <a href="<?php echo $site_url; ?>/admin/categories.php" class="<?php echo nav_active('categories', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></span>
                <span class="nav-item-label">Categories</span>
            </a>

            <div class="sidebar-section-label">Commerce</div>

            <a href="<?php echo $site_url; ?>/admin/coupons.php" class="<?php echo nav_active('coupons', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></span>
                <span class="nav-item-label">Coupons</span>
            </a>

            <a href="<?php echo $site_url; ?>/admin/bulk-actions.php" class="<?php echo nav_active('bulk-actions', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></span>
                <span class="nav-item-label">Bulk Actions</span>
            </a>

            <div class="sidebar-section-label">Reports</div>

            <a href="<?php echo $site_url; ?>/admin/analytics.php" class="<?php echo nav_active('analytics', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
                <span class="nav-item-label">Analytics</span>
            </a>

            <a href="<?php echo $site_url; ?>/admin/reports.php" class="<?php echo nav_active('reports', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
                <span class="nav-item-label">Reports</span>
            </a>

            <div class="sidebar-section-label">System</div>

            <a href="<?php echo $site_url; ?>/admin/email-logs.php" class="<?php echo nav_active('email-logs', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                <span class="nav-item-label">Email Logs</span>
            </a>

            <a href="<?php echo $site_url; ?>/admin/settings.php" class="<?php echo nav_active('settings', $active_page); ?>">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></span>
                <span class="nav-item-label">Settings</span>
            </a>

            <a href="<?php echo $site_url; ?>/index.php" target="_blank" class="nav-item" style="margin-top:8px;">
                <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></span>
                <span class="nav-item-label">View Store</span>
            </a>

        </nav><!-- /sidebar-nav -->

        <!-- Footer / User -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?php echo $initial; ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="sidebar-user-role">Administrator</div>
                </div>
                <a href="<?php echo $site_url; ?>/auth/logout.php" class="sidebar-logout" title="Logout">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </div>

    </aside><!-- /admin-sidebar -->


    <!-- ===== MAIN WRAPPER ===== -->
    <div class="admin-main-wrapper">

        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="topbar-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" placeholder="Search products, orders, customers…" aria-label="Global search">
                    <span class="topbar-shortcut">⌘K</span>
                </div>
            </div>

            <div class="topbar-right">
                <!-- Theme toggle -->
                <button class="topbar-btn" id="theme-toggle" title="Switch to light mode" aria-label="Toggle theme">
                    <!-- Sun — shown in dark mode (click to go light) -->
                    <svg class="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <!-- Moon — shown in light mode (click to go dark) -->
                    <svg class="theme-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>

                <button class="topbar-btn" title="Notifications" aria-label="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <span class="topbar-notif-dot"></span>
                </button>

                <div class="topbar-divider"></div>

                <!-- User dropdown -->
                <div class="topbar-user" id="user-dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                    <div class="topbar-avatar"><?php echo $initial; ?></div>
                    <div class="topbar-user-info">
                        <div class="topbar-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="topbar-user-role">Administrator</div>
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </div>

                <!-- User dropdown menu -->
                <div class="dropdown-menu" id="user-dropdown-menu" style="top:52px;right:0;min-width:180px;">
                    <a href="<?php echo $site_url; ?>/admin/settings.php" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                        Settings
                    </a>
                    <a href="<?php echo $site_url; ?>/index.php" target="_blank" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        View Store
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo $site_url; ?>/auth/logout.php" class="dropdown-item danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </div>

            </div><!-- /topbar-right -->
        </header><!-- /admin-topbar -->

        <!-- Topbar spacer -->
        <div class="admin-topbar-spacer"></div>

        <!-- Flash message display -->
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div style="padding:12px 28px 0;">
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_type'] ?? 'info'); ?> flash-alert animate-fade-in">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <div class="alert-body">
                    <div class="alert-text"><?php echo htmlspecialchars($_SESSION['flash_message']); ?></div>
                </div>
            </div>
        </div>
        <?php
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        endif;

        // Legacy session message compat
        if (isset($_SESSION['message'])) {
            $type = $_SESSION['message_type'] ?? 'info';
            $map  = ['success' => 'success', 'danger' => 'danger', 'warning' => 'warning', 'info' => 'info'];
            $cls  = $map[$type] ?? 'info';
            echo '<div style="padding:12px 28px 0;">';
            echo '<div class="alert alert-' . $cls . ' flash-alert animate-fade-in">';
            echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
            echo '<div class="alert-body"><div class="alert-text">' . htmlspecialchars($_SESSION['message']) . '</div></div>';
            echo '</div></div>';
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
        ?>

        <!-- Page Content -->
        <main class="admin-main">
<?php
}

function admin_footer() {
    $site_url = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    ?>
        </main><!-- /admin-main -->
    </div><!-- /admin-main-wrapper -->

</div><!-- /admin-shell -->

<!-- Global Confirm Modal -->
<div id="confirm-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h2 class="modal-title" id="confirm-modal-title">Confirm Action</h2>
            <button class="modal-close" data-modal-close aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="color:var(--text-secondary);font-size:14px;" id="confirm-message">Are you sure you want to proceed? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" data-modal-close>Cancel</button>
            <button class="btn btn-danger" id="confirm-ok">Confirm</button>
        </div>
    </div>
</div>

<!-- ARS Admin JS -->
<script src="<?php echo $site_url; ?>/assets/js/admin.js"></script>

<!-- Reinit Lucide after all content loaded -->
<script>
    if (typeof lucide !== 'undefined') {
        document.addEventListener('DOMContentLoaded', function() { lucide.createIcons(); });
        lucide.createIcons();
    }
    // Compatibility shim: some pages call Modal.open/close, map to openModal/closeModal
    window.Modal = { open: openModal, close: closeModal };
</script>

</body>
</html>
<?php
}
