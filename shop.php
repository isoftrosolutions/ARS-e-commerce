<?php
$page_title = "Shop All Products";
require_once __DIR__ . '/includes/header.php';

$category_id = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?? 0;
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'newest';
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT) ?? 0;
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT) ?? 0;

if ($min_price < 0) $min_price = 0;
if ($max_price < 0) $max_price = 0;

try {
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0";
    $count_sql = "SELECT COUNT(*) as total FROM products p WHERE p.stock > 0";
    $params = [];
    
    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
        $count_sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($query) {
        $search_term = "%$query%";
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if ($min_price > 0) {
        $sql .= " AND (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) >= ?";
        $count_sql .= " AND (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price > 0) {
        $sql .= " AND (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) <= ?";
        $count_sql .= " AND (CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) <= ?";
        $params[] = $max_price;
    }

    switch($sort) {
        case 'price_low': 
            $sql .= " ORDER BY (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) ASC"; 
            break;
        case 'price_high': 
            $sql .= " ORDER BY (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) DESC"; 
            break;
        case 'name': 
            $sql .= " ORDER BY p.name ASC"; 
            break;
        default: 
            $sql .= " ORDER BY p.created_at DESC"; 
            break;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    $categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
    
    $max_product_price = $pdo->query("SELECT MAX(CASE WHEN discount_price IS NOT NULL THEN discount_price ELSE price END) as max FROM products")->fetch()['max'] ?? 50000;
    
} catch (PDOException $e) {
    error_log("Shop Error: " . $e->getMessage());
    $products = [];
    $categories = [];
    $max_product_price = 50000;
}
?>

<section class="bg-white border-b border-slate-100 py-10">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            <a href="index.php" class="hover:text-brand-600 transition-colors">Home</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-slate-900">Shop Catalog</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
            <?php 
            if($category_id > 0) {
                foreach($categories as $cat) if($cat['id'] == $category_id) echo htmlspecialchars($cat['name']);
            } elseif($query) {
                echo "Search results for: '" . htmlspecialchars($query) . "'";
            } else {
                echo "All Collections";
            }
            ?>
        </h1>
    </div>
</section>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        <aside class="w-full lg:w-72 flex-shrink-0 space-y-10">
            <form id="filter-form" method="GET" action="shop.php" class="space-y-10">
                <input type="hidden" name="category" value="<?= $category_id ?>">
                <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
                
                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 pb-2 border-b-2 border-brand-600 w-fit">Categories</h4>
                    <div class="flex flex-col gap-2">
                        <a href="shop.php<?= $query ? '?q=' . urlencode($query) : '' ?>" class="flex items-center justify-between group py-1.5 transition-all <?= $category_id === 0 ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-brand-600' ?>">
                            <span class="text-sm">All Products</span>
                        </a>
                        <?php foreach($categories as $cat): ?>
                            <a href="shop.php?category=<?= $cat['id'] ?><?= $query ? '&q=' . urlencode($query) : '' ?>" class="flex items-center justify-between group py-1.5 transition-all <?= $category_id == $cat['id'] ? 'text-brand-600 font-bold' : 'text-slate-500 hover:text-brand-600' ?>">
                                <span class="text-sm"><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-400"><?= (int)$cat['product_count'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 pb-2 border-b-2 border-brand-600 w-fit">Price Range</h4>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] text-slate-400 uppercase mb-1 block">Min</label>
                                <input type="number" name="min_price" id="min_price" value="<?= $min_price ?: 0 ?>" min="0" max="<?= $max_product_price ?>"
                                       class="w-full px-3 py-2 bg-slate-50 border-none rounded-lg text-sm font-bold" placeholder="0">
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 uppercase mb-1 block">Max</label>
                                <input type="number" name="max_price" id="max_price" value="<?= $max_price ?: '' ?>" min="0" max="<?= $max_product_price ?>"
                                       class="w-full px-3 py-2 bg-slate-50 border-none rounded-lg text-sm font-bold" placeholder="<?= (int)$max_product_price ?>">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-brand-600 transition-colors">
                            Apply Price Filter
                        </button>
                        <?php if ($min_price > 0 || $max_price > 0): ?>
                        <a href="shop.php?category=<?= $category_id ?><?= $query ? '&q=' . urlencode($query) : '' ?>" class="block text-center text-xs text-red-500 hover:underline">
                            Clear Price Filter
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <div class="bg-slate-900 rounded-3xl p-6 overflow-hidden relative group cursor-pointer">
                <div class="absolute top-0 right-0 w-32 h-32 bg-brand-600/20 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                <h5 class="text-brand-500 font-black text-xl mb-2 italic">EXTRA 15% OFF</h5>
                <p class="text-white text-xs leading-relaxed mb-4">On your first mobile app order. Use code <span class="text-brand-500 font-bold tracking-widest">APP15</span></p>
                <button class="text-[10px] font-black uppercase tracking-widest text-white border-b border-brand-600 pb-1 hover:text-brand-500 transition-colors">Download Now</button>
            </div>
        </aside>

        <div class="flex-grow">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-10 bg-white p-4 rounded-2xl soft-shadow border border-slate-100">
                <p class="text-sm font-bold text-slate-500">Showing <?= count($products) ?> products</p>
                <form action="shop.php" method="GET" class="flex items-center gap-3">
                    <?php if($category_id > 0): ?><input type="hidden" name="category" value="<?= $category_id ?>"><?php endif; ?>
                    <?php if($query): ?><input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>"><?php endif; ?>
                    <?php if($min_price > 0): ?><input type="hidden" name="min_price" value="<?= $min_price ?>"><?php endif; ?>
                    <?php if($max_price > 0): ?><input type="hidden" name="max_price" value="<?= $max_price ?>"><?php endif; ?>
                    <span class="text-xs font-bold text-slate-400 uppercase whitespace-nowrap">Sort by:</span>
                    <select name="sort" onchange="this.form.submit()" class="bg-slate-50 border-none rounded-lg text-xs font-bold text-slate-700 focus:ring-2 focus:ring-brand-500/20 py-2 pl-3 pr-8 outline-none">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Latest Arrivals</option>
                        <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php foreach ($products as $p): ?>
                    <?php 
                    $has_wishlist = false;
                    if (is_logged_in()) {
                        $check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                        $check->execute([$_SESSION['user_id'], $p['id']]);
                        $has_wishlist = (bool)$check->fetch();
                    }
                    ?>
                    <div class="bg-white rounded-3xl soft-shadow border border-slate-100 overflow-hidden group hover:border-brand-500/30 transition-all">
                        <div class="relative aspect-square overflow-hidden bg-slate-50">
                            <?php if($p['discount_price']): ?>
                                <div class="absolute top-4 left-4 z-10 bg-red-600 text-white text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-tighter shadow-lg shadow-red-600/20">
                                    -<?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%
                                </div>
                            <?php endif; ?>
                            <a href="wishlist-action.php?action=<?= $has_wishlist ? 'remove' : 'add' ?>&id=<?= $p['id'] ?>" 
                               class="absolute top-4 right-4 z-10 w-9 h-9 bg-white/80 backdrop-blur-md rounded-full flex items-center justify-center transition-all scale-0 group-hover:scale-100 transform duration-300 <?= $has_wishlist ? 'text-red-500' : 'text-slate-400 hover:text-red-500' ?>">
                                <i data-lucide="heart" class="w-5 h-5 <?= $has_wishlist ? 'fill-current' : '' ?>"></i>
                            </a>
                            <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>">
                                <img src="<?= !empty($p['image']) ? UPLOAD_DIR . htmlspecialchars($p['image']) : 'https://via.placeholder.com/400x400?text=' . urlencode($p['name']) ?>" 
                                     class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110 p-6" alt="<?= htmlspecialchars($p['name']) ?>">
                            </a>
                        </div>
                        <div class="p-6">
                            <span class="text-[10px] font-bold text-brand-600 uppercase tracking-widest"><?= htmlspecialchars($p['category_name'] ?? 'General') ?></span>
                            <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>">
                                <h3 class="text-lg font-bold text-slate-800 mt-1 line-clamp-1 group-hover:text-brand-600 transition-colors"><?= htmlspecialchars($p['name']) ?></h3>
                            </a>
                            <div class="flex items-center justify-between mt-4">
                                <div>
                                    <span class="text-xl font-black text-slate-900"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></span>
                                    <?php if($p['discount_price']): ?>
                                        <span class="text-xs text-slate-400 line-through ml-2"><?= formatPrice($p['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button onclick="addToCart(<?= $p['id'] ?>)" class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center hover:bg-brand-600 transition-all transform group-hover:scale-110">
                                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <div class="col-span-full py-20 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="search-x" class="w-10 h-10"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-2">No products found</h3>
                        <p class="text-slate-500 mb-8 max-w-sm mx-auto">We couldn't find anything matching your filters or search.</p>
                        <a href="shop.php" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl">Reset All Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function addToCart(productId) {
        window.location.href = 'cart-action.php?action=add&id=' + productId;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
