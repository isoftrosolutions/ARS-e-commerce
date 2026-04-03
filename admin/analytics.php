<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

try {
    // 1. Core KPIs
    $total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 0;
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 1;
    $total_customers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE payment_status = 'Paid'")->fetchColumn() ?: 1;

    $aov = $total_revenue / $total_orders;
    $ltv = $total_revenue / $total_customers;

    // 2. Retention (Repeat Customer Rate)
    $repeat_customers = $pdo->query("SELECT COUNT(*) FROM (SELECT user_id FROM orders WHERE payment_status = 'Paid' GROUP BY user_id HAVING COUNT(id) > 1) as repeats")->fetchColumn();
    $retention_rate = ($repeat_customers / $total_customers) * 100;

    // 3. Inventory Health (Stock-out prediction - Simple Linear)
    // We look at sales in last 30 days vs current stock
    $inventory_query = $pdo->query("
        SELECT p.name, p.stock, SUM(oi.quantity) as sold_30_days
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE (o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR o.created_at IS NULL)
        AND (o.payment_status = 'Paid' OR o.id IS NULL)
        GROUP BY p.id
        ORDER BY sold_30_days DESC
    ");
    $inventory_health = $inventory_query->fetchAll();

} catch (PDOException $e) {
    die("Analytics Error: " . $e->getMessage());
}

$page_title = "Business Intelligence";
include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Decision Intelligence</h1>
    <p class="text-slate-500">Data-driven insights to help you scale your store operations.</p>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg Order Value (AOV)</span>
        </div>
        <p class="text-2xl font-bold text-slate-800"><?= formatPrice($aov) ?></p>
        <p class="text-[10px] text-slate-400 mt-1">Average revenue generated per order.</p>
    </div>

    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer LTV</span>
        </div>
        <p class="text-2xl font-bold text-slate-800"><?= formatPrice($ltv) ?></p>
        <p class="text-[10px] text-slate-400 mt-1">Average total revenue per customer.</p>
    </div>

    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Retention Rate</span>
        </div>
        <p class="text-2xl font-bold text-slate-800"><?= number_format($retention_rate, 1) ?>%</p>
        <p class="text-[10px] text-slate-400 mt-1">Percentage of repeat buyers.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Inventory Forecast -->
    <div class="lg:col-span-2 bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Inventory Health & Forecast</h3>
            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Predictive</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase text-center">Current Stock</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase text-center">30D Velocity</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase text-right">Run-out In</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($inventory_health as $item): 
                        $velocity = $item['sold_30_days'] ?: 0;
                        $daily_sales = $velocity / 30;
                        $days_left = ($daily_sales > 0) ? floor($item['stock'] / $daily_sales) : '∞';
                        
                        $status_class = 'text-slate-600';
                        if($days_left !== '∞' && $days_left < 7) $status_class = 'text-red-600 font-bold';
                        elseif($days_left !== '∞' && $days_left < 15) $status_class = 'text-amber-600 font-bold';
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($item['name']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-medium <?= $item['stock'] < 10 ? 'text-red-500 font-bold' : '' ?>"><?= $item['stock'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm text-slate-500"><?= $velocity ?> units</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm <?= $status_class ?>">
                                <?= ($days_left === '∞') ? 'Stable' : $days_left . ' days' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">AI Recommendations</h3>
            <div class="space-y-4">
                <?php if($retention_rate < 20): ?>
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl">
                    <p class="text-xs font-bold text-blue-800 mb-1">Boost Loyalty</p>
                    <p class="text-[11px] text-blue-600 leading-relaxed">Your repeat customer rate is low. Consider launching an email campaign for second-time buyers.</p>
                </div>
                <?php endif; ?>

                <?php 
                $low_stock_count = 0;
                foreach($inventory_health as $i) if($i['stock'] < 5) $low_stock_count++;
                if($low_stock_count > 0): 
                ?>
                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-xs font-bold text-amber-800 mb-1">Restock Alert</p>
                    <p class="text-[11px] text-amber-600 leading-relaxed"><?= $low_stock_count ?> high-velocity items are running out. Restock now to avoid revenue loss.</p>
                </div>
                <?php endif; ?>

                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                    <p class="text-xs font-bold text-emerald-800 mb-1">Price Optimization</p>
                    <p class="text-[11px] text-emerald-600 leading-relaxed">Top products show steady demand. You could test a 5% price increase on your top 3 items.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
