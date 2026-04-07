<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/coupon.php';

$page_title = "Shopping Cart";
require_once __DIR__ . '/includes/header-bootstrap.php';

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['qty'];
}

$shipping_fee = ($subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal == 0) ? 0 : SHIPPING_FEE;

$coupon_discount = 0;
$applied_coupon = null;

if (isset($_SESSION['coupon'])) {
    $result = apply_coupon($_SESSION['coupon']['code'], $subtotal);
    if ($result['valid']) {
        $coupon_discount = $result['discount'];
        $applied_coupon = $_SESSION['coupon'];
    } else {
        unset($_SESSION['coupon']);
    }
}

$grand_total = max(0, $subtotal + $shipping_fee - $coupon_discount);

$coupon_error = $_SESSION['coupon_error'] ?? null;
$coupon_success = $_SESSION['coupon_success'] ?? null;
unset($_SESSION['coupon_error'], $_SESSION['coupon_success']);
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Cart Content -->
<section class="py-4 py-md-5">
    <div class="container">
        
        <?php if (empty($cart)): ?>
            <!-- Empty Cart -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-cart-x display-1 text-muted opacity-25"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added anything yet.</p>
                <a href="shop.php" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-bag me-2"></i>Start Shopping
                </a>
            </div>
        <?php else: ?>
        
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 fw-bold mb-0">Shopping Cart (<?= count($cart) ?> items)</h2>
                    </div>
                    
                    <!-- Free Shipping Progress -->
                    <?php if($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                        <div class="alert alert-info d-flex align-items-center rounded-3 mb-4" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div>
                                Add <strong><?= formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal) ?></strong> more for FREE shipping!
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success d-flex align-items-center rounded-3 mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>You've unlocked <strong>FREE shipping</strong>!</div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Cart Items List -->
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body p-0">
                            <?php foreach ($cart as $id => $item): ?>
                                <div class="cart-item p-3 p-md-4 <?= $id !== array_key_last($cart) ? 'border-bottom' : '' ?>">
                                    <div class="row g-3 align-items-center">
                                        <!-- Product Image -->
                                        <div class="col-3 col-md-2">
                                            <img src="<?= !empty($item['image']) ? UPLOAD_DIR . htmlspecialchars($item['image']) : 'https://via.placeholder.com/150' ?>" 
                                                 class="img-fluid rounded-2 bg-light p-2" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 style="object-fit: contain; height: 80px;">
                                        </div>
                                        
                                        <!-- Product Info -->
                                        <div class="col-9 col-md-4">
                                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                            <p class="text-muted small mb-2"><?= formatPrice($item['price']) ?> each</p>
                                            
                                            <!-- Quantity Controls -->
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="cart-action.php?action=update&id=<?= (int)$id ?>&qty=<?= max(1, $item['qty'] - 1) ?>" 
                                                   class="btn btn-outline-secondary btn-sm rounded-2">
                                                    <i class="bi bi-dash"></i>
                                                </a>
                                                <span class="fw-bold px-3"><?= (int)$item['qty'] ?></span>
                                                <a href="cart-action.php?action=update&id=<?= (int)$id ?>&qty=<?= $item['qty'] + 1 ?>" 
                                                   class="btn btn-outline-secondary btn-sm rounded-2">
                                                    <i class="bi bi-plus"></i>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Price -->
                                        <div class="col-6 col-md-3 text-md-center mt-3 mt-md-0">
                                            <p class="text-muted small mb-1 d-none d-md-block">Total</p>
                                            <h5 class="fw-bold text-dark mb-0"><?= formatPrice($item['price'] * $item['qty']) ?></h5>
                                        </div>
                                        
                                        <!-- Remove -->
                                        <div class="col-6 col-md-3 text-md-end mt-3 mt-md-0">
                                            <a href="cart-action.php?action=remove&id=<?= (int)$id ?>" 
                                               class="btn btn-outline-danger btn-sm rounded-2">
                                                <i class="bi bi-trash me-1 d-none d-md-inline"></i>Remove
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="shop.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">Order Summary</h4>
                            
                            <?php if($coupon_error): ?>
                                <div class="alert alert-danger py-2 rounded-2" role="alert">
                                    <?= htmlspecialchars($coupon_error) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($coupon_success): ?>
                                <div class="alert alert-success py-2 rounded-2" role="alert">
                                    <?= htmlspecialchars($coupon_success) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Summary Rows -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold"><?= formatPrice($subtotal) ?></span>
                            </div>
                            
                            <?php if($coupon_discount > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Coupon (<?= htmlspecialchars($applied_coupon['code']) ?>)</span>
                                    <span class="fw-semibold">-<?= formatPrice($coupon_discount) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Shipping</span>
                                <span class="fw-semibold <?= $shipping_fee == 0 ? 'text-success' : '' ?>">
                                    <?= $shipping_fee == 0 ? 'FREE' : formatPrice($shipping_fee) ?>
                                </span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 fw-bold">Total</span>
                                <span class="h5 fw-bold text-primary"><?= formatPrice($grand_total) ?></span>
                            </div>
                            
                            <!-- Coupon -->
                            <form action="cart-action.php" method="POST" class="mb-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="apply_coupon">
                                <label class="form-label small fw-semibold">Have a coupon?</label>
                                <div class="input-group">
                                    <input type="text" name="coupon_code" placeholder="Enter code" 
                                           value="<?= htmlspecialchars($_SESSION['coupon']['code'] ?? '') ?>"
                                           class="form-control text-uppercase">
                                    <button type="submit" class="btn btn-dark">Apply</button>
                                </div>
                                <?php if($applied_coupon): ?>
                                    <a href="cart-action.php?action=remove_coupon" class="small text-danger">Remove coupon</a>
                                <?php endif; ?>
                            </form>
                            
                            <!-- Checkout Button -->
                            <a href="checkout.php" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-lock me-2"></i>Proceed to Checkout
                            </a>
                            
                            <!-- Payment Methods -->
                            <div class="text-center">
                                <small class="text-muted d-block mb-2">We accept:</small>
                                <div class="d-flex justify-content-center gap-2">
                                    <span class="badge bg-light text-dark">eSewa</span>
                                    <span class="badge bg-light text-danger">FonePay</span>
                                    <span class="badge bg-light text-primary">VISA</span>
                                    <span class="badge bg-light text-success">COD</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
