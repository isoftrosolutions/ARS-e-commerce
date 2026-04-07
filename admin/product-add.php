<?php
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    redirect('../auth/login.php', "Access denied.", "danger");
}

if (isset($_POST['add_product'])) {
    require_csrf();
    $name = trim($_POST['name']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $category_id = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];
    $sku = trim($_POST['sku']);

    // Image Upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = 'product_' . time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, discount_price, category_id, stock, sku, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $price, $discount_price, $category_id, $stock, $sku, $image]);
        redirect('products.php', "Product added successfully!");
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$page_title = "Add New Product";
include 'includes/header.php';
?>

<div class="mb-8">
    <div class="flex items-center gap-2 mb-1">
        <a href="products.php" class="text-slate-400 hover:text-brand-600 transition-colors">Products</a>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
        <span class="text-slate-800 font-bold">New Product</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-800">Add New Product</h1>
</div>

<?php if (isset($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        <span><?= $error ?></span>
    </div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">
        <!-- Basic Information -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Basic Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Product Name</label>
                    <input type="text" name="name" required placeholder="e.g. Wireless Noise Cancelling Headphones"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="6" placeholder="Write a detailed description of the product..."
                              class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all"></textarea>
                </div>
            </div>
        </div>

        <!-- Inventory & Pricing -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Pricing & Inventory</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Regular Price (Rs.)</label>
                    <input type="number" name="price" step="0.01" required placeholder="0.00"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Sale Price (Rs.)</label>
                    <input type="number" name="discount_price" step="0.01" placeholder="Optional"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">SKU</label>
                    <input type="text" name="sku" placeholder="e.g. WH-1000XM4"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Stock Quantity</label>
                    <input type="number" name="stock" required placeholder="0"
                           class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="space-y-6">
        <!-- Organization -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Organization</h3>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Category</label>
                <select name="category_id" required class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition-all">
                    <option value="">Select Category</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Product Image -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Product Image</h3>
            <div class="space-y-4">
                <div id="imagePreviewContainer" class="hidden w-full aspect-square bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center p-2">
                    <img id="imagePreview" src="" class="max-w-full max-h-full object-contain">
                </div>
                <div id="uploadPlaceholder" class="w-full aspect-square bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center p-4 text-slate-400">
                    <i data-lucide="image" class="w-12 h-12 mb-2 stroke-1"></i>
                    <p class="text-xs text-center font-medium">Click to upload product image</p>
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" class="hidden">
                <button type="button" onclick="document.getElementById('imageInput').click()" class="w-full py-2 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">
                    Choose Image
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white p-6 rounded-2xl soft-shadow border border-slate-100">
            <button type="submit" name="add_product" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/20">
                <i data-lucide="save" class="w-4 h-4"></i> Publish Product
            </button>
            <a href="products.php" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors mt-3">
                Discard
            </a>
        </div>
    </div>
</form>

<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.classList.remove('hidden');
                uploadPlaceholder.classList.add('hidden');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
 ?>
