<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

// ── Delete ──────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // Check if any products are tied to this category
    try {
        $count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $count->execute([$del_id]);
        if ($count->fetchColumn() > 0) {
            redirect('categories.php', "Cannot delete: this category has products assigned to it.", "danger");
        }
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
        redirect('categories.php', "Category deleted.");
    } catch (PDOException $e) {
        redirect('categories.php', "Error: " . $e->getMessage(), "danger");
    }
}

// ── Edit inline ─────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $edit_id   = (int)$_POST['id'];
    $edit_name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $edit_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $edit_name)));

    if ($edit_name === '') {
        redirect('categories.php', "Category name cannot be empty.", "danger");
    }
    try {
        $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?")
            ->execute([$edit_name, $edit_slug, $edit_id]);
        redirect('categories.php', "Category updated successfully!");
    } catch (PDOException $e) {
        redirect('categories.php', "Update failed: " . $e->getMessage(), "danger");
    }
}

// ── Add ─────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $new_name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $new_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_name)));

    if ($new_name === '') {
        redirect('categories.php', "Category name cannot be empty.", "danger");
    }
    try {
        $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)")
            ->execute([$new_name, $new_slug]);
        redirect('categories.php', "Category \"$new_name\" added successfully!");
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            redirect('categories.php', "A category with that name/slug already exists.", "danger");
        } else {
            redirect('categories.php', "Database Error: " . $e->getMessage(), "danger");
        }
    }
}

// ── Fetch all categories with product count ──────────────
try {
    $categories = $pdo->query("
        SELECT c.*, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        GROUP BY c.id
        ORDER BY c.name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Which category is being edited (inline)?
$editing_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

$page_title = "Manage Categories";
include 'includes/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Categories</h1>
    <p class="text-slate-500">Organize your products into logical sections.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Add Category Form -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100 sticky top-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Add New Category</h3>
            <form method="POST" action="categories.php" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="new_name" class="block text-sm font-semibold text-slate-700 mb-1">Category Name</label>
                    <input type="text" id="new_name" name="name" required placeholder="e.g. Electronics"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                    <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-wider">Slug: <span id="slugValue" class="text-brand-600">—</span></p>
                </div>
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Category
                </button>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl soft-shadow border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Products</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($categories as $c): ?>
                            <?php if ($editing_id === (int)$c['id']): ?>
                                <tr class="bg-brand-50/30">
                                    <td colspan="2" class="px-6 py-4">
                                        <form method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required
                                                   class="flex-1 px-3 py-1.5 border border-brand-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                            <button type="submit" class="px-3 py-1.5 bg-brand-600 text-white rounded-lg text-xs font-bold hover:bg-brand-700">Save</button>
                                            <a href="categories.php" class="px-3 py-1.5 bg-slate-200 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-300">Cancel</a>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600"><?= $c['product_count'] ?></span>
                                    </td>
                                    <td class="px-6 py-4"></td>
                                </tr>
                            <?php else: ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-slate-800"><?= htmlspecialchars($c['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="text-xs text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded"><?= htmlspecialchars($c['slug']) ?></code>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $c['product_count'] > 0 ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-600' ?>">
                                            <?= $c['product_count'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="categories.php?edit=<?= $c['id'] ?>" class="p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-all" title="Edit">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
                                            <a href="categories.php?delete=<?= $c['id'] ?>" 
                                               onclick="return confirm('Delete this category?')"
                                               class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all <?= $c['product_count'] > 0 ? 'opacity-30 cursor-not-allowed pointer-events-none' : '' ?>" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No categories found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const nameInput = document.getElementById('new_name');
    const slugValue = document.getElementById('slugValue');
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            slugValue.textContent = slug || '—';
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
