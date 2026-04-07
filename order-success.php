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
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="success-checkmark d-inline-flex align-items-center justify-content-center rounded-circle mb-4">
                    <i class="bi bi-check-lg text-success"></i>
                </div>
                <h1 class="display-6 fw-bold text-dark mb-3">THANK YOU!</h1>
                <p class="lead text-muted">
                    Your order <span class="fw-bold text-dark">#ARS-<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></span> has been placed successfully.
                </p>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold d-flex align-items-center">
                        <i class="bi bi-box-seam text-primary me-2"></i> Order Summary
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($items as $index => $item): ?>
                    <div class="d-flex align-items-center gap-3 p-3 <?= $index > 0 ? 'border-top' : '' ?>">
                        <div class="flex-shrink-0" style="width:64px;height:64px;">
                            <?php if ($item['image']): ?>
                                <img src="<?= UPLOAD_DIR . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-semibold"><?= htmlspecialchars($item['name']) ?></p>
                            <small class="text-muted">Qty: <?= (int)$item['quantity'] ?> × <?= formatPrice($item['price']) ?></small>
                        </div>
                        <div class="fw-bold">
                            <?= formatPrice($item['quantity'] * $item['price']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-medium"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Method</span>
                        <span class="fw-medium"><?= htmlspecialchars($order['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Status</span>
                        <span class="badge <?= $order['payment_status'] === 'Paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= htmlspecialchars($order['payment_status']) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Delivery Status</span>
                        <span class="badge bg-info"><?= htmlspecialchars($order['delivery_status']) ?></span>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-box text-primary fs-3 mb-2"></i>
                            <h6 class="fw-bold mb-1">Preparation</h6>
                            <small class="text-muted">Expected within 24h</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-truck text-primary fs-3 mb-2"></i>
                            <h6 class="fw-bold mb-1">Shipping</h6>
                            <small class="text-muted">2-4 Business Days</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-headset text-primary fs-3 mb-2"></i>
                            <h6 class="fw-bold mb-1">Support</h6>
                            <small class="text-muted">Available 24/7</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="orders.php" class="btn btn-dark px-4 py-3 fw-bold">
                    <i class="bi bi-truck me-2"></i> Track My Order
                </a>
                <a href="shop.php" class="btn btn-outline-dark px-4 py-3 fw-bold">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
