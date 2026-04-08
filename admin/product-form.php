<?php
// admin/product-form.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/layout-parts.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = null;
$title = 'Add New Product';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        redirect('products.php', 'Product not found.', 'danger');
    }
    $title = 'Edit Product';
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

admin_header($title, 'products');
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?php echo htmlspecialchars($title); ?></h1>
    </div>
    <div class="page-actions">
        <a href="products.php" class="btn btn-ghost">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Products
        </a>
    </div>
</div>

<form action="product-action.php" method="POST" enctype="multipart/form-data" id="productForm">
    <input type="hidden" name="action" value="<?php echo $id ? 'update' : 'create'; ?>">
    <?php if ($id): ?>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
        <!-- Left Column: Main Info -->
        <div style="display:flex;flex-direction:column;gap:24px;">

            <div class="card card-body">
                <h3 style="margin-bottom:20px;font-size:15px;">Basic Information</h3>
                <div class="form-group">
                    <label class="form-label">Product Name <span class="req">*</span></label>
                    <input type="text" name="name" class="form-input" required
                        value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                        placeholder="Enter product name">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="8"
                        placeholder="Product description…"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="card card-body">
                <h3 style="margin-bottom:20px;font-size:15px;">Pricing &amp; Inventory</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Base Price <span class="req">*</span></label>
                        <div class="input-wrap has-prefix">
                            <div class="input-prefix"><?php echo CURRENCY; ?></div>
                            <input type="number" step="0.01" min="0" name="price" class="form-input" required
                                value="<?php echo $product['price'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Discount Price <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                        <div class="input-wrap has-prefix">
                            <div class="input-prefix"><?php echo CURRENCY; ?></div>
                            <input type="number" step="0.01" min="0" name="discount_price" class="form-input"
                                value="<?php echo $product['discount_price'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity <span class="req">*</span></label>
                        <input type="number" min="0" name="stock" class="form-input" required
                            value="<?php echo $product['stock'] ?? '0'; ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">SKU <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
                        <input type="text" name="sku" class="form-input"
                            value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>"
                            placeholder="e.g. ELE-001">
                    </div>
                </div>
            </div>

        </div><!-- /left column -->

        <!-- Right Column: Sidebar -->
        <div style="display:flex;flex-direction:column;gap:24px;">

            <div class="card card-body">
                <h3 style="margin-bottom:20px;font-size:15px;">Organization</h3>
                <div class="form-group">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="toggle-wrap">
                        <span class="toggle">
                            <input type="checkbox" name="is_featured" value="1"
                                <?php echo ($product['is_featured'] ?? false) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </span>
                        <span style="font-size:13.5px;font-weight:500;">Featured Product</span>
                    </label>
                    <div class="form-helper" style="margin-top:6px;">Featured products appear on the homepage</div>
                </div>
            </div>

            <div class="card card-body">
                <h3 style="margin-bottom:16px;font-size:15px;">Product Image</h3>
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('imageInput').click()">
                    <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <?php if ($product['image'] ?? ''): ?>
                    <img src="<?php echo SITE_URL . '/uploads/' . $product['image']; ?>"
                        class="upload-preview show" id="product-image-preview" alt="Current image">
                    <?php else: ?>
                    <img class="upload-preview" id="product-image-preview" alt="" src="">
                    <div class="upload-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <div class="upload-title">Click to upload image</div>
                    <div class="upload-hint">JPG, PNG or WebP · Max 2MB · 1000×1000px recommended</div>
                    <?php endif; ?>
                </div>
                <?php if ($product['image'] ?? ''): ?>
                <button type="button" class="btn btn-ghost btn-sm" style="width:100%;margin-top:12px;color:var(--danger);" onclick="removeImage()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Remove Image
                </button>
                <?php endif; ?>
            </div>

            <div class="card card-body" style="position:sticky;top:88px;">
                <h3 style="margin-bottom:16px;font-size:15px;">Publish</h3>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <?php echo $id ? 'Update Product' : 'Create Product'; ?>
                    </button>
                    <a href="products.php" class="btn btn-ghost" style="width:100%;">Cancel</a>
                </div>
            </div>

        </div><!-- /right column -->
    </div>
</form>

<script>
// Image upload preview
document.getElementById('imageInput')?.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        Toast.error('Only JPG, PNG, or WebP images are allowed.');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        Toast.error('Image must be under 2 MB.');
        return;
    }
    var reader = new FileReader();
    reader.onload = function(ev) {
        var preview = document.getElementById('product-image-preview');
        preview.src = ev.target.result;
        preview.classList.add('show');
        // Hide the prompt elements if they exist
        var zone = document.getElementById('upload-zone');
        var icon = zone.querySelector('.upload-icon');
        var title = zone.querySelector('.upload-title');
        var hint = zone.querySelector('.upload-hint');
        if (icon) icon.style.display = 'none';
        if (title) title.style.display = 'none';
        if (hint) hint.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

function removeImage() {
    if (!confirm('Remove the current image?')) return;
    var preview = document.getElementById('product-image-preview');
    preview.src = '';
    preview.classList.remove('show');
    document.getElementById('imageInput').value = '';
    var zone = document.getElementById('upload-zone');
    var icon = zone.querySelector('.upload-icon');
    var title = zone.querySelector('.upload-title');
    var hint = zone.querySelector('.upload-hint');
    if (icon) icon.style.display = '';
    if (title) title.style.display = '';
    if (hint) hint.style.display = '';
    // Signal image removal on submit
    var existing = document.querySelector('input[name="remove_image"]');
    if (!existing) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_image';
        input.value = '1';
        document.getElementById('productForm').appendChild(input);
    }
}
</script>

<?php admin_footer(); ?>
