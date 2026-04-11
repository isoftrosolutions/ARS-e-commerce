<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to view your orders.", "info");
}
no_cache();

try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $all_orders = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Orders page error for user {$user_id}: " . $e->getMessage());
    redirect('index.php', 'Something went wrong loading your orders. Please try again.', 'danger');
}

$page_title = "My Orders History";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Account</a></li>
                <li class="breadcrumb-item active" aria-current="page">Orders</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Orders Content -->
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row g-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush rounded-0">
                            <a href="dashboard.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a href="orders.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-box-seam me-2"></i>My Orders
                            </a>
                            <a href="wishlist.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-heart me-2"></i>Wishlist
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                            <a href="auth/logout.php" class="list-group-item list-group-item-action text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <h3 class="fw-bold mb-4">My Orders</h3>
                
                <?php if(empty($all_orders)): ?>
                    <div class="card border-0 shadow-sm rounded-3 text-center py-5">
                        <div class="card-body py-5">
                            <i class="bi bi-box-seam display-1 text-muted opacity-25"></i>
                            <h4 class="mt-4 mb-2">No orders yet</h4>
                            <p class="text-muted mb-4">Start shopping to see your orders here.</p>
                            <a href="shop.php" class="btn btn-primary px-4">
                                <i class="bi bi-bag me-2"></i>Start Shopping
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach($all_orders as $order): ?>
                            <?php
                            $status_class = match($order['delivery_status']) {
                                'Delivered' => 'success',
                                'Shipped' => 'info',
                                'Confirmed' => 'primary',
                                'Cancelled' => 'danger',
                                default => 'warning'
                            };
                            $payment_badge = match($order['payment_status']) {
                                'Paid' => '<span class="badge bg-success ms-2">Paid</span>',
                                'Failed' => '<span class="badge bg-danger ms-2">Failed</span>',
                                default => ''
                            };
                            ?>
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center g-3">
                                            <div class="col-auto">
                                                <div class="bg-light rounded-3 p-3">
                                                    <i class="bi bi-box-seam text-primary fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <a href="order-details.php?id=<?= $order['id'] ?>" class="text-decoration-none fw-bold h5 mb-0">
                                                        #ARS-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
                                                    </a>
                                                    <span class="badge bg-<?= $status_class ?>"><?= $order['delivery_status'] ?></span>
                                                    <?= $payment_badge ?>
                                                </div>
                                                <p class="text-muted small mb-0 mt-1">
                                                    <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?> 
                                                    • <?= $order['payment_method'] ?> 
                                                    • <?= formatPrice($order['total_amount']) ?>
                                                </p>
                                                <?php if(!empty($order['current_location'])): ?>
                                                    <div class="mt-2 text-primary small d-flex align-items-center">
                                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                                        <strong>Location:</strong> <span class="ms-1"><?= htmlspecialchars($order['current_location']) ?></span>
                                                        <span class="ms-2 text-muted" style="font-size: 10px;">(<?= $order['location_updated_at'] ? date('M d, g:i A', strtotime($order['location_updated_at'])) : date('M d, g:i A', strtotime($order['created_at'])) ?>)</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-auto">
                                                <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
