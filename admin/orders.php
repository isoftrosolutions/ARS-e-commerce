<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    $all_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}

$page_title = "Manage Customer Orders";
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Order Management</h1>
        <p class="text-slate-500">Track and manage customer orders and fulfillment status.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            <i data-lucide="filter" class="w-4 h-4"></i> Filter
        </button>
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i> Export
        </button>
    </div>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Payment</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Delivery Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Amount</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($all_orders as $order): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-slate-800">#ARS-<?= $order['id'] ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-700"><?= explode(' (', $order['address'] ?? 'N/A (')[0] ?></div>
                        <div class="text-xs text-slate-400 truncate w-40"><?= htmlspecialchars($order['address'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center gap-1">
                            <?php
                            $p_status = $order['payment_status'] ?? 'Pending';
                            $p_class = match($p_status) {
                                'Paid' => 'bg-emerald-100 text-emerald-700',
                                'Failed' => 'bg-red-100 text-red-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                            ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $p_class ?>">
                                <?= $p_status ?>
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium"><?= $order['payment_method'] ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php
                        $status_classes = [
                            'Pending' => 'bg-amber-100 text-amber-700',
                            'Confirmed' => 'bg-blue-50 text-blue-600',
                            'Shipped' => 'bg-indigo-100 text-indigo-700',
                            'Delivered' => 'bg-emerald-100 text-emerald-700',
                            'Cancelled' => 'bg-red-100 text-red-700'
                        ];
                        $status = $order['delivery_status'] ?? 'Pending';
                        $class = $status_classes[$status] ?? 'bg-slate-100 text-slate-700';
                        ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $class ?>"><?= $status ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-sm font-bold text-slate-800"><?= formatPrice($order['total_amount']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="order-details.php?id=<?= $order['id'] ?>" class="inline-flex p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-all" title="View Details">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($all_orders)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-4 text-slate-200"></i>
                        <p class="text-lg font-medium">No orders found</p>
                        <p class="text-sm">When customers buy products, they will appear here.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
