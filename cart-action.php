<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action === 'add' && $id > 0) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if item already exists in cart
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty']++;
    } else {
        // Fetch product info
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            $_SESSION['cart'][$id] = [
                'name' => $product['name'],
                'price' => $product['discount_price'] ?: $product['price'],
                'image' => $product['image'],
                'qty' => 1
            ];
            redirect('index.php', "Added {$product['name']} to cart!");
        } else {
            redirect('index.php', "Product not found.", "danger");
        }
    }
    redirect('cart.php', "Added to cart!");
}

if ($action === 'remove' && $id > 0) {
    unset($_SESSION['cart'][$id]);
    redirect('cart.php', "Removed from cart.");
}

if ($action === 'update' && $id > 0) {
    $qty = (int)($_GET['qty'] ?? 1);
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    } else {
        $_SESSION['cart'][$id]['qty'] = $qty;
    }
    redirect('cart.php', "Cart updated.");
}

redirect('index.php');
?>
