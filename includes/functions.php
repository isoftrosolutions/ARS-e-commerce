<?php
// includes/functions.php
require_once __DIR__ . '/../config/db.php';

// Format currency
function formatPrice($price) {
    return CURRENCY . number_format($price, 0);
}

// Redirect with a message
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['msg'] = $message;
        $_SESSION['msg_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Display messages
function display_message() {
    if (isset($_SESSION['msg'])) {
        $msg = $_SESSION['msg'];
        $type = $_SESSION['msg_type'] ?? 'info';
        unset($_SESSION['msg'], $_SESSION['msg_type']);
        echo "<div class='alert alert-{$type}'>$msg</div>";
    }
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if admin
function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// Get cart count
function get_cart_count() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}
?>
