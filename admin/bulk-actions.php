<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

// 1. Handle Bulk Price Update
if (isset($_POST['bulk_price_update'])) {
    $category_id = (int)$_POST['category_id'];
    $change_type = $_POST['change_type']; // 'increase' or 'decrease'
    $percent = (float)$_POST['percent'];
    
    if ($percent > 0) {
        $operator = ($change_type === 'increase') ? '*' : '*';
        $factor = ($change_type === 'increase') ? (1 + ($percent / 100)) : (1 - ($percent / 100));
        
        try {
            $sql = "UPDATE products SET price = price * $factor, discount_price = CASE WHEN discount_price IS NOT NULL THEN discount_price * $factor ELSE NULL END";
            if ($category_id > 0) {
                $sql .= " WHERE category_id = $category_id";
            }
            $pdo->exec($sql);
            redirect('bulk-actions.php', "Prices updated by $percent% for " . ($category_id > 0 ? "selected category." : "all products."));
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}

// 2. Handle CSV Export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="product_catalog_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'SKU', 'Name', 'Price', 'Discount Price', 'Stock', 'Category']);
    
    $stmt = $pdo->query("SELECT p.id, p.sku, p.name, p.price, p.discount_price, p.stock, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$page_title = "Bulk Operations";
include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Bulk Operations</h1>
    <p class="text-slate-500">Fast-track your management by updating multiple products at once.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Price Adjustment Card -->
    <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Smart Price Adjuster</h3>
        </div>
        
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Target Category</label>
                <select name="category_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="0">All Products</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Action</label>
                    <select name="change_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="increase">Increase Price</option>
                        <option value="decrease">Decrease Price</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Percentage (%)</label>
                    <input type="number" name="percent" step="0.1" required placeholder="e.g. 10"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl flex gap-3 items-start border border-amber-100">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 flex-shrink-0"></i>
                <p class="text-[11px] text-amber-700 leading-relaxed font-medium">This action will modify multiple prices instantly. It is recommended to download a CSV backup before proceeding.</p>
            </div>
            <button type="submit" name="bulk_price_update" class="w-full py-3 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/20">
                Execute Price Change
            </button>
        </form>
    </div>

    <!-- Inventory & Catalog Management -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="file-down" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Export Inventory</h3>
            </div>
            <p class="text-sm text-slate-500 mb-6 leading-relaxed">Download your entire product list as a CSV file for offline editing, auditing, or migration to another platform.</p>
            <a href="bulk-actions.php?export=1" class="flex items-center justify-center gap-2 w-full py-3 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Download Full CSV
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Stock Reset</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Need to reset stock for a fresh inventory cycle?</p>
            <button class="w-full py-2 bg-slate-100 text-slate-400 rounded-lg text-xs font-bold cursor-not-allowed border border-slate-200" disabled title="Coming soon">
                Bulk Reset Stock (Coming Soon)
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
