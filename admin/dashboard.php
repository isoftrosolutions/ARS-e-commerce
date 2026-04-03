<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied. Admin portal only.", "danger");
}

try {
    // Stats
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();

    // Recent Orders
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    $recent_orders = $stmt->fetchAll();

    // Chart Data (Last 7 days)
    $chart_labels = [];
    $chart_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('D', strtotime($date));
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = ? AND payment_status = 'Paid'");
        $stmt->execute([$date]);
        $chart_data[] = $stmt->fetchColumn() ?: 0;
    }
} catch (PDOException $e) {
    die("Admin Error: " . $e->getMessage());
}

$page_title = "Admin Dashboard";
include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Overview</h1>
    <p class="text-slate-500">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>. Here's what's happening today.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
            <i data-lucide="dollar-sign" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Revenue</p>
            <p class="text-2xl font-bold text-slate-800"><?= formatPrice($total_sales) ?></p>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Orders</p>
            <p class="text-2xl font-bold text-slate-800"><?= $total_orders ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
            <i data-lucide="package" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Products</p>
            <p class="text-2xl font-bold text-slate-800"><?= $total_products ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
            <i data-lucide="users" class="w-6 h-6"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Users</p>
            <p class="text-2xl font-bold text-slate-800"><?= $total_users ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Revenue Chart -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-800">Revenue Analytics</h3>
            <select class="text-sm border-slate-200 rounded-lg bg-slate-50 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
            </select>
        </div>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions & Alerts -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="product-add.php" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-brand-50 hover:text-brand-600 rounded-xl transition-colors group">
                    <i data-lucide="plus-circle" class="w-6 h-6 mb-2 text-slate-400 group-hover:text-brand-500"></i>
                    <span class="text-xs font-semibold">Add Product</span>
                </a>
                <a href="orders.php" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-brand-50 hover:text-brand-600 rounded-xl transition-colors group">
                    <i data-lucide="truck" class="w-6 h-6 mb-2 text-slate-400 group-hover:text-brand-500"></i>
                    <span class="text-xs font-semibold">Ship Orders</span>
                </a>
                <a href="categories.php" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-brand-50 hover:text-brand-600 rounded-xl transition-colors group">
                    <i data-lucide="grid" class="w-6 h-6 mb-2 text-slate-400 group-hover:text-brand-500"></i>
                    <span class="text-xs font-semibold">Categories</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-brand-50 hover:text-brand-600 rounded-xl transition-colors group">
                    <i data-lucide="settings" class="w-6 h-6 mb-2 text-slate-400 group-hover:text-brand-500"></i>
                    <span class="text-xs font-semibold">Settings</span>
                </a>
            </div>
        </div>

        <?php if ($low_stock > 0): ?>
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex gap-4 items-start">
            <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-amber-800 text-sm">Inventory Alert</h4>
                <p class="text-amber-700 text-xs mt-1"><?= $low_stock ?> products are currently low in stock. Consider restocking soon.</p>
                <a href="products.php" class="text-amber-800 text-xs font-bold mt-2 inline-block hover:underline">Manage Inventory &rarr;</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800">Recent Orders</h3>
        <a href="orders.php" class="text-brand-600 text-sm font-semibold hover:underline">View All Orders</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($recent_orders as $order): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-slate-800">#ARS-<?= $order['id'] ?></td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-700"><?= explode(' (', $order['address'])[0] ?></div>
                        <div class="text-xs text-slate-400 truncate w-48"><?= $order['address'] ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $status_classes = [
                            'Pending' => 'bg-amber-100 text-amber-700',
                            'Completed' => 'bg-emerald-100 text-emerald-700',
                            'Cancelled' => 'bg-red-100 text-red-700',
                            'Shipped' => 'bg-blue-100 text-blue-700'
                        ];
                        $status = $order['delivery_status'] ?? 'Pending';
                        $class = $status_classes[$status] ?? 'bg-slate-100 text-slate-700';
                        ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $class ?>"><?= $status ?></span>
                    </td>
                    <td class="px-6 py-4 font-bold text-slate-800"><?= formatPrice($order['total_amount']) ?></td>
                    <td class="px-6 py-4">
                        <a href="order-details.php?id=<?= $order['id'] ?>" class="p-2 text-slate-400 hover:text-brand-600 transition-colors">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($chart_data) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1e293b',
                    titleColor: '#f8fafc',
                    bodyColor: '#f8fafc',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10 },
                        callback: function(value) { return 'Rs.' + value; }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 10 } }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>

