<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

require_csrf();

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comment = trim($_POST['comment'] ?? '');

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

if (!$rating || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a rating (1-5 stars).']);
    exit;
}

if (empty($comment) || strlen($comment) < 5) {
    echo json_encode(['success' => false, 'message' => 'Please write at least 5 characters in your review.']);
    exit;
}

if (strlen($comment) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Review is too long (max 1000 characters).']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    $checkPurchase = $pdo->prepare("
        SELECT o.id FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.delivery_status = 'Delivered'
        LIMIT 1
    ");
    $checkPurchase->execute([$user_id, $product_id]);
    $purchaseRow  = $checkPurchase->fetch();
    $hasPurchased = (bool)$purchaseRow;

    $stmt = $pdo->prepare("
        INSERT INTO product_reviews (product_id, user_id, order_id, rating, comment, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$product_id, $user_id, $hasPurchased ? $purchaseRow['id'] : null, $rating, $comment]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you! Your review has been submitted and is pending approval.'
    ]);
    
} catch (PDOException $e) {
    error_log("Review submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
}
