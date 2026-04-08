<?php
// admin/coupons.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Ensure coupons table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        type ENUM('fixed', 'percentage') DEFAULT 'fixed',
        value DECIMAL(10,2) NOT NULL,
        min_cart_amount DECIMAL(10,2) DEFAULT 0,
        max_discount DECIMAL(10,2) DEFAULT NULL,
        max_uses INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        expires_at DATETIME DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $coupons = [];
}

admin_header('Coupons', 'coupons');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Coupons</h1>
        <p class="page-subtitle">Manage discount codes for your store</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openCouponModal()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Coupon
        </button>
    </div>
</div>

<div class="table-wrapper">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Discount</th>
                    <th>Min. Cart</th>
                    <th>Usage</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon): ?>
                <tr>
                    <td>
                        <code style="font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--accent);background:var(--accent-dim);padding:3px 8px;border-radius:var(--r-sm);">
                            <?php echo htmlspecialchars($coupon['code']); ?>
                        </code>
                    </td>
                    <td>
                        <span class="badge <?php echo $coupon['type'] === 'percentage' ? 'badge-purple' : 'badge-info'; ?>">
                            <?php echo ucfirst($coupon['type']); ?>
                        </span>
                    </td>
                    <td style="font-family:var(--font-mono);font-weight:600;">
                        <?php if ($coupon['type'] === 'percentage'): ?>
                            <?php echo (float)$coupon['value']; ?>%
                            <?php if ($coupon['max_discount']): ?>
                            <span style="font-size:11px;color:var(--text-muted);">(max <?php echo formatPrice($coupon['max_discount']); ?>)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo formatPrice($coupon['value']); ?>
                        <?php endif; ?>
                    </td>
                    <td style="font-family:var(--font-mono);">
                        <?php echo $coupon['min_cart_amount'] > 0 ? formatPrice($coupon['min_cart_amount']) : '—'; ?>
                    </td>
                    <td>
                        <span style="font-family:var(--font-mono);"><?php echo (int)$coupon['used_count']; ?></span>
                        <?php if ($coupon['max_uses']): ?>
                        <span style="color:var(--text-muted);font-size:12px;"> / <?php echo (int)$coupon['max_uses']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12.5px;color:var(--text-secondary);">
                        <?php if ($coupon['expires_at']): ?>
                            <?php
                            $expired = strtotime($coupon['expires_at']) < time();
                            echo '<span style="color:' . ($expired ? 'var(--danger)' : 'inherit') . ';">';
                            echo date('M d, Y', strtotime($coupon['expires_at']));
                            echo $expired ? ' (expired)' : '';
                            echo '</span>';
                            ?>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">Never</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $coupon['status'] === 'active' ? 'badge-success' : 'badge-muted'; ?>">
                            <span class="badge-dot"></span><?php echo ucfirst($coupon['status']); ?>
                        </span>
                    </td>
                    <td class="col-actions">
                        <div class="actions-cell">
                            <button class="btn btn-ghost btn-sm btn-icon" title="Edit"
                                onclick="openCouponModal(<?php echo htmlspecialchars(json_encode($coupon)); ?>)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form action="coupon-action.php" method="POST" style="display:inline;"
                                onsubmit="return confirm('Delete coupon <?php echo addslashes($coupon['code']); ?>?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $coupon['id']; ?>">
                                <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($coupons)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </div>
                            <div class="empty-title">No coupons yet</div>
                            <div class="empty-text">Create your first discount code to attract customers.</div>
                            <button class="btn btn-primary" style="margin-top:12px;" onclick="openCouponModal()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add Coupon
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Coupon Modal -->
<div id="couponModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:540px;">
        <div class="modal-header">
            <h2 class="modal-title" id="couponModalTitle">Add Coupon</h2>
            <button class="modal-close" onclick="closeModal('couponModal')" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="coupon-action.php" method="POST">
            <div class="modal-body" style="display:flex;flex-direction:column;gap:0;">
                <input type="hidden" name="action" id="couponAction" value="create">
                <input type="hidden" name="id" id="couponId">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Coupon Code <span class="req">*</span></label>
                        <input type="text" name="code" id="couponCode" class="form-input" required placeholder="e.g. SAVE20"
                            style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span class="req">*</span></label>
                        <select name="type" id="couponType" class="form-select" onchange="updateValueLabel()">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" id="valueLabel">Discount Value <span class="req">*</span></label>
                        <input type="number" name="value" id="couponValue" class="form-input" required min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="form-group" id="maxDiscountGroup">
                        <label class="form-label">Max Discount (<?php echo CURRENCY; ?>) <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                        <input type="number" name="max_discount" id="couponMaxDiscount" class="form-input" min="0" step="0.01" placeholder="No limit">
                        <div class="form-helper">For percentage coupons only</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Min. Cart Amount (<?php echo CURRENCY; ?>) <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                        <input type="number" name="min_cart_amount" id="couponMinCart" class="form-input" min="0" step="0.01" placeholder="0 = no minimum">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Uses <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                        <input type="number" name="max_uses" id="couponMaxUses" class="form-input" min="1" placeholder="Unlimited">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Expires At <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                        <input type="datetime-local" name="expires_at" id="couponExpires" class="form-input">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Status</label>
                        <select name="status" id="couponStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('couponModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateValueLabel() {
    var type = document.getElementById('couponType').value;
    document.getElementById('valueLabel').innerHTML = (type === 'percentage' ? 'Discount % <span class="req">*</span>' : 'Discount Amount <span class="req">*</span>');
    document.getElementById('maxDiscountGroup').style.display = (type === 'percentage' ? '' : 'none');
}

function openCouponModal(data) {
    var isEdit = data && data.id;
    document.getElementById('couponModalTitle').textContent = isEdit ? 'Edit Coupon' : 'Add Coupon';
    document.getElementById('couponAction').value = isEdit ? 'update' : 'create';
    document.getElementById('couponId').value       = isEdit ? data.id : '';
    document.getElementById('couponCode').value     = isEdit ? data.code : '';
    document.getElementById('couponType').value     = isEdit ? data.type : 'fixed';
    document.getElementById('couponValue').value    = isEdit ? data.value : '';
    document.getElementById('couponMaxDiscount').value = isEdit && data.max_discount ? data.max_discount : '';
    document.getElementById('couponMinCart').value  = isEdit && data.min_cart_amount ? data.min_cart_amount : '';
    document.getElementById('couponMaxUses').value  = isEdit && data.max_uses ? data.max_uses : '';
    document.getElementById('couponStatus').value   = isEdit ? data.status : 'active';
    if (isEdit && data.expires_at) {
        document.getElementById('couponExpires').value = data.expires_at.replace(' ', 'T').substring(0, 16);
    } else {
        document.getElementById('couponExpires').value = '';
    }
    updateValueLabel();
    openModal('couponModal');
}

updateValueLabel();
</script>

<?php admin_footer(); ?>
