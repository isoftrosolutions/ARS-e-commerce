<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('orders.php', "Invalid order.", "danger");
}

// Update status
if (isset($_POST['update_status'])) {
    $delivery_status = $_POST['delivery_status'];
    $payment_status  = $_POST['payment_status'];
    try {
        $stmt = $pdo->prepare("UPDATE orders SET delivery_status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$delivery_status, $payment_status, $id]);
        redirect("order-details.php?id=$id", "Order #ARS-$id updated successfully!");
    } catch (PDOException $e) {
        redirect("order-details.php?id=$id", "Error: " . $e->getMessage(), "danger");
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

if (!$order) {
    redirect('orders.php', "Order not found.", "danger");
}

// Fetch order items
$stmt_items = $pdo->prepare("
    SELECT oi.*, p.name AS product_name, p.slug AS product_slug, p.image AS product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();

$page_title = "Order #ARS-$id";
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="orders.php" class="text-slate-400 hover:text-brand-600 transition-colors">Orders</a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
            <span class="text-slate-800 font-bold">#ARS-<?= $id ?></span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Order Details</h1>
    </div>
    <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            <i data-lucide="printer" class="w-4 h-4"></i> Print Invoice
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Order Items & Info -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Items Ordered</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30">
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Price</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Qty</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-50 rounded border border-slate-100 flex-shrink-0 flex items-center justify-center p-1">
                                        <img src="<?= $item['product_image'] ? '../uploads/' . $item['product_image'] : 'https://via.placeholder.com/50' ?>" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($item['product_name']) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-slate-600"><?= formatPrice($item['price']) ?></td>
                            <td class="px-6 py-4 text-center text-sm text-slate-600">×<?= $item['quantity'] ?></td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-slate-800"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/50">
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-slate-500">Grand Total</td>
                            <td class="px-6 py-4 text-right font-bold text-brand-600 text-lg"><?= formatPrice($order['total_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Customer & Shipping -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-brand-500"></i> Customer Details
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Address</p>
                        <p class="text-sm font-semibold text-slate-700 mt-1"><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                    </div>
                    <?php if ($order['notes']): ?>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Order Note</p>
                        <p class="text-sm text-slate-600 mt-1 italic">"<?= htmlspecialchars($order['notes']) ?>"</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-brand-500"></i> Payment Info
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Method</p>
                        <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($order['payment_method']) ?></p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Status</p>
                        <?php
                        $p_status = $order['payment_status'] ?? 'Pending';
                        $p_class = match($p_status) {
                            'Paid' => 'bg-emerald-100 text-emerald-700',
                            'Failed' => 'bg-red-100 text-red-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                        ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $p_class ?>"><?= $p_status ?></span>
                    </div>
                    <?php if ($order['transaction_id']): ?>
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">TXN ID</p>
                        <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded font-bold text-slate-600"><?= htmlspecialchars($order['transaction_id']) ?></code>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['payment_proof']): ?>
                    <a href="../uploads/<?= htmlspecialchars($order['payment_proof']) ?>" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 border border-brand-200 rounded-lg text-xs font-bold text-brand-600 hover:bg-brand-50 transition-colors">
                        <i data-lucide="image" class="w-4 h-4"></i> View Payment Proof
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status Update -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 sticky top-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-brand-500"></i> Update Order
            </h3>
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Delivery Status</label>
                    <select name="delivery_status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="Pending" <?= $order['delivery_status'] === 'Pending' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="Confirmed" <?= $order['delivery_status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="Shipped" <?= $order['delivery_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped / Out for Delivery</option>
                        <option value="Delivered" <?= $order['delivery_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered ✅</option>
                        <option value="Cancelled" <?= $order['delivery_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled ❌</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Payment Status</label>
                    <select name="payment_status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="Pending" <?= $order['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending Verification</option>
                        <option value="Paid" <?= $order['payment_status'] === 'Paid' ? 'selected' : '' ?>>Verified (Paid) ✅</option>
                        <option value="Failed" <?= $order['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed / Rejected</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/20">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                </button>
            </form>
        </div>

        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl">
            <div class="flex gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-500 flex-shrink-0"></i>
                <div>
                    <h4 class="text-xs font-bold text-blue-800">Order Summary</h4>
                    <p class="text-[11px] text-blue-600 mt-1">Placed on <?= date('M d, Y', strtotime($order['created_at'])) ?>. Last updated on <?= date('M d, Y', strtotime($order['updated_at'] ?? $order['created_at'])) ?>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
