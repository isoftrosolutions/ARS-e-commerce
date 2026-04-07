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

<div class="flex-between mb-6" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 12px; flex: 1; max-width: 600px;">
        <form method="GET" style="display: flex; gap: 12px; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" name="search" placeholder="Search products..." class="form-control" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="padding-left: 36px;">
                <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; color: var(--gray-400);"></i>
            </div>
            <select name="category" class="form-control" style="width: 180px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
    <a href="product-add.php" class="btn btn-primary">
        <i data-lucide="plus"></i> Add Product
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?php if ($product['image']): ?>
                        <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" alt="" style="width: 48px; height: 48px; border-radius: 6px; object-fit: cover;">
                        <?php else: ?>
                        <div style="width: 48px; height: 48px; border-radius: 6px; background: var(--gray-100); display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="image" style="width: 20px; color: var(--gray-400);"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div style="font-size: 12px; color: var(--text-secondary);">SKU: <?php echo htmlspecialchars($product['sku'] ?: 'N/A'); ?></div>
                    </td>
                    <td>
                        <span class="badge badge-info"><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></span>
                    </td>
                    <td>
                        <div style="font-weight: 600;"><?php echo formatPrice($product['price']); ?></div>
                        <?php if ($product['discount_price']): ?>
                        <div style="font-size: 12px; color: var(--text-secondary); text-decoration: line-through;"><?php echo formatPrice($product['discount_price']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['stock'] <= 5): ?>
                        <span style="color: var(--danger); font-weight: 600;"><?php echo $product['stock']; ?> (Low)</span>
                        <?php else: ?>
                        <span><?php echo $product['stock']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($product['is_featured']): ?>
                        <span class="badge badge-success">Featured</span>
                        <?php else: ?>
                        <span class="badge badge-ghost" style="background: var(--gray-100); color: var(--text-secondary);">Regular</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-ghost btn-sm" title="Edit">
                                <i data-lucide="edit-2" style="width: 16px;"></i>
                            </a>
                            <button class="btn btn-ghost btn-sm" style="color: var(--danger);" title="Delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">
                                <i data-lucide="trash-2" style="width: 16px;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px; color: var(--text-secondary);">
                        <i data-lucide="package-search" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.2;"></i>
                        <p>No products found matching your criteria.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div style="margin-top: 24px; display: flex; justify-content: center; gap: 8px;">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($_GET['search'] ?? ''); ?>&category=<?php echo $_GET['category'] ?? ''; ?>" 
       class="btn <?php echo $page === $i ? 'btn-primary' : 'btn-ghost'; ?> btn-sm">
        <?php echo $i; ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Delete Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Delete Product</h2>
            <button class="btn btn-ghost btn-sm" onclick="Modal.close('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="Modal.close('deleteModal')">Cancel</button>
            <form id="deleteForm" method="POST" action="product-action.php">
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
    Modal.open('deleteModal');
}
</script>

<?php admin_footer(); ?>
