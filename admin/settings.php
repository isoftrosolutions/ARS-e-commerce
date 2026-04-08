<?php
// admin/settings.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Ensure settings table exists and load values
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        `key` VARCHAR(100) PRIMARY KEY,
        `value` TEXT NOT NULL
    )");
    $rows = $pdo->query("SELECT `key`, `value` FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $rows = [];
}

$s = array_merge([
    'site_name'               => SITE_NAME,
    'site_email'              => 'contact@ars.com',
    'contact_mobile'          => '9800000000',
    'currency'                => CURRENCY,
    'shipping_fee'            => SHIPPING_FEE,
    'free_shipping_threshold' => FREE_SHIPPING_THRESHOLD,
    'facebook_url'            => '',
    'instagram_url'           => '',
    'maintenance_mode'        => '0',
], $rows);

admin_header('Settings', 'settings');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Configure your store</p>
    </div>
</div>

<div class="card card-body" style="max-width:880px;">
<form action="settings-action.php" method="POST">

    <!-- Store Information -->
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
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-input" value="<?php echo htmlspecialchars($s['site_name']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Support Email</label>
                <input type="email" name="site_email" class="form-input" value="<?php echo htmlspecialchars($s['site_email']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Mobile</label>
                <input type="text" name="contact_mobile" class="form-input" value="<?php echo htmlspecialchars($s['contact_mobile']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Currency Symbol</label>
                <input type="text" name="currency" class="form-input" value="<?php echo htmlspecialchars($s['currency']); ?>" maxlength="10">
            </div>
        </div>
    </div>

    <!-- Shipping -->
    <div class="settings-section">
        <div class="settings-section-header">
            <div class="settings-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <div>
                <div class="settings-section-title">Shipping</div>
                <div class="settings-section-desc">Configure shipping fees and free shipping threshold</div>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Default Shipping Fee (<?php echo CURRENCY; ?>)</label>
                <div class="input-wrap has-prefix">
                    <div class="input-prefix"><?php echo htmlspecialchars(CURRENCY); ?></div>
                    <input type="number" name="shipping_fee" class="form-input" value="<?php echo (float)$s['shipping_fee']; ?>" min="0" step="0.01">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Free Shipping Threshold (<?php echo CURRENCY; ?>)</label>
                <div class="input-wrap has-prefix">
                    <div class="input-prefix"><?php echo htmlspecialchars(CURRENCY); ?></div>
                    <input type="number" name="free_shipping_threshold" class="form-input" value="<?php echo (float)$s['free_shipping_threshold']; ?>" min="0" step="0.01">
                </div>
                <div class="form-helper">Orders above this amount get free shipping. Set 0 to disable.</div>
            </div>
        </div>
    </div>

    <!-- Social Links -->
    <div class="settings-section">
        <div class="settings-section-header">
            <div class="settings-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            </div>
            <div>
                <div class="settings-section-title">Social Links</div>
                <div class="settings-section-desc">Links displayed in your store footer</div>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Facebook URL</label>
                <input type="url" name="facebook_url" class="form-input" value="<?php echo htmlspecialchars($s['facebook_url']); ?>" placeholder="https://facebook.com/yourpage">
            </div>
            <div class="form-group">
                <label class="form-label">Instagram URL</label>
                <input type="url" name="instagram_url" class="form-input" value="<?php echo htmlspecialchars($s['instagram_url']); ?>" placeholder="https://instagram.com/yourpage">
            </div>
        </div>
    </div>

    <!-- Maintenance Mode -->
    <div class="settings-section" style="margin-bottom:24px;">
        <div class="settings-section-header">
            <div class="settings-section-icon" style="background:var(--warning-dim);color:var(--warning);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <div class="settings-section-title">Maintenance Mode</div>
                <div class="settings-section-desc">When enabled, the store is hidden from customers</div>
            </div>
        </div>
        <label class="toggle-wrap">
            <span class="toggle">
                <input type="checkbox" name="maintenance_mode" value="1" <?php echo $s['maintenance_mode'] === '1' ? 'checked' : ''; ?>>
                <span class="toggle-slider"></span>
            </span>
            <span style="font-size:13.5px;font-weight:500;">Enable Maintenance Mode</span>
        </label>
        <div class="form-helper" style="margin-top:8px;">Admin users can still access the store while maintenance is active.</div>
    </div>

    <div style="padding-top:20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:12px;">
        <button type="reset" class="btn btn-ghost">Reset Changes</button>
        <button type="submit" class="btn btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Settings
        </button>
    </div>

</form>
</div>

<?php admin_footer(); ?>
