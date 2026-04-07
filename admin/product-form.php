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

<div class="flex-between mb-6" style="margin-bottom: 24px;">
    <h2 style="margin-bottom: 0;"><?php echo $title; ?></h2>
    <a href="products.php" class="btn btn-ghost">
        <i data-lucide="arrow-left"></i> Back to Products
    </a>
</div>

<form action="product-action.php" method="POST" enctype="multipart/form-data" id="productForm">
    <input type="hidden" name="action" value="<?php echo $id ? 'update' : 'create'; ?>">
    <?php if ($id): ?>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <?php endif; ?>

    <div class="grid grid-3" style="grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left Column: Main Info -->
        <div class="flex-col gap-6" style="display: flex; flex-direction: column; gap: 24px;">
            <div class="card">
                <h3 style="margin-bottom: 20px;">Basic Information</h3>
                <div class="form-group">
                    <label class="form-label">Product Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" placeholder="Enter product name">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="10" placeholder="Product description..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 20px;">Pricing & Inventory</h3>
                <div class="grid grid-2" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Base Price <span style="color: var(--danger);">*</span></label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><?php echo CURRENCY; ?></span>
                            <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $product['price'] ?? ''; ?>" style="padding-left: 45px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Discount Price (Optional)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><?php echo CURRENCY; ?></span>
                            <input type="number" step="0.01" name="discount_price" class="form-control" value="<?php echo $product['discount_price'] ?? ''; ?>" style="padding-left: 45px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity <span style="color: var(--danger);">*</span></label>
                        <input type="number" name="stock" class="form-control" required value="<?php echo $product['stock'] ?? '0'; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SKU (Stock Keeping Unit)</label>
                        <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>" placeholder="e.g. ELE-001">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Options -->
        <div class="flex-col gap-6" style="display: flex; flex-direction: column; gap: 24px;">
            <div class="card">
                <h3 style="margin-bottom: 20px;">Organization</h3>
                <div class="form-group">
                    <label class="form-label">Category <span style="color: var(--danger);">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_featured" value="1" <?php echo ($product['is_featured'] ?? false) ? 'checked' : ''; ?>>
                        <span>Featured Product</span>
                    </label>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 20px;">Product Image</h3>
                <div class="image-upload-zone" style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: border-color 0.2s;">
                    <input type="file" name="image" id="imageInput" style="display: none;" accept="image/*">
                    <div id="imagePreview" style="margin-bottom: 12px; display: <?php echo ($product['image'] ?? '') ? 'block' : 'none'; ?>;">
                        <?php if ($product['image'] ?? ''): ?>
                        <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" alt="Preview" style="max-width: 100%; border-radius: 6px;">
                        <?php endif; ?>
                    </div>
                    <div id="uploadPrompt" style="display: <?php echo ($product['image'] ?? '') ? 'none' : 'block'; ?>;">
                        <i data-lucide="image-plus" style="width: 48px; height: 48px; margin-bottom: 12px; color: var(--gray-400);"></i>
                        <p style="font-size: 14px; color: var(--text-secondary);">Click to upload image</p>
                        <p style="font-size: 12px; color: var(--gray-400); margin-top: 4px;">Recommended: 1000x1000px</p>
                    </div>
                </div>
                <?php if ($product['image'] ?? ''): ?>
                <button type="button" class="btn btn-ghost btn-sm" style="width: 100%; margin-top: 12px; color: var(--danger);" onclick="removeImage()">
                    <i data-lucide="x" style="width: 14px;"></i> Remove Image
                </button>
                <?php endif; ?>
            </div>

            <div class="card" style="position: sticky; top: 88px;">
                <h3 style="margin-bottom: 20px;">Actions</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i data-lucide="save"></i> <?php echo $id ? 'Update Product' : 'Create Product'; ?>
                    </button>
                    <a href="products.php" class="btn btn-ghost" style="width: 100%;">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.querySelector('.image-upload-zone').addEventListener('click', () => {
    document.getElementById('imageInput').click();
});

document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; border-radius: 6px;">`;
            preview.style.display = 'block';
            document.getElementById('uploadPrompt').style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
});

function removeImage() {
    if (confirm('Are you sure you want to remove the current image?')) {
        // This could be handled via a hidden input on form submit
        const preview = document.getElementById('imagePreview');
        preview.style.display = 'none';
        preview.innerHTML = '';
        document.getElementById('uploadPrompt').style.display = 'block';
        document.getElementById('imageInput').value = '';
        
        // Add a hidden input to signal image removal if needed
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_image';
        input.value = '1';
        document.getElementById('productForm').appendChild(input);
    }
}
</script>

<?php admin_footer(); ?>
