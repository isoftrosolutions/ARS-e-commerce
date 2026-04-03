<?php
http_response_code(500);
// Minimal template — avoid requiring functions.php since the error may be DB/config related
$site_name = 'ARS Shop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error | <?= $site_name ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased">
<div class="min-h-screen flex flex-col items-center justify-center p-6 text-center">
    <div class="w-32 h-32 bg-red-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8">
        <i data-lucide="server-crash" class="w-16 h-16 text-red-500"></i>
    </div>
    <h1 class="text-7xl font-black text-slate-900 tracking-tighter mb-4">500</h1>
    <h2 class="text-2xl font-black text-slate-700 mb-4">Internal Server Error</h2>
    <p class="text-slate-500 mb-10 max-w-sm leading-relaxed">
        Something went wrong on our end. We're working to fix it. Please try again shortly.
    </p>
    <a href="index.php" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black hover:bg-red-600 transition-all shadow-xl">
        Return to Homepage
    </a>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
