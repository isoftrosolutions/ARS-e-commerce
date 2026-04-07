<?php
// admin/settings.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Mock settings - in a real app, these would be in a `settings` table
$settings = [
    'site_name' => SITE_NAME,
    'site_email' => 'contact@ars.com',
    'contact_mobile' => '9800000000',
    'currency' => CURRENCY,
    'shipping_fee' => SHIPPING_FEE,
    'free_shipping_threshold' => FREE_SHIPPING_THRESHOLD
];

admin_header('Settings', 'settings');
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 24px;">Store Settings</h2>
    
    <form action="settings-action.php" method="POST">
        <div class="grid grid-2" style="grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Support Email</label>
                <input type="email" name="site_email" class="form-control" value="<?php echo htmlspecialchars($settings['site_email']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Mobile</label>
                <input type="text" name="contact_mobile" class="form-control" value="<?php echo htmlspecialchars($settings['contact_mobile']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Currency Symbol</label>
                <input type="text" name="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Default Shipping Fee (<?php echo CURRENCY; ?>)</label>
                <input type="number" name="shipping_fee" class="form-control" value="<?php echo $settings['shipping_fee']; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Free Shipping Threshold (<?php echo CURRENCY; ?>)</label>
                <input type="number" name="free_shipping_threshold" class="form-control" value="<?php echo $settings['free_shipping_threshold']; ?>">
            </div>
        </div>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px;">
            <button type="reset" class="btn btn-ghost">Reset Changes</button>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php admin_footer(); ?>
