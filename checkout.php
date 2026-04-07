<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = "Checkout";
require_once __DIR__ . '/includes/header-bootstrap.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('shop.php', "Your cart is empty.", "info");
}

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['qty'];
}

$shipping_fee = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_FEE;
$grand_total = $subtotal + $shipping_fee;

$user = null;
if(is_logged_in()) {
    $stmt = $pdo->prepare("SELECT full_name, mobile, email, address FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>

<!-- Breadcrumb -->
<section class="bg-white border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="cart.php" class="text-decoration-none">Cart</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Checkout Content -->
<section class="py-4 py-md-5">
    <div class="container">
        <form action="checkout-process.php" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="row g-4">
                <!-- Left: Form -->
                <div class="col-lg-8">
                    
                    <!-- Shipping Info -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-geo-alt text-primary me-2"></i>Shipping Information
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name *</label>
                                    <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                           class="form-control form-control-lg" placeholder="Enter your name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile Number *</label>
                                    <input type="tel" name="mobile" required value="<?= htmlspecialchars($user['mobile'] ?? '') ?>"
                                           class="form-control form-control-lg" placeholder="98XXXXXXXX" inputmode="numeric">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? '') ?>"
                                           class="form-control form-control-lg" placeholder="your@email.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Delivery Address *</label>
                                    <textarea name="address" required rows="3" class="form-control"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Order Notes (Optional)</label>
                                    <textarea name="notes" rows="2" class="form-control" placeholder="Special instructions..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-credit-card text-primary me-2"></i>Payment Method
                            </h4>
                            
                            <div class="row g-3">
                                <!-- COD -->
                                <div class="col-md-4">
                                    <label class="payment-option card h-100 border-2 rounded-3 p-3 cursor-pointer mb-0 <?= !isset($_POST['payment_method']) || $_POST['payment_method'] === 'COD' ? 'border-primary' : 'border-light' ?>">
                                        <input type="radio" name="payment_method" value="COD" checked class="d-none" onclick="togglePaymentProof(false)">
                                        <div class="card-body text-center">
                                            <i class="bi bi-cash display-6 text-muted mb-2"></i>
                                            <h6 class="fw-bold mb-1">COD</h6>
                                            <small class="text-muted">Cash on Delivery</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <!-- eSewa -->
                                <div class="col-md-4">
                                    <label class="payment-option card h-100 border-2 rounded-3 p-3 cursor-pointer mb-0 <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'eSewa' ? 'border-primary' : 'border-light' ?>">
                                        <input type="radio" name="payment_method" value="eSewa" class="d-none" onclick="togglePaymentProof(true)">
                                        <div class="card-body text-center">
                                            <span class="badge bg-success mb-2">eSewa</span>
                                            <h6 class="fw-bold mb-1">Digital Payment</h6>
                                            <small class="text-muted">Pay via eSewa</small>
                                        </div>
                                    </label>
                                </div>
                                
                                <!-- Bank QR -->
                                <div class="col-md-4">
                                    <label class="payment-option card h-100 border-2 rounded-3 p-3 cursor-pointer mb-0 <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'BankQR' ? 'border-primary' : 'border-light' ?>">
                                        <input type="radio" name="payment_method" value="BankQR" class="d-none" onclick="togglePaymentProof(true)">
                                        <div class="card-body text-center">
                                            <i class="bi bi-qr-code display-6 text-muted mb-2"></i>
                                            <h6 class="fw-bold mb-1">Bank QR</h6>
                                            <small class="text-muted">FonePay / ConnectIPS</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Payment Proof Section -->
                            <div id="payment-proof-section" class="mt-4 p-4 bg-light rounded-3 <?= !isset($_POST['payment_method']) || $_POST['payment_method'] === 'COD' ? 'd-none' : '' ?>">
                                <div class="row g-4 align-items-center">
                                    <div class="col-md-5 text-center">
                                        <img src="https://via.placeholder.com/200x200?text=SCAN+QR" class="img-fluid rounded-3 mb-2" alt="QR Code">
                                        <p class="small text-muted mb-0">Scan to pay</p>
                                        <small class="text-primary fw-semibold">ARS ENTERPRISES PVT LTD</small>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Transaction ID *</label>
                                            <input type="text" name="transaction_id" id="txn_id"
                                                   class="form-control" placeholder="e.g. 192X...">
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Payment Screenshot *</label>
                                            <input type="file" name="payment_proof" id="p_proof" accept="image/jpeg,image/png,image/webp"
                                                   class="form-control" placeholder="Upload screenshot">
                                            <small class="text-muted">JPG, PNG, WEBP - Max 2MB</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right: Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">Order Review</h4>
                            
                            <!-- Cart Items -->
                            <div class="overflow-auto" style="max-height: 200px;">
                                <?php foreach($cart as $id => $item): ?>
                                    <div class="d-flex gap-3 mb-3">
                                        <img src="<?= !empty($item['image']) ? UPLOAD_DIR . htmlspecialchars($item['image']) : 'https://via.placeholder.com/60' ?>" 
                                             class="rounded-2 bg-light p-1" alt="" style="width: 50px; height: 50px; object-fit: contain;">
                                        <div class="flex-grow-1">
                                            <p class="small fw-semibold mb-0 text-truncate"><?= htmlspecialchars($item['name']) ?></p>
                                            <p class="small text-muted mb-0"><?= (int)$item['qty'] ?> x <?= formatPrice($item['price']) ?></p>
                                        </div>
                                        <p class="small fw-bold mb-0"><?= formatPrice($item['price'] * $item['qty']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            
                            <!-- Summary -->
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold"><?= formatPrice($subtotal) ?></span>
                            </div>
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
                            
                            <!-- Place Order -->
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-shield-check me-2"></i>Place Order
                            </button>
                            
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-lock me-1"></i>Your payment is secure
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
    function togglePaymentProof(show) {
        const section = document.getElementById('payment-proof-section');
        const txnIdInput = document.getElementById('txn_id');
        const proofInput = document.getElementById('p_proof');
        
        if (show) {
            section.classList.remove('d-none');
            txnIdInput.required = true;
            proofInput.required = true;
        } else {
            section.classList.add('d-none');
            txnIdInput.required = false;
            proofInput.required = false;
        }
    }
    
    // Payment option selection
    document.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('border-primary'));
            this.classList.add('border-primary');
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
