<?php
$page_title = "Order Confirmed!";
require_once __DIR__ . '/includes/header.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<div class="container mx-auto px-4 md:px-6 py-20 flex flex-col items-center text-center">
    <div class="w-24 h-24 bg-emerald-100 text-emerald-600 rounded-[2.5rem] flex items-center justify-center mb-8 animate-bounce">
        <i data-lucide="check-circle-2" class="w-12 h-12"></i>
    </div>
    
    <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tight mb-4">THANK YOU!</h1>
    <p class="text-xl text-slate-500 font-medium mb-10 max-w-xl">
        Your order <span class="text-slate-900 font-black">#ARS-<?= $order_id ?></span> has been placed successfully. 
        We've sent a confirmation details to your registered mobile number.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl mb-12">
        <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-100">
            <i data-lucide="package" class="w-8 h-8 text-brand-600 mx-auto mb-4"></i>
            <h4 class="font-bold text-slate-900 mb-1">Preparation</h4>
            <p class="text-xs text-slate-400 font-medium leading-relaxed uppercase tracking-widest">Expected within 24h</p>
        </div>
        <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-100">
            <i data-lucide="truck" class="w-8 h-8 text-brand-600 mx-auto mb-4"></i>
            <h4 class="font-bold text-slate-900 mb-1">Shipping</h4>
            <p class="text-xs text-slate-400 font-medium leading-relaxed uppercase tracking-widest">2-4 Business Days</p>
        </div>
        <div class="bg-white p-8 rounded-3xl soft-shadow border border-slate-100">
            <i data-lucide="phone-call" class="w-8 h-8 text-brand-600 mx-auto mb-4"></i>
            <h4 class="font-bold text-slate-900 mb-1">Support</h4>
            <p class="text-xs text-slate-400 font-medium leading-relaxed uppercase tracking-widest">Available 24/7</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4">
        <a href="orders.php" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black text-lg hover:bg-brand-600 transition-all transform hover:-translate-y-1 shadow-xl shadow-slate-200 flex items-center gap-3">
            Track My Order <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </a>
        <a href="shop.php" class="px-10 py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-lg hover:bg-slate-50 transition-all flex items-center gap-3">
            Continue Shopping
        </a>
    </div>

    <!-- Feedback Hook -->
    <div class="mt-20 pt-10 border-t border-slate-100 w-full max-w-2xl">
        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">How was your experience?</p>
        <div class="flex justify-center gap-4">
            <button class="w-12 h-12 bg-white border border-slate-100 rounded-full flex items-center justify-center text-2xl hover:scale-110 transition-transform">😞</button>
            <button class="w-12 h-12 bg-white border border-slate-100 rounded-full flex items-center justify-center text-2xl hover:scale-110 transition-transform">😐</button>
            <button class="w-12 h-12 bg-white border border-slate-100 rounded-full flex items-center justify-center text-2xl hover:scale-110 transition-transform">😊</button>
            <button class="w-12 h-12 bg-white border border-slate-100 rounded-full flex items-center justify-center text-2xl hover:scale-110 transition-transform">🤩</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
