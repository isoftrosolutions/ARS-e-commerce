<?php
// admin/settings.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Mock settings - in a real app, these would be in a `settings` table
$settings = [
    'site_name'               => SITE_NAME,
    'site_email'              => 'contact@ars.com',
    'contact_mobile'          => '9800000000',
    'currency'                => CURRENCY,
    'shipping_fee'            => SHIPPING_FEE,
    'free_shipping_threshold' => FREE_SHIPPING_THRESHOLD
];

admin_header('Settings', 'settings');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Configure your store settings</p>
    </div>
</div>

<div class="card card-body" style="max-width:860px;">

    <div class="settings-section">
        <div class="settings-section-header">
            <div class="settings-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
                <div class="settings-section-title">Store Information</div>
                <div class="settings-section-desc">Basic details about your store</div>
            </div>
        </div>

        <form action="settings-action.php" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="site_name" class="form-input" value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Support Email</label>
                    <input type="email" name="site_email" class="form-input" value="<?php echo htmlspecialchars($settings['site_email']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Mobile</label>
                    <input type="text" name="contact_mobile" class="form-input" value="<?php echo htmlspecialchars($settings['contact_mobile']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" name="currency" class="form-input" value="<?php echo htmlspecialchars($settings['currency']); ?>">
                </div>
            </div>
        </div>

        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div>
                    <div class="settings-section-title">Shipping</div>
                    <div class="settings-section-desc">Configure shipping fees and thresholds</div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Default Shipping Fee (<?php echo CURRENCY; ?>)</label>
                    <input type="number" name="shipping_fee" class="form-input" value="<?php echo $settings['shipping_fee']; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Free Shipping Threshold (<?php echo CURRENCY; ?>)</label>
                    <input type="number" name="free_shipping_threshold" class="form-input" value="<?php echo $settings['free_shipping_threshold']; ?>">
                    <div class="form-helper">Orders above this amount get free shipping</div>
                </div>
            </div>
        </div>

        <div style="padding-top:20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:12px;">
            <button type="reset" class="btn btn-ghost">Reset Changes</button>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php admin_footer(); ?>
