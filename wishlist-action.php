<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to use wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'toggle';

if (!$product_id) {
    redirect('shop.php');
}

try {
    if ($action === 'add' || $action === 'toggle') {
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            if ($action === 'toggle') {
                $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
                $msg = "Removed from wishlist";
            } else {
                $msg = "Already in wishlist";
            }
        } else {
            $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$user_id, $product_id]);
            $msg = "Added to wishlist";
        }
    } elseif ($action === 'remove') {
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
        $msg = "Removed from wishlist";
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['status' => 'success', 'message' => $msg]);
    } else {
        redirect($_SERVER['HTTP_REFERER'] ?? 'wishlist.php', $msg);
    }

} catch (PDOException $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    } else {
        redirect('shop.php', "Error: " . $e->getMessage(), "danger");
    }
}
?>
