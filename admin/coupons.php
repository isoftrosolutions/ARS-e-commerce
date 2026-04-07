<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

// Ensure coupons table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        type ENUM('fixed', 'percentage') DEFAULT 'fixed',
        value DECIMAL(10, 2) NOT NULL,
        min_cart_amount DECIMAL(10, 2) DEFAULT 0,
        expiry_date DATE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Setup Error: " . $e->getMessage());
}

// Handle Add Coupon
if (isset($_POST['add_coupon'])) {
    require_csrf();
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $value = (float)$_POST['value'];
    $min_amount = (float)$_POST['min_cart_amount'];
    $expiry = $_POST['expiry_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_cart_amount, expiry_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $value, $min_amount, $expiry]);
        redirect('coupons.php', "Coupon code $code created successfully!");
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
    redirect('coupons.php', "Coupon deleted.");
}

// Fetch all coupons
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

$page_title = "Manage Coupons";
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Coupons & Discounts</h1>
        <p class="text-slate-500">Create and manage promotional discount codes for your customers.</p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <span><?= $error ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Add Coupon Form -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 sticky top-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Create New Coupon</h3>
            <form action="" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Coupon Code</label>
                    <input type="text" name="code" required placeholder="e.g. SUMMER20"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all uppercase">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Type</label>
                        <select name="type" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20">
                            <option value="fixed">Fixed (Rs.)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Value</label>
                        <input type="number" name="value" required placeholder="0.00"
                               class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Min. Purchase (Rs.)</label>
                    <input type="number" name="min_cart_amount" value="0"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" required
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <button type="submit" name="add_coupon" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Coupon
                </button>
            </form>
        </div>
    </div>

    <!-- Coupons List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Discount</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Expiry</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($coupons as $c): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-brand-600 bg-brand-50 px-2 py-1 rounded border border-brand-100 inline-block">
                                    <?= htmlspecialchars($c['code']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-800">
                                    <?= $c['type'] === 'percentage' ? $c['value'] . '%' : formatPrice($c['value']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                Min spend: <?= formatPrice($c['min_cart_amount']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $is_expired = strtotime($c['expiry_date']) < strtotime(date('Y-m-d'));
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $is_expired ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600' ?>">
                                    <?= date('M d, Y', strtotime($c['expiry_date'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="coupons.php?delete=<?= $c['id'] ?>" 
                                       onclick="return confirm('Delete this coupon?')"
                                       class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <i data-lucide="ticket" class="w-12 h-12 mx-auto mb-4 text-slate-200"></i>
                                <p class="text-lg font-medium">No coupons active</p>
                                <p class="text-sm">Create your first discount code to boost sales.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
