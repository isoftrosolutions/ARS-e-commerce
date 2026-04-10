<?php
// includes/functions.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/security.php';

function formatPrice($price) {
    return CURRENCY . number_format((float)$price, 0);
}

/**
 * Safely resolves product image path to avoid double-prefixing
 */
function getProductImage($path) {
    if (empty($path)) return 'https://via.placeholder.com/400x400?text=No+Image';
    
    // If it already contains 'uploads/', strip it to avoid double prefixing
    // especially for older session data or manual entries
    if (strpos($path, 'uploads/') === 0) {
        $path = substr($path, 8);
    }
    
    return UPLOAD_DIR . $path;
}

function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['msg'] = $message;
        $_SESSION['msg_type'] = $type;
    }
    $safeUrl = filter_var($url, FILTER_SANITIZE_URL);
    session_write_close(); // Ensure session is saved before redirecting
    header("Location: $safeUrl");
    exit();
}

function display_message() {
    if (isset($_SESSION['msg'])) {
        $msg = htmlspecialchars($_SESSION['msg']);
        $type = $_SESSION['msg_type'] ?? 'info';
        unset($_SESSION['msg'], $_SESSION['msg_type']);
        
        $alertClass = match($type) {
            'success' => 'bg-green-50 border-green-200 text-green-700',
            'danger' => 'bg-red-50 border-red-200 text-red-700',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
            default => 'bg-blue-50 border-blue-200 text-blue-700'
        };
        
        echo "<div class='fixed top-24 right-4 z-50 max-w-sm p-4 rounded-xl border shadow-lg $alertClass animate-fade-in-down'>";
        echo "<div class='flex items-center gap-3'>";
        echo "<span>$msg</span>";
        echo "</div>";
        echo "</div>";
        echo "<script>setTimeout(() => document.querySelector('.animate-fade-in-down')?.remove(), 5000);</script>";
    }
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function get_cart_count(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}

function get_user_email(): string {
    if (!is_logged_in()) return '';
    global $pdo;
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user['email'] ?? '';
}

function json_response($data, $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
