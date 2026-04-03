    </main>
    
    <!-- Footer Section -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-8 border-t border-slate-800 mt-auto">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand Info -->
                <div class="space-y-6">
                    <a href="index.php" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 bg-brand-600 text-white rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xl font-extrabold tracking-tighter text-white">ARS<span class="text-brand-600">SHOP</span></span>
                    </a>
                    <p class="text-sm leading-relaxed">Your trusted online shopping destination in Nepal. We provide high-quality products with the fastest delivery service across the country.</p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                        <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all"><i data-lucide="instagram" class="w-5 h-5"></i></a>
                        <a href="#" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center hover:bg-brand-600 hover:text-white transition-all"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-bold mb-6">Customer Service</h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Track Your Order</a></li>
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Return & Refunds</a></li>
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Shipping Policy</a></li>
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div>
                    <h4 class="text-white font-bold mb-6">Popular Categories</h4>
                    <ul class="space-y-4 text-sm">
                        <?php 
                        // Reuse nav categories if possible
                        foreach($nav_categories as $f_cat): ?>
                            <li><a href="shop.php?category=<?= $f_cat['id'] ?>" class="hover:text-brand-500 transition-colors"><?= htmlspecialchars($f_cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-white font-bold mb-6">Contact Us</h4>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 text-brand-500 flex-shrink-0"></i>
                            <span>Kathmandu Metropolitan City,<br>Bagmati Province, Nepal</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="w-5 h-5 text-brand-500 flex-shrink-0"></i>
                            <span>+977-98XXXXXXXX</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="w-5 h-5 text-brand-500 flex-shrink-0"></i>
                            <span>support@arsshop.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Payment Partners -->
            <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6">
                <p class="text-xs text-slate-500 text-center md:text-left">&copy; <?= date('Y') ?> Easy Shopping A.R.S. All Rights Reserved. Built for high performance.</p>
                <div class="flex items-center gap-4 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all">
                    <!-- Placeholder icons for payment partners -->
                    <div class="bg-white px-2 py-1 rounded text-[10px] font-black text-blue-900 tracking-tighter">eSewa</div>
                    <div class="bg-white px-2 py-1 rounded text-[10px] font-black text-red-600 tracking-tighter">FonePay</div>
                    <div class="bg-white px-2 py-1 rounded text-[10px] font-black text-blue-600 tracking-tighter">VISA</div>
                    <div class="bg-white px-2 py-1 rounded text-[10px] font-black text-slate-900 tracking-tighter">CASH</div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
