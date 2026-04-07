<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

require_csrf();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('shop.php', "Your cart is empty.", "info");
}

$full_name = sanitize_input($_POST['full_name'] ?? '');
$mobile = sanitize_input($_POST['mobile'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$address = sanitize_input($_POST['address'] ?? '');
$notes = sanitize_input($_POST['notes'] ?? '');
$payment_method = sanitize_input($_POST['payment_method'] ?? '');
$transaction_id = sanitize_input($_POST['transaction_id'] ?? '');

$errors = [];
if (empty($full_name)) $errors[] = 'Name is required';
if (!validate_mobile($mobile)) $errors[] = 'Invalid mobile number';
if (empty($address)) $errors[] = 'Address is required';
if (!in_array($payment_method, ['COD', 'eSewa', 'BankQR'])) $errors[] = 'Invalid payment method';

if (!empty($email) && !validate_email($email)) {
    $errors[] = 'Invalid email format';
}

if (!empty($errors)) {
    redirect('checkout.php', implode(', ', $errors), 'danger');
}

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['qty'];
}

$shipping_fee = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_FEE;

$coupon_discount = 0;
$coupon_code = null;
if (isset($_SESSION['coupon'])) {
    require_once __DIR__ . '/includes/coupon.php';
    $result = apply_coupon($_SESSION['coupon']['code'], $subtotal);
    if ($result['valid']) {
        $coupon_discount = $result['discount'];
        $coupon_code = $_SESSION['coupon']['code'];
    } else {
        unset($_SESSION['coupon']); // Invalidated code
    }
}

$total_amount = max(0, $subtotal + $shipping_fee - $coupon_discount);
$payment_proof = '';
if (($payment_method === 'eSewa' || $payment_method === 'BankQR') && isset($_FILES['payment_proof'])) {
    $uploader = new SecureFileUpload('payments');
    $result = $uploader->upload($_FILES['payment_proof'], 'proof');
    
    if (!$result['success']) {
        redirect('checkout.php', $result['error'], 'danger');
    }
    $payment_proof = $result['filename'];
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, stock, name, price FROM products WHERE id IN (" . implode(',', array_fill(0, count($cart), '?')) . ") FOR UPDATE");
    $stmt->execute(array_keys($cart));
    $products = [];
    while ($row = $stmt->fetch()) {
        $products[$row['id']] = $row;
    }

    foreach ($cart as $product_id => $item) {
        $product_id = (int)$product_id;
        if (!isset($products[$product_id])) {
            throw new Exception("Product not found: ID $product_id");
        }
        
        $product = $products[$product_id];
        if ($product['stock'] < $item['qty']) {
            throw new Exception("Insufficient stock for: " . $product['name'] . " (Available: " . $product['stock'] . ")");
        }
        
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt->execute([$item['qty'], $product_id, $item['qty']]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Failed to reserve stock for: " . $product['name']);
        }
    }

    $user_id = $_SESSION['user_id'] ?? null;
    $payment_status = ($payment_method === 'COD') ? 'Pending' : 'Paid';
    $delivery_status = 'Pending';
    $full_delivery_info = "$full_name ($mobile)" . ($email ? " | $email" : "") . " | $address";

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, coupon_code, discount_amount, payment_method, payment_status, delivery_status, transaction_id, payment_proof, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $total_amount,
        $coupon_code,
        $coupon_discount,
        $payment_method,
        $payment_status,
        $delivery_status,
        $transaction_id,
        $payment_proof,
        $full_delivery_info,
        $notes
    ]);

    if ($coupon_code) {
        $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$coupon_code]);
        unset($_SESSION['coupon']);
    }
    
    $order_id = $pdo->lastInsertId();

    $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $product_id => $item) {
        $stmt_item->execute([$order_id, (int)$product_id, $item['qty'], $item['price']]);
    }

    $pdo->commit();

    try {
        require_once __DIR__ . '/includes/classes/EmailManager.php';
        $emailMgr = new EmailManager($pdo);
        $user_email = $email ?: ($_SESSION['user_email'] ?? '');
        if ($user_email) {
            $emailMgr->queue($user_email, $full_name, 'order_confirmation', [
                'name' => $full_name,
                'order_id' => $order_id,
                'total' => formatPrice($total_amount)
            ]);
        }
    } catch (Exception $e) {
        error_log("Email Queue Error: " . $e->getMessage());
    }

    unset($_SESSION['cart']);
    redirect("order-success.php?id=$order_id", "Order placed successfully!");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Checkout error: " . $e->getMessage());
    redirect('checkout.php', $e->getMessage(), "danger");
}
