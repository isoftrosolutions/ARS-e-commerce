<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_page = basename($_SERVER['PHP_SELF']);

// Nav helper
function nav_class(array $pages): string {
    global $current_page;
    return in_array($current_page, $pages) ? 'nav-item active' : 'nav-item';
}

$admin_name = htmlspecialchars($_SESSION['user_name'] ?? 'Admin');
$admin_initial = strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ARS Admin' : 'ARS Admin' ?></title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <!-- Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- ARS Admin CSS -->
  <link rel="stylesheet" href="../assets/css/admin.css">
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
        <?php if (file_exists(__DIR__ . '/../../assets/logo.jpeg')): ?>
          <img src="../assets/logo.jpeg" alt="ARS Logo">
        <?php else: ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00D4FF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <?php endif; ?>
      </div>
      <span class="sidebar-brand-name">ARS Shop</span>
      <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav" aria-label="Admin Navigation">

      <div class="sidebar-section-label">Main</div>

      <a href="dashboard.php" class="<?= nav_class(['dashboard.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
        <span class="nav-item-label">Dashboard</span>
      </a>

      <a href="orders.php" class="<?= nav_class(['orders.php','order-details.php']) ?>">
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

      <a href="products.php" class="<?= nav_class(['products.php','product-add.php','product-edit.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
        <span class="nav-item-label">Products</span>
      </a>

      <a href="customers.php" class="<?= nav_class(['customers.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></span>
        <span class="nav-item-label">Customers</span>
      </a>

      <a href="categories.php" class="<?= nav_class(['categories.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></span>
        <span class="nav-item-label">Categories</span>
      </a>

      <div class="sidebar-section-label">Commerce</div>

      <a href="coupons.php" class="<?= nav_class(['coupons.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></span>
        <span class="nav-item-label">Coupons</span>
      </a>

      <a href="bulk-actions.php" class="<?= nav_class(['bulk-actions.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></span>
        <span class="nav-item-label">Bulk Actions</span>
      </a>

      <div class="sidebar-section-label">Reports</div>

      <a href="analytics.php" class="<?= nav_class(['analytics.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
        <span class="nav-item-label">Analytics</span>
      </a>

      <a href="reports.php" class="<?= nav_class(['reports.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span>
        <span class="nav-item-label">Reports</span>
      </a>

      <div class="sidebar-section-label">System</div>

      <a href="settings.php" class="<?= nav_class(['settings.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></span>
        <span class="nav-item-label">Settings</span>
      </a>

      <a href="email-logs.php" class="<?= nav_class(['email-logs.php']) ?>">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
        <span class="nav-item-label">Email Logs</span>
      </a>

      <a href="../index.php" target="_blank" class="nav-item" style="margin-top:8px;">
        <span class="nav-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></span>
        <span class="nav-item-label">View Store</span>
      </a>

    </nav><!-- /sidebar-nav -->

    <!-- Footer / User -->
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?= $admin_initial ?></div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name"><?= $admin_name ?></div>
          <div class="sidebar-user-role">Administrator</div>
        </div>
        <a href="../auth/logout.php" class="sidebar-logout" title="Logout">
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
        <!-- Notifications -->
        <button class="topbar-btn" title="Notifications" aria-label="Notifications">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
          <span class="topbar-notif-dot"></span>
        </button>

        <div class="topbar-divider"></div>

        <!-- User dropdown -->
        <div class="topbar-user" id="user-dropdown-trigger" aria-haspopup="true" aria-expanded="false">
          <div class="topbar-avatar"><?= $admin_initial ?></div>
          <div class="topbar-user-info">
            <div class="topbar-user-name"><?= $admin_name ?></div>
            <div class="topbar-user-role">Administrator</div>
          </div>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>

        <!-- User dropdown menu -->
        <div class="dropdown-menu" id="user-dropdown-menu" style="top:52px;right:0;min-width:180px;">
          <a href="settings.php" class="dropdown-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Settings
          </a>
          <a href="../index.php" target="_blank" class="dropdown-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            View Store
          </a>
          <div class="dropdown-divider"></div>
          <a href="../auth/logout.php" class="dropdown-item danger">
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
      <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> flash-alert animate-fade-in">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <div class="alert-body">
          <div class="alert-text"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
        </div>
      </div>
    </div>
    <?php
      unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    endif;
    ?>

    <!-- Page Content -->
    <main class="admin-main">
<?php
// Helper: display_message() compatibility
function display_admin_message(): void {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $map  = ['success' => 'success', 'danger' => 'danger', 'warning' => 'warning', 'info' => 'info'];
        $cls  = $map[$type] ?? 'info';
        echo '<div class="alert alert-' . $cls . ' flash-alert animate-fade-in mb-4">';
        echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:17px;height:17px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
        echo '<div class="alert-body"><div class="alert-text">' . htmlspecialchars($_SESSION['message']) . '</div></div>';
        echo '</div>';
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
}
display_admin_message();
?>
