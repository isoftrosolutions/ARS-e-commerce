<?php
// admin/order-details.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    redirect('orders.php');
}

// Fetch Order
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.mobile as customer_mobile
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php', 'Order not found.', 'danger');
}

// Fetch Order Items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image, p.sku
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

admin_header("Order #$id", 'orders');
?>

<div class="flex-between mb-6" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <h2 style="margin-bottom: 0;">Order #<?php echo $id; ?></h2>
        <span class="badge badge-<?php echo strtolower($order['delivery_status']); ?>">
            <?php echo $order['delivery_status']; ?>
        </span>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="orders.php" class="btn btn-ghost">
            <i data-lucide="arrow-left"></i> Back to Orders
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i data-lucide="printer"></i> Print Invoice
        </button>
    </div>
</div>

<div class="grid grid-3" style="grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Left Column: Items and Details -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 0;">Order Items</h3>
            </div>
            <div class="table-container" style="border: none; border-radius: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td style="display: flex; align-items: center; gap: 12px;">
                                <?php if ($item['product_image']): ?>
                                <img src="<?php echo SITE_URL . '/' . $item['product_image']; ?>" alt="" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($item['sku'] ?: 'N/A'); ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right; font-weight: 600;">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 20px; background: var(--gray-50); display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                <div style="display: flex; justify-content: space-between; width: 200px;">
                    <span style="color: var(--text-secondary);">Subtotal:</span>
                    <span style="font-weight: 500;"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; width: 200px;">
                    <span style="color: var(--text-secondary);">Shipping:</span>
                    <span style="font-weight: 500;"><?php echo formatPrice(0); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; width: 200px; padding-top: 8px; border-top: 1px solid var(--gray-200);">
                    <span style="font-weight: 700; font-size: 1.1rem;">Total:</span>
                    <span style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px;">Shipping Address</h3>
            <p style="white-space: pre-line; line-height: 1.6;"><?php echo htmlspecialchars($order['address']); ?></p>
        </div>

        <?php if ($order['notes']): ?>
        <div class="card">
            <h3 style="margin-bottom: 20px;">Order Notes</h3>
            <p style="font-style: italic; color: var(--text-secondary);"><?php echo htmlspecialchars($order['notes']); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Customer and Payment -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <h3 style="margin-bottom: 20px;">Customer Info</h3>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem;">
                    <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Customer ID: #<?php echo $order['user_id']; ?></div>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="mail" style="width: 16px; color: var(--gray-400);"></i>
                    <span><?php echo htmlspecialchars($order['customer_email'] ?: 'N/A'); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="phone" style="width: 16px; color: var(--gray-400);"></i>
                    <span><?php echo htmlspecialchars($order['customer_mobile']); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px;">Payment Information</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Method:</span>
                    <span class="badge badge-info"><?php echo $order['payment_method']; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Status:</span>
                    <span class="badge badge-<?php echo strtolower($order['payment_status']); ?>"><?php echo $order['payment_status']; ?></span>
                </div>
                <?php if ($order['transaction_id']): ?>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="color: var(--text-secondary); font-size: 12px;">Transaction ID:</span>
                    <span style="font-family: monospace; background: var(--gray-100); padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        <?php echo htmlspecialchars($order['transaction_id']); ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if ($order['payment_proof']): ?>
                <div style="margin-top: 12px;">
                    <span style="color: var(--text-secondary); font-size: 12px; display: block; margin-bottom: 8px;">Payment Proof:</span>
                    <a href="<?php echo SITE_URL . '/' . $order['payment_proof']; ?>" target="_blank">
                        <img src="<?php echo SITE_URL . '/' . $order['payment_proof']; ?>" alt="Payment Proof" style="width: 100%; border-radius: 6px; border: 1px solid var(--border-color);">
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px;">Actions</h3>
            <button class="btn btn-primary" style="width: 100%; margin-bottom: 12px;" onclick="openStatusModal(<?php echo $id; ?>, '<?php echo $order['delivery_status']; ?>', '<?php echo $order['payment_status']; ?>')">
                <i data-lucide="refresh-cw"></i> Update Status
            </button>
            <form action="order-action.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="delivery_status" value="Cancelled">
                <input type="hidden" name="payment_status" value="<?php echo $order['payment_status']; ?>">
                <button type="submit" class="btn btn-ghost" style="width: 100%; color: var(--danger);">
                    <i data-lucide="x-circle"></i> Cancel Order
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Re-using the status modal from orders.php -->
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
