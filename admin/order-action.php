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
    
    $stmt = $pdo->prepare("UPDATE orders SET delivery_status = ?, payment_status = ? WHERE id = ?");
    if ($stmt->execute([$delivery_status, $payment_status, $id])) {
        redirect('orders.php', 'Order status updated successfully.');
    } else {
        redirect('orders.php', 'Failed to update order status.', 'danger');
    }
}
