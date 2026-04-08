<?php
// admin/order-details.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    redirect('orders.php');
}

// Fetch Order (LEFT JOIN to handle guest orders)
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.mobile as customer_mobile
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
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
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$pay_map = ['Paid' => 'badge-success', 'Pending' => 'badge-warning', 'Failed' => 'badge-danger'];
$del_map = ['Pending' => 'badge-warning', 'Confirmed' => 'badge-info', 'Shipped' => 'badge-orange', 'Delivered' => 'badge-success', 'Cancelled' => 'badge-danger'];

admin_header("Order #ARS-" . str_pad($id, 4, '0', STR_PAD_LEFT), 'orders');
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="orders.php">Orders</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span>#ARS-<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <h1 class="page-title" style="margin-bottom:0;">Order #ARS-<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></h1>
            <span class="badge <?php echo $del_map[$order['delivery_status']] ?? 'badge-muted'; ?>">
                <span class="badge-dot"></span><?php echo htmlspecialchars($order['delivery_status']); ?>
            </span>
        </div>
        <p class="page-subtitle">Placed <?php echo date('F j, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <div class="page-actions">
        <a href="orders.php" class="btn btn-ghost">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Orders
        </a>
        <button class="btn btn-ghost" onclick="window.print()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print Invoice
        </button>
        <button class="btn btn-primary" onclick="openStatusModal(<?php echo $id; ?>, '<?php echo $order['delivery_status']; ?>', '<?php echo $order['payment_status']; ?>', '<?php echo addslashes($order['current_location'] ?? ''); ?>')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            Update Status
        </button>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">

    <!-- LEFT COLUMN -->
    <div style="display:flex;flex-direction:column;gap:24px;">

        <!-- Order Items -->
        <div class="table-wrapper">
            <div class="card-header">
                <span class="card-title">Order Items</span>
                <span style="font-size:12px;color:var(--text-muted);"><?php echo count($items); ?> item<?php echo count($items) !== 1 ? 's' : ''; ?></span>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <?php if ($item['product_image']): ?>
                                    <img src="<?php echo SITE_URL . '/uploads/' . htmlspecialchars($item['product_image']); ?>"
                                        style="width:40px;height:40px;border-radius:var(--r-md);object-fit:cover;background:var(--bg-card);flex-shrink:0;" alt="">
                                    <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:var(--r-md);background:var(--bg-card);display:flex;align-items:center;justify-content:center;color:var(--text-muted);flex-shrink:0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                    <?php endif; ?>
                                    <span style="font-weight:600;font-size:13.5px;"><?php echo htmlspecialchars($item['product_name'] ?? 'Deleted Product'); ?></span>
                                </div>
                            </td>
                            <td><span style="font-family:var(--font-mono);font-size:12px;color:var(--text-muted);"><?php echo htmlspecialchars($item['sku'] ?: '—'); ?></span></td>
                            <td style="font-family:var(--font-mono);"><?php echo formatPrice($item['price']); ?></td>
                            <td><span class="badge badge-muted"><?php echo (int)$item['quantity']; ?></span></td>
                            <td style="text-align:right;font-family:var(--font-mono);font-weight:600;">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Order total -->
            <div style="padding:16px 20px;background:var(--bg-card);border-top:1px solid var(--border);display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <div style="display:flex;justify-content:space-between;width:220px;font-size:13px;">
                    <span style="color:var(--text-secondary);">Subtotal</span>
                    <span style="font-family:var(--font-mono);"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;width:220px;font-size:13px;">
                    <span style="color:var(--text-secondary);">Shipping</span>
                    <span style="font-family:var(--font-mono);color:var(--success);">Free</span>
                </div>
                <div style="display:flex;justify-content:space-between;width:220px;padding-top:10px;border-top:1px solid var(--border);font-size:15px;font-weight:700;">
                    <span>Total</span>
                    <span style="font-family:var(--font-mono);color:var(--accent);"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--accent);"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Shipping Address
            </h3>
            <p style="white-space:pre-line;line-height:1.7;font-size:13.5px;color:var(--text-secondary);"><?php echo htmlspecialchars($order['address'] ?: '—'); ?></p>
        </div>

        <?php if ($order['notes']): ?>
        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">Order Notes</h3>
            <p style="font-style:italic;color:var(--text-secondary);font-size:13.5px;"><?php echo htmlspecialchars($order['notes']); ?></p>
        </div>
        <?php endif; ?>

    </div>

    <!-- RIGHT COLUMN -->
    <div style="display:flex;flex-direction:column;gap:24px;">

        <!-- Customer Info -->
        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;">Customer</h3>
            <?php if ($order['customer_name']): ?>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:44px;height:44px;border-radius:var(--r-full);background:var(--accent-dim);border:1.5px solid var(--border-accent);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--accent);flex-shrink:0;">
                    <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight:600;font-size:14px;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Customer ID: #<?php echo $order['user_id']; ?></div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php if ($order['customer_email']): ?>
                <div style="display:flex;align-items:center;gap:9px;font-size:13px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);flex-shrink:0;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span style="color:var(--text-secondary);"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['customer_mobile']): ?>
                <div style="display:flex;align-items:center;gap:9px;font-size:13px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);flex-shrink:0;"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.1 1.18 2 2 0 012.11.01h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.56-.56a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/></svg>
                    <span style="color:var(--text-secondary);"><?php echo htmlspecialchars($order['customer_mobile']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="display:flex;align-items:center;gap:10px;color:var(--text-muted);font-size:13px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Guest Order
            </div>
            <?php endif; ?>
        </div>

        <!-- Payment Info -->
        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;">Payment</h3>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                    <span style="color:var(--text-secondary);">Method</span>
                    <span class="badge badge-info"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                    <span style="color:var(--text-secondary);">Status</span>
                    <span class="badge <?php echo $pay_map[$order['payment_status']] ?? 'badge-muted'; ?>">
                        <span class="badge-dot"></span><?php echo htmlspecialchars($order['payment_status']); ?>
                    </span>
                </div>
                <?php if ($order['transaction_id']): ?>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:0.05em;">Transaction ID</div>
                    <code style="font-family:var(--font-mono);font-size:12px;background:var(--bg-overlay);padding:5px 9px;border-radius:var(--r-sm);color:var(--text-secondary);display:block;word-break:break-all;"><?php echo htmlspecialchars($order['transaction_id']); ?></code>
                </div>
                <?php endif; ?>
                <?php if ($order['payment_proof']): ?>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Payment Proof</div>
                    <a href="<?php echo SITE_URL . '/' . htmlspecialchars($order['payment_proof']); ?>" target="_blank">
                        <img src="<?php echo SITE_URL . '/' . htmlspecialchars($order['payment_proof']); ?>"
                            alt="Payment Proof"
                            style="width:100%;border-radius:var(--r-md);border:1px solid var(--border);transition:opacity var(--t-fast);"
                            onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="card card-body">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">Actions</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <button class="btn btn-primary" style="width:100%;" onclick="openStatusModal(<?php echo $id; ?>, '<?php echo $order['delivery_status']; ?>', '<?php echo $order['payment_status']; ?>', '<?php echo addslashes($order['current_location'] ?? ''); ?>')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                    Update Status
                </button>
                <?php if ($order['delivery_status'] !== 'Cancelled'): ?>
                <form action="order-action.php" method="POST"
                    onsubmit="return confirm('Cancel this order? This cannot be undone.')">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="delivery_status" value="Cancelled">
                    <input type="hidden" name="payment_status" value="<?php echo htmlspecialchars($order['payment_status']); ?>">
                    <button type="submit" class="btn btn-danger" style="width:100%;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Cancel Order
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /right column -->

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
                <input type="hidden" name="redirect" value="order-details.php?id=<?php echo $id; ?>">

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

                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" id="modalPaymentStatus" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Failed">Failed</option>
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
