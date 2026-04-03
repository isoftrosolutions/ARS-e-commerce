<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    redirect('index.php', 'Invalid order.', 'danger');
}

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (user_id IS NULL OR user_id = 0)");
    $stmt->execute([$order_id]);
}
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php', 'Order not found.', 'danger');
}

$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image, p.slug FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$page_title = "Order Confirmed!";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-12 md:py-20">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-10">
            <div class="w-24 h-24 bg-emerald-100 text-emerald-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 animate-bounce">
                <i data-lucide="check-circle-2" class="w-12 h-12"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-3">THANK YOU!</h1>
            <p class="text-lg text-slate-500 font-medium">
                Your order <span class="text-slate-900 font-black">#ARS-<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></span> has been placed successfully.
            </p>
        </div>

        <div class="bg-white rounded-3xl soft-shadow border border-slate-100 p-6 md:p-8 mb-8">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i data-lucide="package" class="w-5 h-5 text-brand-600"></i>
                Order Summary
            </h3>
            
            <div class="space-y-4 mb-6">
                <?php foreach ($items as $item): ?>
                <div class="flex items-center gap-4 py-3 border-b border-slate-100 last:border-0">
                    <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center overflow-hidden">
                        <?php if ($item['image']): ?>
                            <img src="<?= UPLOAD_DIR . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i data-lucide="image" class="w-6 h-6 text-slate-400"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="text-sm text-slate-500">Qty: <?= (int)$item['quantity'] ?> × <?= formatPrice($item['price']) ?></p>
                    </div>
                    <div class="font-bold text-slate-800">
                        <?= formatPrice($item['quantity'] * $item['price']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="border-t border-slate-100 pt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Subtotal</span>
                    <span class="font-medium"><?= formatPrice($order['total_amount']) ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Payment Method</span>
                    <span class="font-medium"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Payment Status</span>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold <?= $order['payment_status'] === 'Paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                        <?= htmlspecialchars($order['payment_status']) ?>
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Delivery Status</span>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                        <?= htmlspecialchars($order['delivery_status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
            <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 text-center">
                <i data-lucide="package" class="w-8 h-8 text-brand-600 mx-auto mb-3"></i>
                <h4 class="font-bold text-slate-900 mb-1">Preparation</h4>
                <p class="text-xs text-slate-400 font-medium">Expected within 24h</p>
            </div>
            <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 text-center">
                <i data-lucide="truck" class="w-8 h-8 text-brand-600 mx-auto mb-3"></i>
                <h4 class="font-bold text-slate-900 mb-1">Shipping</h4>
                <p class="text-xs text-slate-400 font-medium">2-4 Business Days</p>
            </div>
            <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 text-center">
                <i data-lucide="phone-call" class="w-8 h-8 text-brand-600 mx-auto mb-3"></i>
                <h4 class="font-bold text-slate-900 mb-1">Support</h4>
                <p class="text-xs text-slate-400 font-medium">Available 24/7</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="orders.php" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-base hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200 flex items-center justify-center gap-3">
                Track My Order <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
            <a href="shop.php" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-base hover:bg-slate-50 transition-all flex items-center justify-center gap-3">
                Continue Shopping
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
