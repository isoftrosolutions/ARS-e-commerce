<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to view order details.", "info");
}
no_cache();

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
    'Pending' => 'bg-warning text-dark',
    'Confirmed' => 'bg-primary',
    'Shipped' => 'bg-purple',
    'Delivered' => 'bg-success',
    'Cancelled' => 'bg-danger'
];

$payment_colors = [
    'Pending' => 'bg-warning text-dark',
    'Paid' => 'bg-success',
    'Failed' => 'bg-danger'
];

$page_title = "Order #$order_id";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <div class="col-12 col-lg-3">
            <div class="card shadow-sm sticky-top" style="top: 100px;">
                <div class="card-body p-2">
                    <nav class="nav flex-column">
                        <a href="dashboard.php" class="nav-link text-muted py-2 px-3 rounded">
                            <i class="bi bi-grid-1x2 me-2"></i> Dashboard
                        </a>
                        <a href="orders.php" class="nav-link active bg-primary text-white py-2 px-3 rounded">
                            <i class="bi bi-box-seam me-2"></i> My Orders
                        </a>
                        <a href="wishlist.php" class="nav-link text-muted py-2 px-3 rounded">
                            <i class="bi bi-heart me-2"></i> Wishlist
                        </a>
                        <hr class="my-2">
                        <a href="auth/logout.php" class="nav-link text-danger py-2 px-3 rounded">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-9">
            <div class="mb-4">
                <a href="orders.php" class="text-decoration-none text-muted mb-3 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Back to Orders
                </a>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Order #<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></h1>
                        <p class="text-muted mb-0 small">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge <?= $status_colors[$order['delivery_status']] ?? 'bg-secondary' ?> px-3 py-2">
                            <?= htmlspecialchars($order['delivery_status']) ?>
                        </span>
                        <span class="badge <?= $payment_colors[$order['payment_status']] ?? 'bg-secondary' ?> px-3 py-2">
                            <?= htmlspecialchars($order['payment_status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Tracking Status -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i> Current Status & Location
                    </h5>
                </div>
                <div class="card-body bg-light rounded-bottom p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="tracking-icon-pulse flex-shrink-0">
                            <i class="bi bi-geo-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Product Location:</h6>
                            <p class="h5 text-primary fw-bold mb-2"><?= htmlspecialchars($order['current_location'] ?: 'Processing your order...') ?></p>
                            <p class="text-muted small mb-0">Last updated: <?= $order['location_updated_at'] ? date('M d, Y \a\t g:i A', strtotime($order['location_updated_at'])) : date('M d, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-box-seam text-primary me-2"></i> Order Items
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($items as $index => $item): ?>
                            <div class="d-flex align-items-center gap-3 p-3 <?= $index > 0 ? 'border-top' : '' ?>">
                                <div class="flex-shrink-0" style="width:80px;height:80px;">
                                    <?php if ($item['image']): ?>
                                        <img src="<?= UPLOAD_DIR . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-fluid rounded">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="product.php?slug=<?= htmlspecialchars($item['slug'] ?? '') ?>" class="text-decoration-none fw-semibold text-dark">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <p class="text-muted mb-0 small">
                                        <?= (int)$item['quantity'] ?> × <?= formatPrice($item['price']) ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <p class="fw-bold mb-0"><?= formatPrice($item['quantity'] * $item['price']) ?></p>
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
                                <span class="text-muted">Shipping</span>
                                <span class="fw-medium text-success">FREE</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top fw-bold">
                                <span>Total</span>
                                <span class="text-primary"><?= formatPrice($order['total_amount']) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($order['payment_method'] !== 'COD' && !empty($order['transaction_id'])): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-credit-card text-primary me-2"></i> Payment Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <p class="text-muted small mb-1">Method</p>
                                    <p class="fw-semibold mb-0"><?= htmlspecialchars($order['payment_method']) ?></p>
                                </div>
                                <div class="col-6 mb-3">
                                    <p class="text-muted small mb-1">Transaction ID</p>
                                    <p class="fw-semibold mb-0 font-monospace"><?= htmlspecialchars($order['transaction_id']) ?></p>
                                </div>
                                <?php if (!empty($order['payment_proof'])): ?>
                                <div class="col-12">
                                    <p class="text-muted small mb-2">Payment Proof</p>
                                    <a href="<?= UPLOAD_DIR . htmlspecialchars($order['payment_proof']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-image me-1"></i> View Screenshot
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-geo-alt text-primary me-2"></i> Delivery Address
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($order['notes'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center">
                                <i class="bi bi-chat-left-text text-primary me-2"></i> Order Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-headset me-2"></i> Need Help?
                            </h5>
                            <p class="mb-3 small opacity-75">Have questions about your order? We're here to help.</p>
                            <a href="tel:+9779820210361" class="text-white text-decoration-none fw-semibold">
                                <i class="bi bi-telephone me-1"></i> +977 982-0210361
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
