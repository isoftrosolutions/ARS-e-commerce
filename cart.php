<?php
$page_title = "My Shopping Bag";
require_once __DIR__ . '/includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$shipping_threshold = 1000;
$shipping_fee = ($subtotal >= $shipping_threshold || $subtotal == 0) ? 0 : 150;
$grand_total = $subtotal + $shipping_fee;
?>

<div class="container mx-auto px-4 md:px-6 py-12">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">
        <a href="index.php" class="hover:text-brand-600 transition-colors">Home</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-slate-900">Shopping Bag</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-12">
        <!-- Main Cart Column -->
        <div class="flex-grow">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Shopping Bag</h1>
                <span class="text-sm font-bold text-slate-400"><?= count($cart) ?> items</span>
            </div>

            <?php if (empty($cart)): ?>
                <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="shopping-bag" class="w-10 h-10"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2">Your bag is empty</h3>
                    <p class="text-slate-500 mb-8 max-w-sm mx-auto">Looks like you haven't found anything yet. Explore our latest arrivals and fill it up!</p>
                    <a href="shop.php" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl">Start Shopping</a>
                </div>
            <?php else: ?>
                <!-- Free Shipping Progress -->
                <?php if($subtotal < $shipping_threshold): ?>
                    <div class="bg-blue-50 border border-blue-100 p-6 rounded-3xl mb-8">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-blue-800 uppercase tracking-wide">Almost there!</p>
                            <p class="text-xs font-bold text-blue-800">Add <?= formatPrice($shipping_threshold - $subtotal) ?> more for FREE shipping</p>
                        </div>
                        <div class="w-full h-2 bg-blue-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 transition-all duration-500" style="width: <?= ($subtotal / $shipping_threshold) * 100 ?>%"></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-3xl mb-8 flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </div>
                        <p class="text-sm font-bold text-emerald-800 tracking-tight">Congratulations! You've unlocked <span class="underline underline-offset-4">FREE Delivery</span> for this order.</p>
                    </div>
                <?php endif; ?>

                <!-- Cart Items List -->
                <div class="space-y-6">
                    <?php foreach ($cart as $id => $item): ?>
                    <div class="bg-white p-6 md:p-8 rounded-[2rem] soft-shadow border border-slate-100 flex flex-col md:flex-row items-center gap-8 relative group">
                        <div class="w-32 h-32 bg-slate-50 rounded-2xl border border-slate-100 p-4 flex-shrink-0">
                            <img src="<?= $item['image'] ? UPLOAD_DIR . $item['image'] : 'https://via.placeholder.com/150' ?>" 
                                 class="w-full h-full object-contain" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        
                        <div class="flex-grow text-center md:text-left">
                            <h3 class="text-lg font-bold text-slate-900 mb-1"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Unit Price: <?= formatPrice($item['price']) ?></p>
                            
                            <div class="flex items-center justify-center md:justify-start gap-4">
                                <div class="flex items-center bg-slate-100 rounded-xl p-1">
                                    <a href="cart-action.php?action=update&id=<?= $id ?>&qty=<?= $item['qty'] - 1 ?>" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-brand-600 transition-colors">
                                        <i data-lucide="minus" class="w-4 h-4"></i>
                                    </a>
                                    <span class="w-10 text-center text-sm font-black text-slate-900"><?= $item['qty'] ?></span>
                                    <a href="cart-action.php?action=update&id=<?= $id ?>&qty=<?= $item['qty'] + 1 ?>" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-brand-600 transition-colors">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </a>
                                </div>
                                <a href="cart-action.php?action=remove&id=<?= $id ?>" class="text-[10px] font-black uppercase tracking-widest text-red-400 hover:text-red-600 transition-colors">Remove</a>
                            </div>
                        </div>

                        <div class="text-center md:text-right">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total</p>
                            <p class="text-xl font-black text-slate-900"><?= formatPrice($item['price'] * $item['qty']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Summary Sidebar -->
        <?php if (!empty($cart)): ?>
        <div class="w-full lg:w-96 flex-shrink-0">
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 soft-shadow border border-slate-100 sticky top-28">
                <h3 class="text-xl font-black text-slate-900 mb-8 tracking-tight">Order Summary</h3>
                
                <div class="space-y-4 mb-8 pb-8 border-b border-slate-100">
                    <div class="flex justify-between items-center text-sm font-bold">
                        <span class="text-slate-400">Subtotal</span>
                        <span class="text-slate-900"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm font-bold">
                        <span class="text-slate-400">Shipping</span>
                        <span class="<?= $shipping_fee == 0 ? 'text-emerald-600' : 'text-slate-900' ?>">
                            <?= $shipping_fee == 0 ? 'FREE' : formatPrice($shipping_fee) ?>
                        </span>
                    </div>
                </div>

                <!-- Coupon Field -->
                <div class="mb-8">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Promo Code</label>
                    <div class="flex gap-2">
                        <input type="text" placeholder="Enter code" class="flex-grow px-4 py-3 bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-brand-500/20 outline-none">
                        <button class="px-4 py-3 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-brand-600 transition-all uppercase tracking-widest">Apply</button>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-10">
                    <span class="text-lg font-black text-slate-900">Total</span>
                    <span class="text-2xl font-black text-brand-600"><?= formatPrice($grand_total) ?></span>
                </div>

                <a href="checkout.php" class="block w-full py-5 bg-slate-900 text-white text-center rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200 mb-6">
                    Checkout Now
                </a>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3 grayscale opacity-50 justify-center">
                        <div class="bg-white px-2 py-1 rounded text-[8px] font-black text-blue-900 border">eSewa</div>
                        <div class="bg-white px-2 py-1 rounded text-[8px] font-black text-red-600 border">FonePay</div>
                        <div class="bg-white px-2 py-1 rounded text-[8px] font-black text-blue-600 border">VISA</div>
                    </div>
                    <p class="text-[10px] text-center text-slate-400 font-medium leading-relaxed italic">By clicking Checkout, you agree to our 7-Day Easy Return Policy.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
