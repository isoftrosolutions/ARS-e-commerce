<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = "Secure Checkout";
require_once __DIR__ . '/includes/header.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('shop.php', "Your cart is empty.", "info");
}

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['qty'];
}

$shipping_fee = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0 : SHIPPING_FEE;
$grand_total = $subtotal + $shipping_fee;

$user = null;
if(is_logged_in()) {
    $stmt = $pdo->prepare("SELECT full_name, mobile, email, address FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">
        <a href="cart.php" class="hover:text-brand-600 transition-colors">Cart</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-slate-900">Secure Checkout</span>
    </div>

    <form action="checkout-process.php" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="flex flex-col lg:flex-row gap-12">
            <div class="flex-grow space-y-10">
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] soft-shadow border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 bg-brand-600 text-white rounded-full flex items-center justify-center font-black text-sm">1</div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Shipping Information</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Full Name *</label>
                            <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Mobile Number *</label>
                            <input type="tel" name="mobile" required value="<?= htmlspecialchars($user['mobile'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all" placeholder="98XXXXXXXX">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Email (for order updates)</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all" placeholder="your@email.com">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Delivery Address *</label>
                            <textarea name="address" required rows="3"
                                      class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Order Notes (Optional)</label>
                            <textarea name="notes" rows="2"
                                      class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all" placeholder="Special instructions..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] soft-shadow border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 bg-brand-600 text-white rounded-full flex items-center justify-center font-black text-sm">2</div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Payment Method</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-6 bg-slate-50 rounded-3xl border-2 border-transparent hover:border-brand-500/20 cursor-pointer transition-all has-[:checked]:bg-white has-[:checked]:border-brand-600 has-[:checked]:shadow-xl has-[:checked]:shadow-brand-600/5 group">
                            <input type="radio" name="payment_method" value="COD" checked onclick="togglePaymentProof(false)" class="sr-only">
                            <i data-lucide="banknote" class="w-8 h-8 text-slate-400 group-has-[:checked]:text-brand-600 mb-4 transition-colors"></i>
                            <span class="text-sm font-black text-slate-900 mb-1">COD</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Cash on delivery</span>
                        </label>

                        <label class="relative flex flex-col p-6 bg-slate-50 rounded-3xl border-2 border-transparent hover:border-brand-500/20 cursor-pointer transition-all has-[:checked]:bg-white has-[:checked]:border-brand-600 has-[:checked]:shadow-xl has-[:checked]:shadow-brand-600/5 group">
                            <input type="radio" name="payment_method" value="eSewa" onclick="togglePaymentProof(true)" class="sr-only">
                            <i data-lucide="wallet" class="w-8 h-8 text-slate-400 group-has-[:checked]:text-brand-600 mb-4 transition-colors"></i>
                            <span class="text-sm font-black text-slate-900 mb-1">eSewa</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Digital Payment</span>
                        </label>

                        <label class="relative flex flex-col p-6 bg-slate-50 rounded-3xl border-2 border-transparent hover:border-brand-500/20 cursor-pointer transition-all has-[:checked]:bg-white has-[:checked]:border-brand-600 has-[:checked]:shadow-xl has-[:checked]:shadow-brand-600/5 group">
                            <input type="radio" name="payment_method" value="BankQR" onclick="togglePaymentProof(true)" class="sr-only">
                            <i data-lucide="qr-code" class="w-8 h-8 text-slate-400 group-has-[:checked]:text-brand-600 mb-4 transition-colors"></i>
                            <span class="text-sm font-black text-slate-900 mb-1">Bank QR</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">FonePay / ConnectIPS</span>
                        </label>
                    </div>

                    <div id="payment-proof-section" class="hidden mt-10 pt-10 border-t border-slate-100 animate-in fade-in slide-in-from-top-4 duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                            <div class="bg-white p-6 rounded-3xl border-2 border-dashed border-brand-200 text-center">
                                <img src="https://via.placeholder.com/200x200?text=SCAN+QR+TO+PAY" class="w-40 h-40 mx-auto mb-4" alt="QR Code">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Payable To</p>
                                <p class="text-sm font-black text-slate-900 uppercase">ARS ENTERPRISES PVT LTD</p>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Transaction ID *</label>
                                    <input type="text" name="transaction_id" id="txn_id"
                                           class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500/20 outline-none transition-all" placeholder="e.g. 192X...">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Payment Screenshot (JPG, PNG, WEBP - Max 2MB) *</label>
                                    <input type="file" name="payment_proof" id="p_proof" accept="image/jpeg,image/png,image/webp"
                                           class="w-full text-xs font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-96 flex-shrink-0">
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 soft-shadow border border-slate-100 sticky top-28">
                    <h3 class="text-xl font-black text-slate-900 mb-8 tracking-tight">Order Review</h3>
                    
                    <div class="space-y-4 mb-8 max-h-64 overflow-y-auto">
                        <?php foreach($cart as $id => $item): ?>
                        <div class="flex gap-4">
                            <div class="w-14 h-14 bg-slate-50 rounded-xl border border-slate-100 p-2 flex-shrink-0">
                                <img src="<?= !empty($item['image']) ? UPLOAD_DIR . htmlspecialchars($item['image']) : 'https://via.placeholder.com/100' ?>" class="w-full h-full object-contain" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="flex-grow">
                                <p class="text-xs font-bold text-slate-900 line-clamp-1"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-1"><?= (int)$item['qty'] ?> x <?= formatPrice($item['price']) ?></p>
                            </div>
                            <p class="text-xs font-black text-slate-900"><?= formatPrice($item['price'] * $item['qty']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

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

                    <div class="flex justify-between items-center mb-10">
                        <span class="text-lg font-black text-slate-900">Total</span>
                        <span class="text-2xl font-black text-brand-600"><?= formatPrice($grand_total) ?></span>
                    </div>

                    <button type="submit" class="block w-full py-5 bg-slate-900 text-white text-center rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200 flex items-center justify-center gap-3">
                        <i data-lucide="shield-check" class="w-6 h-6 text-brand-500"></i> Place Order
                    </button>

                    <div class="mt-6 flex items-center justify-center gap-3 text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest">End-to-end Encrypted</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function togglePaymentProof(show) {
        const section = document.getElementById('payment-proof-section');
        const txnIdInput = document.getElementById('txn_id');
        const proofInput = document.getElementById('p_proof');
        
        if (show) {
            section.classList.remove('hidden');
            txnIdInput.required = true;
            proofInput.required = true;
        } else {
            section.classList.add('hidden');
            txnIdInput.required = false;
            proofInput.required = false;
        }
    }
    
    document.querySelector('form').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('p_proof');
        if (fileInput.files[0]) {
            const file = fileInput.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const maxSize = 2 * 1024 * 1024;
            
            if (!validTypes.includes(file.type)) {
                e.preventDefault();
                alert('Invalid file type. Only JPG, PNG, and WEBP are allowed.');
                return;
            }
            
            if (file.size > maxSize) {
                e.preventDefault();
                alert('File size exceeds 2MB limit.');
                return;
            }
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
