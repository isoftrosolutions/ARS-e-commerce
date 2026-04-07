<?php
// admin/orders.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filters
$where = "WHERE 1=1";
$params = [];

if (!empty($_GET['status'])) {
    $where .= " AND o.delivery_status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['payment'])) {
    $where .= " AND o.payment_status = ?";
    $params[] = $_GET['payment'];
}

// Fetch Orders
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.mobile as customer_mobile
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $where 
    ORDER BY o.created_at DESC 
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o $where");
$count_stmt->execute($params);
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

admin_header('Orders', 'orders');
?>

<div class="flex-between mb-6" style="margin-bottom: 24px;">
    <form method="GET" style="display: flex; gap: 12px; flex: 1; max-width: 600px;">
        <select name="status" class="form-control">
            <option value="">All Delivery Status</option>
            <option value="Pending" <?php echo ($_GET['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Confirmed" <?php echo ($_GET['status'] ?? '') === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
            <option value="Shipped" <?php echo ($_GET['status'] ?? '') === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
            <option value="Delivered" <?php echo ($_GET['status'] ?? '') === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
            <option value="Cancelled" <?php echo ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <select name="payment" class="form-control">
            <option value="">All Payment Status</option>
            <option value="Pending" <?php echo ($_GET['payment'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Paid" <?php echo ($_GET['payment'] ?? '') === 'Paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="Failed" <?php echo ($_GET['payment'] ?? '') === 'Failed' ? 'selected' : ''; ?>>Failed</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;">#<?php echo $order['id']; ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo $order['payment_method']; ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 500;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo $order['customer_mobile']; ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo formatPrice($order['total_amount']); ?></div>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($order['payment_status']); ?>">
                            <?php echo $order['payment_status']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($order['delivery_status']); ?>">
                            <?php echo $order['delivery_status']; ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 14px;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);"><?php echo date('h:i A', strtotime($order['created_at'])); ?></div>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-ghost btn-sm" title="View Details">
                                <i data-lucide="eye" style="width: 16px;"></i>
                            </a>
                            <button class="btn btn-ghost btn-sm" onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['delivery_status']; ?>', '<?php echo $order['payment_status']; ?>')" title="Update Status">
                                <i data-lucide="refresh-cw" style="width: 16px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-secondary);">
                        <i data-lucide="shopping-bag" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.2;"></i>
                        <p>No orders found.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal-overlay">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Update Order Status</h2>
            <button class="btn btn-ghost btn-sm" onclick="Modal.close('statusModal')">&times;</button>
        </div>
        <form action="order-action.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="modalOrderId">
                
                <div class="form-group">
                    <label class="form-label">Delivery Status</label>
                    <select name="delivery_status" id="modalDeliveryStatus" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" id="modalPaymentStatus" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="Modal.close('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(id, deliveryStatus, paymentStatus) {
    document.getElementById('modalOrderId').value = id;
    document.getElementById('modalDeliveryStatus').value = deliveryStatus;
    document.getElementById('modalPaymentStatus').value = paymentStatus;
    Modal.open('statusModal');
}
</script>

<?php admin_footer(); ?>
