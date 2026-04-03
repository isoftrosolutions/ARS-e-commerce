<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('shop.php', "Your cart is empty.", "info");
}

// 1. Get input data
$full_name = htmlspecialchars(trim($_POST['full_name']));
$mobile = htmlspecialchars(trim($_POST['mobile']));
$address = htmlspecialchars(trim($_POST['address']));
$notes = htmlspecialchars(trim($_POST['notes']));
$payment_method = $_POST['payment_method'];
$transaction_id = $_POST['transaction_id'] ?? '';

// Re-calculate total server-side for security
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$shipping_threshold = 1000;
$shipping_fee = ($subtotal >= $shipping_threshold) ? 0 : 150;
$total_amount = $subtotal + $shipping_fee;

// 2. Handle Payment Proof Upload
$payment_proof = '';
if (($payment_method === 'eSewa' || $payment_method === 'BankQR') && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
    $file = $_FILES['payment_proof'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    
    if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) {
        $payment_proof = $filename;
    }
}

try {
    $pdo->beginTransaction();

    // Check stock for all items first
    foreach ($cart as $product_id => $item) {
        $stmt_stock = $pdo->prepare("SELECT stock, name FROM products WHERE id = ?");
        $stmt_stock->execute([$product_id]);
        $prod = $stmt_stock->fetch();
        if ($prod['stock'] < $item['qty']) {
            throw new Exception("Insufficient stock for: " . $prod['name']);
        }
    }

    $user_id = $_SESSION['user_id'] ?? null;
    $payment_status = ($payment_method === 'COD') ? 'Pending' : 'Paid';
    $delivery_status = 'Pending';
    
    $full_delivery_info = "$full_name ($mobile) | $address";

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, delivery_status, transaction_id, payment_proof, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        $total_amount,
        $payment_method,
        $payment_status,
        $delivery_status,
        $transaction_id,
        $payment_proof,
        $full_delivery_info,
        $notes
    ]);
    
    $order_id = $pdo->lastInsertId();

    // Add Order Items & Reduce Stock
    $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_stock_update = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    
    foreach ($cart as $product_id => $item) {
        $stmt_item->execute([$order_id, $product_id, $item['qty'], $item['price']]);
        $stmt_stock_update->execute([$item['qty'], $product_id]);
    }

    $pdo->commit();

    // --- NEW: Email Integration ---
    try {
        require_once __DIR__ . '/includes/classes/EmailManager.php';
        $emailMgr = new EmailManager($pdo);
        
        // Get user email if logged in, otherwise use input (assuming guest email exists)
        $user_email = $_SESSION['user_email'] ?? $email ?? ''; // You might need to capture email in checkout form
        if ($user_email) {
            $emailMgr->queue($user_email, $full_name, 'order_confirmation', [
                'name' => $full_name,
                'order_id' => $order_id,
                'total' => 'Rs. ' . number_format($total_amount, 2)
            ]);
        }
    } catch (Exception $e) {
        // Log error but don't break the success flow
        error_log("Email Queue Error: " . $e->getMessage());
    }
    // ------------------------------

    // Clear cart
    unset($_SESSION['cart']);
    
    redirect("order-success.php?id=$order_id", "Order placed successfully!");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect('checkout.php', $e->getMessage(), "danger");
}
?>
