<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

// Ensure settings table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT
    )");

    // Seed default settings if empty
    $check = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($check == 0) {
        $defaults = [
            'store_name' => 'ARS E-Commerce',
            'store_email' => 'support@ars.com',
            'store_phone' => '9800000000',
            'store_address' => 'Kathmandu, Nepal',
            'currency' => 'Rs.',
            'tax_percent' => '0',
            'shipping_fee' => '100',
            'cod_enabled' => '1',
            'esewa_enabled' => '1',
            'bank_qr_enabled' => '1'
        ];
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaults as $key => $val) {
            $stmt->execute([$key, $val]);
        }
    }
} catch (PDOException $e) {
    die("Setup Error: " . $e->getMessage());
}

// Handle Update
if (isset($_POST['update_settings'])) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        $pdo->commit();
        redirect('settings.php', "Settings updated successfully!");
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all settings
$settings_raw = $pdo->query("SELECT * FROM settings")->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$page_title = "Store Settings";
include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Store Settings</h1>
    <p class="text-slate-500">Configure your store's general information, payment methods, and business rules.</p>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <span><?= $error ?></span>
    </div>
<?php endif; ?>

<form action="" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <?= csrf_field() ?>
    <div class="lg:col-span-2 space-y-6">
        <!-- General Information -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-500"></i> General Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Store Name</label>
                    <input type="text" name="settings[store_name]" value="<?= htmlspecialchars($settings['store_name'] ?? '') ?>"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Support Email</label>
                    <input type="email" name="settings[store_email]" value="<?= htmlspecialchars($settings['store_email'] ?? '') ?>"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Support Phone</label>
                    <input type="text" name="settings[store_phone]" value="<?= htmlspecialchars($settings['store_phone'] ?? '') ?>"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Store Address</label>
                    <textarea name="settings[store_address]" rows="2"
                              class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all"><?= htmlspecialchars($settings['store_address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Business Rules -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                <i data-lucide="briefcase" class="w-4 h-4 text-brand-500"></i> Business Rules
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Currency Symbol</label>
                    <input type="text" name="settings[currency]" value="<?= htmlspecialchars($settings['currency'] ?? 'Rs.') ?>"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tax (%)</label>
                    <input type="number" name="settings[tax_percent]" value="<?= htmlspecialchars($settings['tax_percent'] ?? '0') ?>"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Flat Shipping Fee</label>
                    <input type="number" name="settings[shipping_fee]" value="<?= htmlspecialchars($settings['shipping_fee'] ?? '0') ?>"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="space-y-6">
        <!-- Payment Methods -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-brand-500"></i> Payment Methods
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="banknote" class="w-5 h-5 text-slate-400"></i>
                        <span class="text-sm font-semibold text-slate-700">Cash on Delivery</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[cod_enabled]" value="0">
                        <input type="checkbox" name="settings[cod_enabled]" value="1" class="sr-only peer" <?= ($settings['cod_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="wallet" class="w-5 h-5 text-slate-400"></i>
                        <span class="text-sm font-semibold text-slate-700">eSewa Payment</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[esewa_enabled]" value="0">
                        <input type="checkbox" name="settings[esewa_enabled]" value="1" class="sr-only peer" <?= ($settings['esewa_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i data-lucide="qr-code" class="w-5 h-5 text-slate-400"></i>
                        <span class="text-sm font-semibold text-slate-700">Bank QR</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[bank_qr_enabled]" value="0">
                        <input type="checkbox" name="settings[bank_qr_enabled]" value="1" class="sr-only peer" <?= ($settings['bank_qr_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <button type="submit" name="update_settings" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/20">
                <i data-lucide="save" class="w-4 h-4"></i> Save Settings
            </button>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
