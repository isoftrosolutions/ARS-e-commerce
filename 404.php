<?php
http_response_code(404);
$page_title = "Page Not Found";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-24 text-center">
    <div class="max-w-lg mx-auto">
        <div class="w-32 h-32 bg-slate-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8">
            <i data-lucide="file-question" class="w-16 h-16 text-slate-400"></i>
        </div>
        <h1 class="text-7xl font-black text-slate-900 tracking-tighter mb-4">404</h1>
        <h2 class="text-2xl font-black text-slate-700 mb-4">Page Not Found</h2>
        <p class="text-slate-500 mb-10 leading-relaxed">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="index.php" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-brand-600 transition-all shadow-xl">
                Go to Homepage
            </a>
            <a href="shop.php" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black hover:bg-slate-50 transition-all">
                Browse Shop
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
