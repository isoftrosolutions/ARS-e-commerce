<?php
require_once __DIR__ . '/auth_check.php';

try {
    $total_revenue   = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='Paid'")->fetchColumn();
    $total_orders    = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pending_orders  = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE delivery_status='Pending'")->fetchColumn();
    $total_products  = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $low_stock       = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();
    $total_customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();

    $recent_orders = $pdo->query(
        "SELECT o.*, u.full_name as customer_name
         FROM orders o LEFT JOIN users u ON o.user_id = u.id
         ORDER BY o.created_at DESC LIMIT 6"
    )->fetchAll();

    $chart_labels = []; $chart_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $chart_labels[] = date('D', strtotime($d));
        $s = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at)=? AND payment_status='Paid'");
        $s->execute([$d]);
        $chart_data[] = (float)$s->fetchColumn();
    }

    $top_products = $pdo->query(
        "SELECT p.name, p.image, COALESCE(SUM(oi.quantity),0) as sold
         FROM products p LEFT JOIN order_items oi ON p.id=oi.product_id
         GROUP BY p.id ORDER BY sold DESC LIMIT 5"
    )->fetchAll();

} catch (PDOException $e) {
    die('Dashboard error: ' . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/layout-parts.php';
admin_header('Dashboard', 'dashboard');
?>

<div class="page-header animate-fade-up">
  <div>
    <h1 class="page-title">Executive Overview</h1>
    <p class="page-subtitle">Welcome back, <strong style="color:var(--accent)"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></strong>. Here's your store at a glance.</p>
  </div>
  <div class="page-actions">
    <a href="orders.php" class="btn btn-ghost">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
      All Orders
    </a>
    <a href="product-add.php" class="btn btn-primary">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Product
    </a>
  </div>
</div>

<?php if ($low_stock > 0): ?>
<div class="alert alert-warning animate-fade-up mb-4">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
  <div class="alert-body">
    <div class="alert-title">Inventory Alert</div>
    <div class="alert-text"><?= $low_stock ?> product<?= $low_stock > 1 ? 's are' : ' is' ?> running low on stock.
      <a href="products.php" style="color:var(--warning);text-decoration:underline;margin-left:4px;">Manage inventory →</a>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- KPI Cards -->
<div class="kpi-grid">

  <div class="kpi-card kpi-revenue animate-fade-up stagger-1">
    <div class="kpi-top">
      <div class="kpi-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      </div>
      <span class="kpi-trend up">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        +12.4%
      </span>
    </div>
    <div class="kpi-value">Rs.<?= number_format($total_revenue, 0) ?></div>
    <div class="kpi-label">Total Revenue (Paid)</div>
  </div>

  <div class="kpi-card kpi-orders animate-fade-up stagger-2">
    <div class="kpi-top">
      <div class="kpi-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      </div>
      <span class="kpi-trend up">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        +8.1%
      </span>
    </div>
    <div class="kpi-value"><?= number_format($total_orders) ?></div>
    <div class="kpi-label">Total Orders &nbsp;<span class="badge badge-warning" style="font-size:10px;padding:2px 7px;"><?= $pending_orders ?> pending</span></div>
  </div>

  <div class="kpi-card kpi-products animate-fade-up stagger-3">
    <div class="kpi-top">
      <div class="kpi-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <span class="kpi-trend down">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
        -2.0%
      </span>
    </div>
    <div class="kpi-value"><?= number_format($total_products) ?></div>
    <div class="kpi-label">Total Products</div>
  </div>

  <div class="kpi-card kpi-customers animate-fade-up stagger-4">
    <div class="kpi-top">
      <div class="kpi-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
      <span class="kpi-trend up">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        +5.7%
      </span>
    </div>
    <div class="kpi-value"><?= number_format($total_customers) ?></div>
    <div class="kpi-label">Total Customers</div>
  </div>

</div>

<!-- Chart + Top Products -->
<div class="section-grid animate-fade-up stagger-2" style="margin-bottom:24px;">

  <div class="card">
    <div class="card-header">
      <span class="card-title">Revenue Dynamics</span>
      <span style="font-size:12px;color:var(--text-muted);">Last 7 days</span>
    </div>
    <div class="card-body" style="padding-top:14px;">
      <div style="position:relative;height:220px;">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Top Performers</span>
      <a href="products.php" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div>
      <?php if (empty($top_products)): ?>
        <div class="empty-state" style="padding:24px 20px;">
          <div class="empty-title">No sales data yet</div>
        </div>
      <?php else: ?>
        <?php foreach ($top_products as $i => $p): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-bottom:1px solid var(--border);">
          <?php if ($p['image']): ?>
            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" style="width:36px;height:36px;border-radius:var(--r-md);object-fit:cover;background:var(--bg-card);flex-shrink:0;" alt="">
          <?php else: ?>
            <div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--bg-card);display:flex;align-items:center;justify-content:center;color:var(--text-muted);flex-shrink:0;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
          <?php endif; ?>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['name']) ?></div>
            <div style="font-size:11px;color:var(--text-muted);"><?= (int)$p['sold'] ?> sold</div>
          </div>
          <span class="badge badge-muted" style="font-size:10px;">#<?= $i + 1 ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Recent Orders -->
<div class="card animate-fade-up stagger-3">
  <div class="card-header">
    <span class="card-title">Recent Orders</span>
    <a href="orders.php" class="btn btn-ghost btn-sm">View All</a>
  </div>

  <?php if (empty($recent_orders)): ?>
    <div class="empty-state">
      <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px;"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
      <div class="empty-title">No orders yet</div>
      <div class="empty-text">Orders will appear here once customers start shopping.</div>
    </div>
  <?php else: ?>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Payment</th>
          <th>Delivery</th>
          <th>Amount</th>
          <th>Date</th>
          <th class="col-actions">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $pay_map = ['Paid'=>'badge-success','Pending'=>'badge-warning','Failed'=>'badge-danger'];
        $del_map = ['Pending'=>'badge-warning','Confirmed'=>'badge-info','Shipped'=>'badge-orange','Delivered'=>'badge-success','Cancelled'=>'badge-danger'];
        foreach ($recent_orders as $o):
            $pay_cls = $pay_map[$o['payment_status']] ?? 'badge-muted';
            $del_cls = $del_map[$o['delivery_status']] ?? 'badge-muted';
            $cname   = htmlspecialchars($o['customer_name'] ?: 'Guest');
        ?>
        <tr>
          <td><span class="order-id">#ARS-<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span></td>
          <td style="font-weight:500;font-size:13.5px;"><?= $cname ?></td>
          <td><span class="badge <?= $pay_cls ?>"><span class="badge-dot"></span><?= htmlspecialchars($o['payment_status']) ?></span></td>
          <td><span class="badge <?= $del_cls ?>"><span class="badge-dot"></span><?= htmlspecialchars($o['delivery_status']) ?></span></td>
          <td style="font-family:var(--font-mono);font-weight:600;">Rs.<?= number_format($o['total_amount'], 2) ?></td>
          <td style="color:var(--text-secondary);font-size:12.5px;"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
          <td class="col-actions">
            <a href="order-details.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="View">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="section-grid-equal animate-fade-up stagger-4" style="margin-top:24px;">
  <a href="product-add.php" class="card" style="padding:20px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:all var(--t-fast);">
    <div style="width:40px;height:40px;border-radius:var(--r-md);background:var(--accent-dim);color:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </div>
    <div><div style="font-weight:600;font-size:13.5px;">Add New Product</div><div style="font-size:12px;color:var(--text-muted);">List a new item in your store</div></div>
  </a>

  <a href="categories.php" class="card" style="padding:20px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:all var(--t-fast);">
    <div style="width:40px;height:40px;border-radius:var(--r-md);background:var(--warning-dim);color:var(--warning);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
    </div>
    <div><div style="font-weight:600;font-size:13.5px;">Manage Categories</div><div style="font-size:12px;color:var(--text-muted);">Organize your product catalog</div></div>
  </a>

  <a href="orders.php" class="card" style="padding:20px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:all var(--t-fast);">
    <div style="width:40px;height:40px;border-radius:var(--r-md);background:rgba(167,139,250,0.1);color:#A78BFA;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
    </div>
    <div><div style="font-weight:600;font-size:13.5px;">Process Orders</div><div style="font-size:12px;color:var(--text-muted);"><?= $pending_orders ?> orders awaiting action</div></div>
  </a>

  <a href="settings.php" class="card" style="padding:20px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:all var(--t-fast);">
    <div style="width:40px;height:40px;border-radius:var(--r-md);background:var(--success-dim);color:var(--success);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
    </div>
    <div><div style="font-weight:600;font-size:13.5px;">Store Settings</div><div style="font-size:12px;color:var(--text-muted);">Configure your platform</div></div>
  </a>
</div>

<script>
(function() {
  var _chart = null;
  var labels  = <?= json_encode($chart_labels) ?>;
  var data    = <?= json_encode($chart_data) ?>;

  function build() {
    var canvas = document.getElementById('revenueChart');
    if (!canvas || typeof Chart === 'undefined') return;
    if (_chart) { _chart.destroy(); _chart = null; }

    var c = (typeof ThemeManager !== 'undefined') ? ThemeManager.chartColors() : {};
    var line        = c.line        || '#00D4FF';
    var fillStart   = c.fillStart   || 'rgba(0,212,255,0.15)';
    var fillEnd     = c.fillEnd     || 'rgba(0,212,255,0.01)';
    var grid        = c.grid        || 'rgba(255,255,255,0.05)';
    var tick        = c.tick        || '#4E5569';
    var pointBg     = c.pointBg     || '#0F1117';
    var tipBg       = c.tooltipBg   || '#242836';
    var tipTitle    = c.tooltipTitle|| 'rgba(255,255,255,0.87)';
    var tipBody     = c.tooltipBody || '#8B92A5';
    var tipBorder   = c.tooltipBorder || 'rgba(0,212,255,0.22)';

    _chart = new Chart(canvas, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Revenue',
          data: data,
          borderColor: line,
          backgroundColor: function(ctx2) {
            var ch = ctx2.chart, area = ch.chartArea;
            if (!area) return 'transparent';
            var g = ch.ctx.createLinearGradient(0, area.top, 0, area.bottom);
            g.addColorStop(0, fillStart);
            g.addColorStop(1, fillEnd);
            return g;
          },
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointBackgroundColor: pointBg,
          pointBorderColor: line,
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: tipBg,
            titleColor: tipTitle,
            bodyColor: tipBody,
            borderColor: tipBorder,
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
              label: function(ctx3) { return 'Revenue: Rs.' + ctx3.parsed.y.toLocaleString(); }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: grid, drawBorder: false },
            ticks: { color: tick, font: { size: 11, family: "'JetBrains Mono', monospace" }, callback: function(v) { return 'Rs.' + v.toLocaleString(); } },
            border: { display: false }
          },
          x: {
            grid: { display: false },
            ticks: { color: tick, font: { size: 11 } },
            border: { display: false }
          }
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', build);
  document.addEventListener('ars:themechange', build);
})();
</script>

<?php admin_footer(); ?>
