<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

try {
    // 1. Revenue by Category
    $category_revenue = $pdo->query("
        SELECT c.name, SUM(oi.price * oi.quantity) as revenue
        FROM categories c
        JOIN products p ON c.id = p.category_id
        JOIN order_items oi ON p.id = oi.product_id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.payment_status = 'Paid'
        GROUP BY c.id
    ")->fetchAll();

    // 2. Best Selling Products
    $top_products = $pdo->query("
        SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as total_revenue
        FROM products p
        JOIN order_items oi ON p.id = oi.product_id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.payment_status = 'Paid'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5
    ")->fetchAll();

    // 3. Monthly Sales (Last 6 months)
    $monthly_sales = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $month_display = date('M Y', strtotime("-$i months"));
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$month]);
        $monthly_sales[$month_display] = $stmt->fetchColumn() ?: 0;
    }

} catch (PDOException $e) {
    die("Report Error: " . $e->getMessage());
}

$page_title = "Reports & Analytics";
include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Reports & Analytics</h1>
    <p class="text-slate-500">Analyze your store's performance with sales data and product trends.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Monthly Sales Chart -->
    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Sales Growth (Last 6 Months)</h3>
        <div class="h-64">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Revenue by Category</h3>
        <div class="h-64 flex items-center justify-center">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Products Table -->
<div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800">Best Selling Products</h3>
        <button class="text-sm font-semibold text-brand-600 hover:underline">Download Full Report</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Product Name</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Units Sold</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($top_products as $p): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($p['name']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold"><?= $p['total_sold'] ?> sold</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold text-slate-800"><?= formatPrice($p['total_revenue']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($top_products)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center text-slate-400">Not enough sales data yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Monthly Sales Chart
    const salesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($monthly_sales)) ?>,
            datasets: [{
                label: 'Monthly Revenue',
                data: <?= json_encode(array_values($monthly_sales)) ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Category Pie Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($category_revenue, 'name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($category_revenue, 'revenue')) ?>,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            },
            cutout: '70%'
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
