<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

// Logic to handle actions (Delete, etc.)
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        redirect('products.php', "Product deleted successfully!");
    } catch (PDOException $e) {
        redirect('products.php', "Error deleting product: " . $e->getMessage(), "danger");
    }
}

try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
    $all_products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}

$page_title = "Manage Products";
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Product Management</h1>
        <p class="text-slate-500">View and manage your store's inventory and stock levels.</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i> Export
        </button>
        <a href="product-add.php" class="flex items-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700 transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Product
        </a>
    </div>
</div>

<!-- Filters & Search -->
<div class="bg-white p-4 rounded-xl soft-shadow border border-slate-100 mb-6 flex flex-wrap items-center gap-4">
    <div class="relative flex-1 min-w-[240px]">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
        </div>
        <input type="text" placeholder="Search products..." class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
    </div>
    <select class="border-slate-200 rounded-lg text-sm bg-slate-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
        <option>All Categories</option>
        <!-- Categories could be looped here -->
    </select>
    <select class="border-slate-200 rounded-lg text-sm bg-slate-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
        <option>Stock Status</option>
        <option>In Stock</option>
        <option>Low Stock</option>
        <option>Out of Stock</option>
    </select>
</div>

<!-- Products Table -->
<div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-20">Image</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Product Info</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($all_products as $p): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="w-12 h-12 bg-slate-50 rounded-lg border border-slate-100 overflow-hidden flex items-center justify-center p-1">
                            <img src="<?= $p['image'] ? '../uploads/' . $p['image'] : 'https://via.placeholder.com/100' ?>" 
                                 alt="<?= htmlspecialchars($p['name']) ?>" 
                                 class="max-w-full max-h-full object-contain rounded">
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="text-xs text-slate-400 mt-1">SKU: <?= htmlspecialchars($p['sku'] ?: 'N/A') ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        <span class="px-2 py-1 bg-slate-100 rounded text-xs font-medium"><?= htmlspecialchars($p['category_name']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-slate-800"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></div>
                        <?php if($p['discount_price']): ?>
                        <div class="text-xs text-slate-400 line-through"><?= formatPrice($p['price']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($p['stock'] <= 0): ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Out of Stock
                            </span>
                        <?php elseif ($p['stock'] < 10): ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low Stock (<?= $p['stock'] ?>)
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> <?= $p['stock'] ?> Units
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="product-edit.php?id=<?= $p['id'] ?>" class="p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-all" title="Edit">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                            <a href="products.php?delete=<?= $p['id'] ?>" 
                               onclick="return confirm('Delete this product permanently?')"
                               class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Placeholder -->
    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
        <div class="text-sm text-slate-500">Showing <?= count($all_products) ?> products</div>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-slate-200 rounded text-sm text-slate-400 cursor-not-allowed">Previous</button>
            <button class="px-3 py-1 bg-brand-600 border border-brand-600 rounded text-sm text-white">1</button>
            <button class="px-3 py-1 border border-slate-200 rounded text-sm text-slate-600 hover:bg-slate-50">Next</button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

