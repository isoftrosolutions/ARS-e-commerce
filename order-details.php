<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to view order details.", "info");
}

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    redirect('orders.php', 'Invalid order.', 'danger');
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php', 'Order not found.', 'danger');
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image, p.slug 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$status_colors = [
    'Pending' => 'bg-amber-100 text-amber-700',
    'Confirmed' => 'bg-blue-100 text-blue-700',
    'Shipped' => 'bg-purple-100 text-purple-700',
    'Delivered' => 'bg-green-100 text-green-700',
    'Cancelled' => 'bg-red-100 text-red-700'
];

$payment_colors = [
    'Pending' => 'bg-amber-100 text-amber-700',
    'Paid' => 'bg-green-100 text-green-700',
    'Failed' => 'bg-red-100 text-red-700'
];

$page_title = "Order #$order_id";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-8 sticky top-28">
                <nav class="flex flex-col gap-2">
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i> Dashboard
                    </a>
                    <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-brand-600 text-white font-bold transition-all shadow-lg shadow-brand-600/20">
                        <i data-lucide="package" class="w-4 h-4"></i> My Orders
                    </a>
                    <a href="wishlist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 font-bold transition-all">
                        <i data-lucide="heart" class="w-4 h-4"></i> Wishlist
                    </a>
                    <hr class="my-4 border-slate-100">
                    <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 font-bold transition-all">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                    </a>
                </nav>
            </div>
        </aside>

        <div class="flex-grow">
            <div class="mb-8">
                <a href="orders.php" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 transition-colors mb-4">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Orders
                </a>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900">Order #<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></h1>
                        <p class="text-slate-500 text-sm mt-1">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-4 py-2 rounded-full text-sm font-bold <?= $status_colors[$order['delivery_status']] ?? 'bg-slate-100 text-slate-700' ?>">
                            <?= htmlspecialchars($order['delivery_status']) ?>
                        </span>
                        <span class="px-4 py-2 rounded-full text-sm font-bold <?= $payment_colors[$order['payment_status']] ?? 'bg-slate-100 text-slate-700' ?>">
                            <?= htmlspecialchars($order['payment_status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <i data-lucide="package" class="w-5 h-5 text-brand-600"></i>
                            Order Items
                        </h3>
                        
                        <div class="space-y-4">
                            <?php foreach ($items as $item): ?>
                            <div class="flex items-center gap-4 py-4 border-b border-slate-100 last:border-0">
                                <div class="w-20 h-20 bg-slate-50 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= UPLOAD_DIR . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-contain">
                                    <?php else: ?>
                                        <i data-lucide="image" class="w-8 h-8 text-slate-400"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow">
                                    <a href="product.php?slug=<?= htmlspecialchars($item['slug'] ?? '') ?>" class="font-bold text-slate-800 hover:text-brand-600 transition-colors">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <p class="text-sm text-slate-500 mt-1">
                                        <?= (int)$item['quantity'] ?> × <?= formatPrice($item['price']) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-slate-900"><?= formatPrice($item['quantity'] * $item['price']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="border-t border-slate-100 mt-6 pt-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-medium"><?= formatPrice($order['total_amount']) ?></span>
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-500">Shipping</span>
                                <span class="font-medium text-emerald-600">FREE</span>
                            </div>
                            <div class="flex justify-between text-lg font-black mt-4 pt-4 border-t border-slate-100">
                                <span>Total</span>
                                <span class="text-brand-600"><?= formatPrice($order['total_amount']) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($order['payment_method'] !== 'COD' && !empty($order['transaction_id'])): ?>
                    <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-5 h-5 text-brand-600"></i>
                            Payment Details
                        </h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Method</p>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($order['payment_method']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Transaction ID</p>
                                <p class="font-bold text-slate-800 font-mono"><?= htmlspecialchars($order['transaction_id']) ?></p>
                            </div>
                            <?php if (!empty($order['payment_proof'])): ?>
                            <div class="col-span-2">
                                <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Payment Proof</p>
                                <a href="<?= UPLOAD_DIR . htmlspecialchars($order['payment_proof']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-lg text-sm font-bold hover:bg-slate-200 transition-colors">
                                    <i data-lucide="image" class="w-4 h-4"></i> View Screenshot
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-5 h-5 text-brand-600"></i>
                            Delivery Address
                        </h3>
                        <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                    </div>

                    <?php if (!empty($order['notes'])): ?>
                    <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i data-lucide="message-square" class="w-5 h-5 text-brand-600"></i>
                            Order Notes
                        </h3>
                        <p class="text-slate-600"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="bg-gradient-to-br from-brand-600 to-brand-900 rounded-3xl p-6 md:p-8 text-white">
                        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <i data-lucide="headphones" class="w-5 h-5"></i>
                            Need Help?
                        </h3>
                        <p class="text-sm text-white/80 mb-4">Have questions about your order? We're here to help.</p>
                        <a href="tel:+9779800000000" class="flex items-center gap-2 text-sm font-bold hover:underline">
                            <i data-lucide="phone" class="w-4 h-4"></i> +977 980-000-0000
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
