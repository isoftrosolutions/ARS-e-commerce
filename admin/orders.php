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
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name as customer_name, u.mobile as customer_mobile
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
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
} catch (PDOException $e) {
    $orders = [];
    $total_pages = 1;
}

admin_header('Orders', 'orders');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">Manage and process customer orders</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1;">
        <select name="status" class="form-select" style="width: 200px;">
            <option value="">All Delivery Status</option>
            <option value="Pending"   <?php echo ($_GET['status'] ?? '') === 'Pending'    ? 'selected' : ''; ?>>Pending</option>
            <option value="Confirmed" <?php echo ($_GET['status'] ?? '') === 'Confirmed'  ? 'selected' : ''; ?>>Confirmed</option>
            <option value="Shipped"   <?php echo ($_GET['status'] ?? '') === 'Shipped'    ? 'selected' : ''; ?>>Shipped</option>
            <option value="Delivered" <?php echo ($_GET['status'] ?? '') === 'Delivered'  ? 'selected' : ''; ?>>Delivered</option>
            <option value="Cancelled" <?php echo ($_GET['status'] ?? '') === 'Cancelled'  ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <select name="payment" class="form-select" style="width: 200px;">
            <option value="">All Payment Status</option>
            <option value="Pending" <?php echo ($_GET['payment'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Paid"    <?php echo ($_GET['payment'] ?? '') === 'Paid'    ? 'selected' : ''; ?>>Paid</option>
            <option value="Failed"  <?php echo ($_GET['payment'] ?? '') === 'Failed'  ? 'selected' : ''; ?>>Failed</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if (!empty($_GET['status']) || !empty($_GET['payment'])): ?>
        <a href="orders.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrapper">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Date</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <?php
                    $pay_map = [
                        'Paid'    => 'badge-success',
                        'Pending' => 'badge-warning',
                        'Failed'  => 'badge-danger',
                    ];
                    $del_map = [
                        'Pending'   => 'badge-warning',
                        'Confirmed' => 'badge-info',
                        'Shipped'   => 'badge-orange',
                        'Delivered' => 'badge-success',
                        'Cancelled' => 'badge-danger',
                    ];
                    $pay_cls = $pay_map[$order['payment_status']] ?? 'badge-muted';
                    $del_cls = $del_map[$order['delivery_status']] ?? 'badge-muted';
                ?>
                <tr>
                    <td>
                        <span class="order-id">#ARS-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                    </td>
                    <td>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></div>
                        <div style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($order['customer_mobile'] ?? ''); ?></div>
                    </td>
                    <td style="font-family:var(--font-mono);font-weight:600;"><?php echo formatPrice($order['total_amount']); ?></td>
                    <td><span class="badge <?php echo $pay_cls; ?>"><span class="badge-dot"></span><?php echo htmlspecialchars($order['payment_status']); ?></span></td>
                    <td><span class="badge <?php echo $del_cls; ?>"><span class="badge-dot"></span><?php echo htmlspecialchars($order['delivery_status']); ?></span></td>
                    <td>
                        <div style="font-size:14px;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                        <div style="font-size:12px;color:var(--text-secondary);"><?php echo date('h:i A', strtotime($order['created_at'])); ?></div>
                    </td>
                    <td class="col-actions">
                        <div class="actions-cell">
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-ghost btn-sm btn-icon" title="View Details">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <button class="btn btn-ghost btn-sm btn-icon" onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['delivery_status']; ?>', '<?php echo $order['payment_status']; ?>', '<?php echo addslashes($order['current_location'] ?? ''); ?>')" title="Update Status">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px;"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
                            <div class="empty-title">No orders found</div>
                            <div class="empty-text">Try adjusting your filter criteria.</div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="table-footer">
        <span></span>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&payment=<?php echo urlencode($_GET['payment'] ?? ''); ?>"
               class="page-btn <?php echo $page === $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h2 class="modal-title">Update Order Status</h2>
            <button class="modal-close" onclick="closeModal('statusModal')" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="order-action.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="modalOrderId">

                <div class="form-group">
                    <label class="form-label">Delivery Status</label>
                    <select name="delivery_status" id="modalDeliveryStatus" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top:20px;margin-bottom:0;">
                    <label class="form-label">Current Product Location</label>
                    <input type="text" name="current_location" id="modalCurrentLocation" class="form-input" placeholder="e.g. Kathmandu Hub, Out for Delivery">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('statusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(id, deliveryStatus, paymentStatus, location) {
    document.getElementById('modalOrderId').value = id;
    document.getElementById('modalDeliveryStatus').value = deliveryStatus;
    document.getElementById('modalPaymentStatus').value = paymentStatus;
    document.getElementById('modalCurrentLocation').value = location || '';
    openModal('statusModal');
}
</script>

<?php admin_footer(); ?>
