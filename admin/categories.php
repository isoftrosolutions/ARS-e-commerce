<?php
// admin/categories.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Fetch Categories with product counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

admin_header('Categories', 'categories');
?>

<div class="flex-between mb-6" style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 0;">Manage Categories</h2>
    <button class="btn btn-primary" onclick="openCategoryModal()">
        <i data-lucide="plus"></i> Add Category
    </button>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td>#<?php echo $cat['id']; ?></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td><code style="background: var(--gray-100); padding: 2px 6px; border-radius: 4px;"><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                    <td>
                        <span class="badge badge-info"><?php echo $cat['product_count']; ?> Products</span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button class="btn btn-ghost btn-sm" onclick="openCategoryModal(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>', '<?php echo addslashes($cat['slug']); ?>')">
                                <i data-lucide="edit-2" style="width: 16px;"></i>
                            </button>
                            <form action="category-action.php" method="POST" onsubmit="return confirm('Are you sure? This will not delete products but they will become uncategorized.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);">
                                    <i data-lucide="trash-2" style="width: 16px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal-overlay">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Category</h2>
            <button class="btn btn-ghost btn-sm" onclick="Modal.close('categoryModal')">&times;</button>
        </div>
        <form action="category-action.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" id="modalAction" value="create">
                <input type="hidden" name="id" id="modalCatId">
                
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="modalCatName" class="form-control" required placeholder="e.g. Electronics">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slug (Optional)</label>
                    <input type="text" name="slug" id="modalCatSlug" class="form-control" placeholder="e.g. electronics">
                    <small style="color: var(--text-secondary); font-size: 11px;">Leave blank to auto-generate</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="Modal.close('categoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCategoryModal(id = null, name = '', slug = '') {
    const title = id ? 'Edit Category' : 'Add Category';
    const action = id ? 'update' : 'create';
    
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalAction').value = action;
    document.getElementById('modalCatId').value = id || '';
    document.getElementById('modalCatName').value = name;
    document.getElementById('modalCatSlug').value = slug;
    
    Modal.open('categoryModal');
}
</script>

<?php admin_footer(); ?>
