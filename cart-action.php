<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ($_POST['action'] ?? 'add');
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0;
$qty = filter_input(INPUT_GET, 'qty', FILTER_VALIDATE_INT) ?? 1;

if ($action !== 'remove_coupon' && $action !== 'clear_coupon' && $action !== 'apply_coupon') {
    if ($product_id <= 0) {
        redirect('shop.php', 'Invalid product.', 'danger');
    }
}

switch ($action) {
    case 'add':
        require_csrf();
        addToCart($product_id, $qty);
        break;
        
    case 'update':
        require_csrf();
        updateCart($product_id, $qty);
        break;
        
    case 'remove':
        require_csrf();
        removeFromCart($product_id);
        break;
        
    case 'apply_coupon':
        require_csrf();
        applyCoupon();
        break;
        
    case 'remove_coupon':
    case 'clear_coupon':
        require_csrf();
        clearCoupon();
        break;
        
    default:
        redirect('shop.php');
}

function addToCart($product_id, $qty) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, name, price, discount_price, stock, image FROM products WHERE id = ? AND stock > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        redirect('shop.php', 'Product not found or out of stock.', 'danger');
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $price = $product['discount_price'] ?? $product['price'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = [
            'name' => $product['name'],
            'price' => $price,
            'image' => $product['image'],
            'qty' => $qty
        ];
    }
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        json_response(['status' => 'success', 'message' => 'Added to cart', 'count' => get_cart_count()]);
    }
    
    redirect('cart.php', 'Product added to cart!');
}

function updateCart($product_id, $qty) {
    if (!isset($_SESSION['cart'][$product_id])) {
        redirect('cart.php');
    }
    
    if ($qty <= 0) {
        unset($_SESSION['cart'][$product_id]);
        redirect('cart.php', 'Item removed from cart.');
    }
    
    $_SESSION['cart'][$product_id]['qty'] = $qty;
    redirect('cart.php');
}

function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        json_response(['status' => 'success', 'message' => 'Removed from cart', 'count' => get_cart_count()]);
    }
    
    redirect('cart.php', 'Item removed from cart.');
}

function applyCoupon() {
    require_once __DIR__ . '/includes/coupon.php';
    
    $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
    
    if (empty($code)) {
        $_SESSION['coupon_error'] = 'Please enter a coupon code';
        redirect('cart.php');
    }
    
    $cart = $_SESSION['cart'] ?? [];
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += (float)$item['price'] * (int)$item['qty'];
    }
    
    $result = apply_coupon($code, $subtotal);
    
    if ($result['valid']) {
        $_SESSION['coupon'] = [
            'code' => $result['coupon']['code'],
            'id' => $result['coupon']['id'],
            'type' => $result['coupon']['type'],
            'value' => $result['coupon']['value']
        ];
        $_SESSION['coupon_success'] = "Coupon applied! You save " . formatPrice($result['discount']);
    } else {
        $_SESSION['coupon_error'] = $result['message'];
    }
    
    redirect('cart.php');
}

function clearCoupon() {
    unset($_SESSION['coupon']);
    $_SESSION['coupon_success'] = 'Coupon removed';
    redirect('cart.php');
}
