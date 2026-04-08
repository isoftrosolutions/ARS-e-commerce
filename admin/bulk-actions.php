<?php
// admin/bulk-actions.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Handle bulk operations
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = $_POST['op'] ?? '';

    if ($op === 'stock_update') {
        $min = (int)($_POST['stock_min'] ?? 0);
        $add = (int)($_POST['stock_add'] ?? 0);
        if ($add > 0) {
            $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE stock <= ?");
            $stmt->execute([$add, $min]);
            $count = $stmt->rowCount();
            $result = ['type' => 'success', 'msg' => "Added {$add} units to {$count} product" . ($count !== 1 ? 's' : '') . " with stock ≤ {$min}."];
        } else {
            $result = ['type' => 'danger', 'msg' => 'Units to add must be greater than zero.'];
        }
    }

    if ($op === 'price_adjust') {
        $pct = (float)($_POST['price_pct'] ?? 0);
        $dir = $_POST['price_dir'] === 'decrease' ? -1 : 1;
        $cat = (int)($_POST['price_cat'] ?? 0);

        if ($pct <= 0 || $pct > 90) {
            $result = ['type' => 'danger', 'msg' => 'Percentage must be between 1 and 90.'];
        } else {
            $factor = 1 + ($dir * $pct / 100);
            if ($cat > 0) {
                $stmt = $pdo->prepare("UPDATE products SET price = ROUND(price * ?, 2) WHERE category_id = ?");
                $stmt->execute([$factor, $cat]);
            } else {
                $stmt = $pdo->prepare("UPDATE products SET price = ROUND(price * ?, 2)");
                $stmt->execute([$factor]);
            }
            $count = $stmt->rowCount();
            $dir_label = $dir === 1 ? 'Increased' : 'Decreased';
            $result = ['type' => 'success', 'msg' => "{$dir_label} price by {$pct}% for {$count} product" . ($count !== 1 ? 's' : '') . "."];
        }
    }

    if ($op === 'cancel_old_orders') {
        $days = max(1, (int)($_POST['days'] ?? 30));
        $stmt = $pdo->prepare("UPDATE orders SET delivery_status='Cancelled' WHERE delivery_status='Pending' AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        $count = $stmt->rowCount();
        $result = ['type' => 'success', 'msg' => "Cancelled {$count} pending order" . ($count !== 1 ? 's' : '') . " older than {$days} days."];
    }

    if ($op === 'toggle_featured') {
        $cat = (int)($_POST['feat_cat'] ?? 0);
        $val = (int)($_POST['featured_val'] ?? 0);
        if ($cat > 0) {
            $stmt = $pdo->prepare("UPDATE products SET is_featured = ? WHERE category_id = ?");
            $stmt->execute([$val, $cat]);
        } else {
            $stmt = $pdo->prepare("UPDATE products SET is_featured = ?");
            $stmt->execute([$val]);
        }
        $count = $stmt->rowCount();
        $label = $val ? 'Featured' : 'unfeatured';
        $result = ['type' => 'success', 'msg' => "Marked {$count} product" . ($count !== 1 ? 's' : '') . " as {$label}."];
    }
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

admin_header('Bulk Actions', 'bulk-actions');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Bulk Actions</h1>
        <p class="page-subtitle">Perform batch operations on products and orders</p>
    </div>
</div>

<?php if ($result): ?>
<div class="alert alert-<?php echo $result['type']; ?> animate-fade-in" style="margin-bottom:20px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <div class="alert-body"><div class="alert-text"><?php echo $result['msg']; ?></div></div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

    <!-- Stock Update -->
    <div class="card card-body">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--success-dim);color:var(--success);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;">Restock Low Items</div>
                <div style="font-size:12px;color:var(--text-secondary);">Add stock to products below a threshold</div>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="op" value="stock_update">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Stock Threshold (≤)</label>
                    <input type="number" name="stock_min" class="form-input" value="10" min="0">
                    <div class="form-helper">Affect products at or below this stock level</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Units to Add</label>
                    <input type="number" name="stock_add" class="form-input" value="50" min="1">
                </div>
            </div>
            <button type="submit" class="btn btn-success" style="width:100%;margin-top:4px;"
                onclick="return confirm('This will add stock to all matching products. Proceed?')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Apply Stock Update
            </button>
        </form>
    </div>

    <!-- Price Adjust -->
    <div class="card card-body">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--accent-dim);color:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;">Bulk Price Adjustment</div>
                <div style="font-size:12px;color:var(--text-secondary);">Increase or decrease prices by a percentage</div>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="op" value="price_adjust">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Percentage (%)</label>
                    <input type="number" name="price_pct" class="form-input" value="10" min="1" max="90" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Direction</label>
                    <select name="price_dir" class="form-select">
                        <option value="increase">Increase</option>
                        <option value="decrease">Decrease</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Category <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                <select name="price_cat" class="form-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"
                onclick="return confirm('This will permanently change product prices. Proceed?')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                Apply Price Change
            </button>
        </form>
    </div>

    <!-- Cancel Old Orders -->
    <div class="card card-body">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--danger-dim);color:var(--danger);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;">Cancel Stale Orders</div>
                <div style="font-size:12px;color:var(--text-secondary);">Auto-cancel pending orders older than N days</div>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="op" value="cancel_old_orders">
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Orders Older Than (days)</label>
                <input type="number" name="days" class="form-input" value="30" min="1">
                <div class="form-helper">Only affects orders with Pending delivery status</div>
            </div>
            <button type="submit" class="btn btn-danger" style="width:100%;"
                onclick="return confirm('This will cancel all old pending orders. This cannot be undone. Proceed?')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Cancel Stale Orders
            </button>
        </form>
    </div>

    <!-- Featured Toggle -->
    <div class="card card-body">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:36px;height:36px;border-radius:var(--r-md);background:var(--warning-dim);color:var(--warning);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;">Bulk Featured Toggle</div>
                <div style="font-size:12px;color:var(--text-secondary);">Mark products as featured or regular in bulk</div>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="op" value="toggle_featured">
            <div class="form-group">
                <label class="form-label">Mark As</label>
                <select name="featured_val" class="form-select">
                    <option value="1">Featured</option>
                    <option value="0">Regular (unfeatured)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Category <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                <select name="feat_cat" class="form-select">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary" style="width:100%;"
                onclick="return confirm('This will update the featured status of all matching products. Proceed?')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Apply Featured Toggle
            </button>
        </form>
    </div>

</div>

<?php admin_footer(); ?>
