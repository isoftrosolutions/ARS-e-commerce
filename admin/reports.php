<?php
// admin/reports.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Handle CSV exports
$export = $_GET['export'] ?? '';
if ($export === 'orders') {
    $from = $_GET['from'] ?? date('Y-m-01');
    $to   = $_GET['to']   ?? date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT o.id, u.full_name as customer, u.mobile, o.payment_method, o.payment_status,
               o.delivery_status, o.total_amount, o.created_at
        FROM orders o LEFT JOIN users u ON o.user_id = u.id
        WHERE DATE(o.created_at) BETWEEN ? AND ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$from, $to]);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="orders_' . $from . '_to_' . $to . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['Order ID', 'Customer', 'Mobile', 'Payment Method', 'Payment Status', 'Delivery Status', 'Amount', 'Date']);
    foreach ($rows as $r) {
        fputcsv($fp, [
            '#ARS-' . str_pad($r['id'], 4, '0', STR_PAD_LEFT),
            $r['customer'] ?: 'Guest',
            $r['mobile'] ?? '',
            $r['payment_method'],
            $r['payment_status'],
            $r['delivery_status'],
            $r['total_amount'],
            $r['created_at'],
        ]);
    }
    fclose($fp);
    exit;
}

if ($export === 'products') {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.sku, c.name as category, p.price, p.discount_price, p.stock, p.is_featured, p.created_at
        FROM products p LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.name ASC
    ");
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['ID', 'Name', 'SKU', 'Category', 'Price', 'Discount Price', 'Stock', 'Featured', 'Created']);
    foreach ($rows as $r) {
        fputcsv($fp, [$r['id'], $r['name'], $r['sku'], $r['category'], $r['price'], $r['discount_price'], $r['stock'], $r['is_featured'] ? 'Yes' : 'No', $r['created_at']]);
    }
    fclose($fp);
    exit;
}

if ($export === 'customers') {
    $stmt = $pdo->query("
        SELECT u.id, u.full_name, u.mobile, u.email, u.address,
               COUNT(o.id) as orders, COALESCE(SUM(o.total_amount),0) as total_spent, u.created_at
        FROM users u LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.role = 'customer' GROUP BY u.id ORDER BY u.full_name ASC
    ");
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['ID', 'Name', 'Mobile', 'Email', 'Address', 'Orders', 'Total Spent', 'Joined']);
    foreach ($rows as $r) {
        fputcsv($fp, [$r['id'], $r['full_name'], $r['mobile'], $r['email'], $r['address'], $r['orders'], $r['total_spent'], $r['created_at']]);
    }
    fclose($fp);
    exit;
}

// Summary stats for the reports page
try {
    $stats = [
        'total_revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='Paid'")->fetchColumn(),
        'total_orders'  => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'total_prods'   => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'total_custs'   => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
    ];
    $from_default = date('Y-m-01');
    $to_default   = date('Y-m-d');
} catch (PDOException $e) {
    $stats = ['total_revenue' => 0, 'total_orders' => 0, 'total_prods' => 0, 'total_custs' => 0];
    $from_default = $to_default = date('Y-m-d');
}

admin_header('Reports', 'reports');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle">Export data and generate reports</p>
    </div>
</div>

<!-- Quick Stats -->
<div class="kpi-grid" style="margin-bottom:28px;">
    <div class="kpi-card kpi-revenue">
        <div class="kpi-top"><div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div></div>
        <div class="kpi-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
        <div class="kpi-label">All-time Revenue</div>
    </div>
    <div class="kpi-card kpi-orders">
        <div class="kpi-top"><div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div></div>
        <div class="kpi-value"><?php echo number_format($stats['total_orders']); ?></div>
        <div class="kpi-label">Total Orders</div>
    </div>
    <div class="kpi-card kpi-products">
        <div class="kpi-top"><div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div></div>
        <div class="kpi-value"><?php echo number_format($stats['total_prods']); ?></div>
        <div class="kpi-label">Products Listed</div>
    </div>
    <div class="kpi-card kpi-customers">
        <div class="kpi-top"><div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div></div>
        <div class="kpi-value"><?php echo number_format($stats['total_custs']); ?></div>
        <div class="kpi-label">Customers</div>
    </div>
</div>

<!-- Export Cards -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">

    <!-- Orders Export -->
    <div class="card card-body">
        <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--accent-dim);color:var(--accent);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        </div>
        <h3 style="font-size:15px;font-weight:700;margin-bottom:6px;">Orders Report</h3>
        <p style="font-size:12.5px;color:var(--text-secondary);margin-bottom:18px;line-height:1.5;">Export all orders within a date range as CSV.</p>
        <form method="GET" action="reports.php">
            <input type="hidden" name="export" value="orders">
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-input" value="<?php echo $from_default; ?>">
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-input" value="<?php echo $to_default; ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Orders CSV
            </button>
        </form>
    </div>

    <!-- Products Export -->
    <div class="card card-body">
        <div style="width:44px;height:44px;border-radius:var(--r-md);background:var(--warning-dim);color:var(--warning);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <h3 style="font-size:15px;font-weight:700;margin-bottom:6px;">Products Report</h3>
        <p style="font-size:12.5px;color:var(--text-secondary);margin-bottom:18px;line-height:1.5;">Export your full product catalog with pricing and stock info.</p>
        <a href="?export=products" class="btn btn-primary" style="width:100%;display:flex;margin-top:auto;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Products CSV
        </a>
    </div>

    <!-- Customers Export -->
    <div class="card card-body">
        <div style="width:44px;height:44px;border-radius:var(--r-md);background:rgba(167,139,250,0.1);color:#A78BFA;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <h3 style="font-size:15px;font-weight:700;margin-bottom:6px;">Customers Report</h3>
        <p style="font-size:12.5px;color:var(--text-secondary);margin-bottom:18px;line-height:1.5;">Export your full customer list with contact and order history.</p>
        <a href="?export=customers" class="btn btn-primary" style="width:100%;display:flex;margin-top:auto;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Customers CSV
        </a>
    </div>

</div>

<?php admin_footer(); ?>
