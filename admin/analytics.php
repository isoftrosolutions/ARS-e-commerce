<?php
// admin/analytics.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

$range = isset($_GET['range']) ? (int)$_GET['range'] : 30;
if (!in_array($range, [7, 30, 90])) $range = 30;

try {
    // Revenue over time
    $rev_labels = [];
    $rev_data   = [];
    $ord_data   = [];

    if ($range <= 30) {
        for ($i = $range - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $rev_labels[] = date($range <= 7 ? 'D' : 'M j', strtotime($d));
            $s = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at)=? AND payment_status='Paid'");
            $s->execute([$d]); $rev_data[] = (float)$s->fetchColumn();
            $s2 = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=?");
            $s2->execute([$d]); $ord_data[] = (int)$s2->fetchColumn();
        }
    } else {
        for ($i = 11; $i >= 0; $i--) {
            $d = date('Y-m', strtotime("-{$i} months"));
            $rev_labels[] = date('M Y', strtotime($d));
            $s = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')=? AND payment_status='Paid'");
            $s->execute([$d]); $rev_data[] = (float)$s->fetchColumn();
            $s2 = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')=?");
            $s2->execute([$d]); $ord_data[] = (int)$s2->fetchColumn();
        }
    }

    // Order status breakdown
    $status_breakdown = $pdo->query("
        SELECT delivery_status, COUNT(*) as cnt
        FROM orders GROUP BY delivery_status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Payment method breakdown
    $payment_breakdown = $pdo->query("
        SELECT payment_method, COUNT(*) as cnt
        FROM orders GROUP BY payment_method
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Top categories by revenue
    $top_cats = $pdo->query("
        SELECT c.name, COALESCE(SUM(oi.price * oi.quantity),0) as revenue
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.payment_status = 'Paid'
        GROUP BY c.id ORDER BY revenue DESC LIMIT 6
    ")->fetchAll();

    // KPIs
    $total_rev  = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='Paid'")->fetchColumn();
    $total_ord  = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_cust = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
    $avg_order  = $total_ord > 0 ? $total_rev / max(1, (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status='Paid'")->fetchColumn()) : 0;

    // Top products
    $top_prods = $pdo->query("
        SELECT p.name, COALESCE(SUM(oi.quantity),0) as units, COALESCE(SUM(oi.price*oi.quantity),0) as revenue
        FROM products p LEFT JOIN order_items oi ON p.id=oi.product_id
        LEFT JOIN orders o ON oi.order_id=o.id AND o.payment_status='Paid'
        GROUP BY p.id ORDER BY revenue DESC LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    die('Analytics error: ' . htmlspecialchars($e->getMessage()));
}

admin_header('Analytics', 'analytics');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Analytics</h1>
        <p class="page-subtitle">Sales performance and store insights</p>
    </div>
    <div class="page-actions">
        <div style="display:flex;gap:6px;">
            <a href="?range=7"  class="btn <?php echo $range === 7  ? 'btn-primary' : 'btn-ghost'; ?> btn-sm">7D</a>
            <a href="?range=30" class="btn <?php echo $range === 30 ? 'btn-primary' : 'btn-ghost'; ?> btn-sm">30D</a>
            <a href="?range=90" class="btn <?php echo $range === 90 ? 'btn-primary' : 'btn-ghost'; ?> btn-sm">90D</a>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card kpi-revenue">
        <div class="kpi-top">
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        </div>
        <div class="kpi-value"><?php echo formatPrice($total_rev); ?></div>
        <div class="kpi-label">Total Revenue (Paid)</div>
    </div>
    <div class="kpi-card kpi-orders">
        <div class="kpi-top">
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
        </div>
        <div class="kpi-value"><?php echo number_format($total_ord); ?></div>
        <div class="kpi-label">Total Orders</div>
    </div>
    <div class="kpi-card kpi-customers">
        <div class="kpi-top">
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        </div>
        <div class="kpi-value"><?php echo number_format($total_cust); ?></div>
        <div class="kpi-label">Registered Customers</div>
    </div>
    <div class="kpi-card kpi-products">
        <div class="kpi-top">
            <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        </div>
        <div class="kpi-value"><?php echo formatPrice($avg_order); ?></div>
        <div class="kpi-label">Avg. Order Value</div>
    </div>
</div>

<!-- Revenue & Orders Chart -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <span class="card-title">Revenue & Orders</span>
        <span style="font-size:12px;color:var(--text-muted);">Last <?php echo $range; ?> days</span>
    </div>
    <div class="card-body" style="padding-top:16px;">
        <div style="position:relative;height:260px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<!-- Bottom Row: Breakdowns + Top Products -->
<div class="section-grid" style="margin-bottom:24px;">

    <!-- Left: delivery + payment pie charts -->
    <div style="display:flex;flex-direction:column;gap:24px;">
        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;">Order Status</h3>
            <?php
            $status_colors = ['Pending'=>'#FFB020','Confirmed'=>'#00D4FF','Shipped'=>'#FB923C','Delivered'=>'#00E676','Cancelled'=>'#FF4757'];
            $total_statuses = array_sum($status_breakdown);
            ?>
            <?php foreach ($status_breakdown as $status => $cnt): ?>
            <?php $pct = $total_statuses > 0 ? round($cnt / $total_statuses * 100) : 0; ?>
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:12.5px;">
                    <span style="color:var(--text-secondary);"><?php echo htmlspecialchars($status); ?></span>
                    <span style="font-family:var(--font-mono);font-weight:600;"><?php echo $cnt; ?> <span style="color:var(--text-muted);">(<?php echo $pct; ?>%)</span></span>
                </div>
                <div style="height:6px;background:var(--bg-overlay);border-radius:var(--r-full);overflow:hidden;">
                    <div style="width:<?php echo $pct; ?>%;height:100%;background:<?php echo $status_colors[$status] ?? '#8B92A5'; ?>;border-radius:var(--r-full);transition:width 0.8s var(--ease);"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($status_breakdown)): ?>
            <p style="color:var(--text-muted);font-size:13px;">No orders yet.</p>
            <?php endif; ?>
        </div>

        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;">Payment Methods</h3>
            <?php $total_pay = array_sum($payment_breakdown); ?>
            <?php foreach ($payment_breakdown as $method => $cnt): ?>
            <?php $pct = $total_pay > 0 ? round($cnt / $total_pay * 100) : 0; ?>
            <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--border);">
                <div style="width:8px;height:8px;border-radius:var(--r-full);background:var(--accent);flex-shrink:0;"></div>
                <div style="flex:1;font-size:13px;"><?php echo htmlspecialchars($method); ?></div>
                <div style="font-family:var(--font-mono);font-size:12px;color:var(--text-secondary);"><?php echo $cnt; ?> orders</div>
                <span class="badge badge-muted"><?php echo $pct; ?>%</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($payment_breakdown)): ?>
            <p style="color:var(--text-muted);font-size:13px;">No orders yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: top products table -->
    <div style="display:flex;flex-direction:column;gap:24px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Top Products by Revenue</span>
                <a href="products.php" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <?php if (empty($top_prods)): ?>
            <div class="empty-state" style="padding:24px;">
                <div class="empty-title">No sales data</div>
            </div>
            <?php else: ?>
            <?php foreach ($top_prods as $i => $p): ?>
            <div style="display:flex;align-items:center;gap:14px;padding:12px 20px;border-bottom:1px solid var(--border);">
                <div style="width:28px;height:28px;border-radius:var(--r-full);background:var(--accent-dim);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:11px;font-weight:700;color:var(--accent);flex-shrink:0;"><?php echo $i + 1; ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['name']); ?></div>
                    <div style="font-size:11px;color:var(--text-muted);"><?php echo (int)$p['units']; ?> units sold</div>
                </div>
                <div style="font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--success);flex-shrink:0;"><?php echo formatPrice($p['revenue']); ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;">Top Categories</h3>
            <?php $max_cat_rev = max(array_column($top_cats, 'revenue') ?: [0]); ?>
            <?php foreach ($top_cats as $cat): ?>
            <?php $pct = $max_cat_rev > 0 ? round($cat['revenue'] / $max_cat_rev * 100) : 0; ?>
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:12.5px;">
                    <span style="color:var(--text-secondary);"><?php echo htmlspecialchars($cat['name']); ?></span>
                    <span style="font-family:var(--font-mono);font-weight:600;"><?php echo formatPrice($cat['revenue']); ?></span>
                </div>
                <div style="height:5px;background:var(--bg-overlay);border-radius:var(--r-full);overflow:hidden;">
                    <div style="width:<?php echo $pct; ?>%;height:100%;background:var(--accent);border-radius:var(--r-full);"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($top_cats)): ?>
            <p style="color:var(--text-muted);font-size:13px;">No category data yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
(function() {
    var _chart  = null;
    var labels  = <?php echo json_encode($rev_labels); ?>;
    var revData = <?php echo json_encode($rev_data); ?>;
    var ordData = <?php echo json_encode($ord_data); ?>;
    var currency = '<?php echo addslashes(CURRENCY); ?>';

    function build() {
        var canvas = document.getElementById('revenueChart');
        if (!canvas || typeof Chart === 'undefined') return;
        if (_chart) { _chart.destroy(); _chart = null; }

        var c = (typeof ThemeManager !== 'undefined') ? ThemeManager.chartColors() : {};
        var line      = c.line        || '#00D4FF';
        var line2     = c.line2       || '#FFB020';
        var fillStart = c.fillStart   || 'rgba(0,212,255,0.15)';
        var grid      = c.grid        || 'rgba(255,255,255,0.05)';
        var tick      = c.tick        || '#4E5569';
        var pointBg   = c.pointBg     || '#0F1117';
        var tipBg     = c.tooltipBg   || '#242836';
        var tipTitle  = c.tooltipTitle|| 'rgba(255,255,255,0.87)';
        var tipBody   = c.tooltipBody || '#8B92A5';
        var tipBorder = c.tooltipBorder || 'rgba(255,255,255,0.1)';

        _chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revData,
                        backgroundColor: fillStart,
                        borderColor: line,
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders',
                        type: 'line',
                        data: ordData,
                        borderColor: line2,
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: pointBg,
                        pointBorderColor: line2,
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        yAxisID: 'y2',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        labels: { color: tipBody, font: { size: 12 }, boxWidth: 12 }
                    },
                    tooltip: {
                        backgroundColor: tipBg,
                        titleColor: tipTitle,
                        bodyColor: tipBody,
                        borderColor: tipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: grid, drawBorder: false },
                        ticks: { color: tick, font: { size: 11 }, callback: function(v) { return currency + v.toLocaleString(); } },
                        border: { display: false }
                    },
                    y2: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { color: tick, font: { size: 11 } },
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
