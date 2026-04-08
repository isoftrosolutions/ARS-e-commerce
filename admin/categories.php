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

<div class="page-header">
    <div>
        <h1 class="page-title">Categories</h1>
        <p class="page-subtitle">Organize your product catalog</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openCategoryModal()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Category
        </button>
    </div>
</div>

<div class="table-wrapper">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="font-family:var(--font-mono);color:var(--text-muted);">#<?php echo $cat['id']; ?></td>
                    <td style="font-weight:600;"><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td><code style="background:var(--bg-overlay);padding:2px 7px;border-radius:var(--r-sm);font-family:var(--font-mono);font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                    <td><span class="badge badge-info"><?php echo $cat['product_count']; ?> Products</span></td>
                    <td class="col-actions">
                        <div class="actions-cell">
                            <button class="btn btn-ghost btn-sm btn-icon" title="Edit"
                                onclick="openCategoryModal(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>', '<?php echo addslashes($cat['slug']); ?>')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form action="category-action.php" method="POST" style="display:inline;"
                                onsubmit="return confirm('Delete this category? Products will become uncategorized.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px;"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg></div>
                            <div class="empty-title">No categories yet</div>
                            <div class="empty-text">Add your first category to organize products.</div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Category</h2>
            <button class="modal-close" onclick="closeModal('categoryModal')" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="category-action.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" id="modalAction" value="create">
                <input type="hidden" name="id" id="modalCatId">

                <div class="form-group">
                    <label class="form-label">Category Name <span class="req">*</span></label>
                    <input type="text" name="name" id="modalCatName" class="form-input" required placeholder="e.g. Electronics">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Slug <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                    <input type="text" name="slug" id="modalCatSlug" class="form-input" placeholder="e.g. electronics">
                    <div class="form-helper">Leave blank to auto-generate from name</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('categoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCategoryModal(id = null, name = '', slug = '') {
    document.getElementById('modalTitle').textContent  = id ? 'Edit Category' : 'Add Category';
    document.getElementById('modalAction').value       = id ? 'update' : 'create';
    document.getElementById('modalCatId').value        = id || '';
    document.getElementById('modalCatName').value      = name;
    document.getElementById('modalCatSlug').value      = slug;
    openModal('categoryModal');
}
</script>

<?php admin_footer(); ?>
