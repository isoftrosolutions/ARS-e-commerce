<?php
// admin/products.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filters
$where = "WHERE 1=1";
$params = [];

if (!empty($_GET['search'])) {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['category'])) {
    $where .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
}

// Fetch Categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Fetch Products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

admin_header('Products', 'products');
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle">Manage your store's product catalog</p>
    </div>
    <div class="page-actions">
        <a href="product-add.php" class="btn btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Product
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;flex:1;align-items:center;">
        <div style="position:relative;flex:1;min-width:200px;max-width:320px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-muted);pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" placeholder="Search products or SKU…"
                class="form-input" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                style="padding-left:34px;">
        </div>
        <select name="category" class="form-select" style="width:180px;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['category'])): ?>
        <a href="products.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrapper">
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:80px;">Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?php if ($product['image']): ?>
                        <img src="<?php echo SITE_URL . '/uploads/' . $product['image']; ?>" alt=""
                            class="product-thumb">
                        <?php else: ?>
                        <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-sku">SKU: <?php echo htmlspecialchars($product['sku'] ?: 'N/A'); ?></div>
                    </td>
                    <td>
                        <span class="badge badge-info"><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></span>
                    </td>
                    <td>
                        <div class="price-main"><?php echo formatPrice($product['price']); ?></div>
                        <?php if ($product['discount_price']): ?>
                        <div class="price-strike"><?php echo formatPrice($product['discount_price']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['stock'] <= 5): ?>
                        <span style="color:var(--danger);font-weight:600;"><?php echo $product['stock']; ?> <span style="font-size:11px;">(Low)</span></span>
                        <?php else: ?>
                        <span style="font-family:var(--font-mono);"><?php echo $product['stock']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['is_featured']): ?>
                        <span class="badge badge-accent">Featured</span>
                        <?php else: ?>
                        <span class="badge badge-muted">Regular</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <div class="actions-cell">
                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Delete"
                                onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:26px;height:26px;"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div>
                            <div class="empty-title">No products found</div>
                            <div class="empty-text">Try a different search query or category filter.</div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="table-footer">
        <span style="color:var(--text-secondary);">
            Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_products; ?> products)
        </span>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>&category=<?php echo $_GET['category'] ?? ''; ?>"
               class="page-btn <?php echo $page === $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirm Modal -->
<div id="deleteModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h2 class="modal-title">Delete Product</h2>
            <button class="modal-close" onclick="closeModal('deleteModal')" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
            <p style="color:var(--text-secondary);font-size:13px;margin-top:8px;">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
            <form id="deleteForm" method="POST" action="product-action.php" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteProductId">
                <button type="submit" class="btn btn-danger">Delete Product</button>
            </form>
        </div>
    </div>
</div>

<script>
function deleteProduct(id, name) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteProductName').textContent = name;
    openModal('deleteModal');
}
</script>

<?php admin_footer(); ?>
