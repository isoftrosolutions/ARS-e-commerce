<?php
// admin/order-action.php
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('orders.php');
}

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $id = (int)$_POST['id'];
    $delivery_status = $_POST['delivery_status'];
    $payment_status = $_POST['payment_status'];
    $current_location = $_POST['current_location'] ?? '';
    
    // Check if location changed to update timestamp
    $stmt_check = $pdo->prepare("SELECT current_location FROM orders WHERE id = ?");
    $stmt_check->execute([$id]);
    $old_location = $stmt_check->fetchColumn();
    
    $sql = "UPDATE orders SET delivery_status = ?, payment_status = ?, current_location = ?";
    $params = [$delivery_status, $payment_status, $current_location];
    
    if ($old_location !== $current_location) {
        $sql .= ", location_updated_at = NOW()";
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $pdo->prepare($sql);
    $back = isset($_POST['redirect']) ? $_POST['redirect'] : 'orders.php';
    if ($stmt->execute($params)) {
        redirect($back, 'Order status updated successfully.');
    } else {
        redirect($back, 'Failed to update order status.', 'danger');
    }
}
