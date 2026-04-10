<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('auth/login.php', "Please login to access your dashboard.", "info");
}

try {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $stmt_stats = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt_stats->execute([$user_id]);
    $total_orders = $stmt_stats->fetchColumn();

    $stmt_recent = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt_recent->execute([$user_id]);
    $recent_orders = $stmt_recent->fetchAll();

} catch (PDOException $e) {
    die("Dashboard Error: " . $e->getMessage());
}

$page_title = "My Account Dashboard";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Dashboard Content -->
<section class="py-4 py-md-5">
    <div class="container">
        <div class="row g-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                    <div class="card-body p-4 text-center">
                        <div class="user-avatar-lg mx-auto mb-3">
                            <?= substr($user['full_name'], 0, 1) ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                        <p class="text-muted small mb-3">Customer Since <?= date('Y', strtotime($user['created_at'])) ?></p>
                    </div>
                    <hr class="my-0">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush rounded-0">
                            <a href="dashboard.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a href="orders.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-box-seam me-2"></i>My Orders
                            </a>
                            <a href="wishlist.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-heart me-2"></i>Wishlist
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                        </div>
                    </div>
                    <div class="card-body border-top">
                        <a href="auth/logout.php" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                
                <!-- Welcome Card -->
                <div class="card border-0 shadow-sm rounded-3 bg-primary text-white mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h4>
                                <p class="mb-0 opacity-75">Manage your orders and account settings</p>
                            </div>
                            <div class="col-auto">
                                <a href="shop.php" class="btn btn-light btn-lg">
                                    <i class="bi bi-bag me-2"></i>Shop Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                            <i class="bi bi-box-seam text-primary fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h3 class="mb-0"><?= number_format($total_orders) ?></h3>
                                        <p class="text-muted mb-0 small">Total Orders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                            <i class="bi bi-clock-history text-warning fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <?php
                                        $qPending = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND delivery_status = 'Pending'");
                                        $qPending->execute([$user_id]);
                                        $pending_count = (int)$qPending->fetchColumn();
                                        ?>
                                        <h3 class="mb-0"><?= number_format($pending_count) ?></h3>
                                        <p class="text-muted mb-0 small">Pending Orders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                            <i class="bi bi-heart text-success fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <?php
                                        $qWishList = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                                        $qWishList->execute([$user_id]);
                                        $wish_count = (int)$qWishList->fetchColumn();
                                        ?>
                                        <h3 class="mb-0"><?= number_format($wish_count) ?></h3>
                                        <p class="text-muted mb-0 small">Wishlist Items</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Recent Orders</h5>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if(empty($recent_orders)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bag display-4 text-muted opacity-25"></i>
                                <p class="text-muted mt-3 mb-4">No orders yet</p>
                                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0 rounded-start">Order ID</th>
                                            <th class="border-0">Date</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0">Total</th>
                                            <th class="border-0 rounded-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_orders as $order): ?>
                                            <tr>
                                                <td class="align-middle">
                                                    <a href="order-details.php?id=<?= $order['id'] ?>" class="text-decoration-none fw-semibold">
                                                        #ARS-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
                                                    </a>
                                                </td>
                                                <td class="align-middle text-muted"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                                <td class="align-middle">
                                                    <?php
                                                    $status_class = match($order['delivery_status']) {
                                                        'Delivered' => 'success',
                                                        'Shipped' => 'info',
                                                        'Confirmed' => 'primary',
                                                        'Cancelled' => 'danger',
                                                        default => 'warning'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?= $status_class ?>"><?= $order['delivery_status'] ?></span>
                                                </td>
                                                <td class="align-middle fw-semibold"><?= formatPrice($order['total_amount']) ?></td>
                                                <td class="align-middle">
                                                    <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
