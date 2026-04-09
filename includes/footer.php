    </main>
    
    <!-- Footer Section -->
    <footer class="site-footer pt-12 md:pt-16 pb-6 md:pb-8 mt-auto">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-10 lg:gap-12 mb-10 md:mb-14">
                <!-- Brand Info -->
                <div class="space-y-5">
                    <a href="index.php" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg shadow-brand-600/10 transition-transform group-hover:scale-105 border border-slate-700 overflow-hidden">
                            <img src="assets/logo.jpeg" alt="ARS Shop Logo" class="w-full h-full object-contain p-1">
                        </div>
                        <span class="text-lg font-bold tracking-tight text-white">ARS<span class="text-brand-500">SHOP</span></span>
                    </a>
                    <p class="text-sm leading-relaxed text-gray-400">Your trusted online shopping destination in Nepal. We provide high-quality products with the fastest delivery service across the country.</p>
                    <div class="flex items-center gap-3">
                        <a href="#" class="footer-social text-gray-400 hover:text-white" aria-label="Website">
                            <i data-lucide="globe" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="footer-social text-gray-400 hover:text-white" aria-label="Instagram">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="footer-social text-gray-400 hover:text-white" aria-label="Facebook">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-base font-semibold mb-5">Customer Service</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-sm hover:text-brand-500 transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3 opacity-50"></i>Track Your Order</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-500 transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3 opacity-50"></i>Return & Refunds</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-500 transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3 opacity-50"></i>Shipping Policy</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-500 transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3 opacity-50"></i>Terms & Conditions</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-500 transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3 opacity-50"></i>Privacy Policy</a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div>
                    <h4 class="text-base font-semibold mb-5">Popular Categories</h4>
                    <ul class="space-y-3">
                        <?php 
                        foreach($nav_categories as $f_cat): ?>
                            <li><a href="shop.php?category=<?= $f_cat['id'] ?>" class="text-sm hover:text-brand-500 transition-colors flex items-center gap-2"><i data-lucide="chevron-right" class="w-3 h-3 opacity-50"></i><?= htmlspecialchars($f_cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-base font-semibold mb-5">Contact Us</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="map-pin" class="w-4 h-4 text-brand-500"></i>
                            </div>
                            <span class="text-sm text-gray-400">Birgunj-13 Radhemai,<br>Parsa, Nepal</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="phone" class="w-4 h-4 text-brand-500"></i>
                            </div>
                            <span class="text-sm text-gray-400">+977-9820210361</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white/5 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="mail" class="w-4 h-4 text-brand-500"></i>
                            </div>
                            <span class="text-sm text-gray-400">easyshoppinga.r.s1@gmail.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Payment Partners & Copyright -->
            <div class="pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <span class="text-xs text-gray-500">We Accept:</span>
                    <div class="flex items-center gap-2">
                        <span class="payment-badge text-blue-900">eSewa</span>
                        <span class="payment-badge text-red-600">FonePay</span>
                        <span class="payment-badge text-blue-700">VISA</span>
                        <span class="payment-badge text-slate-800">COD</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <!-- PWA Install Button -->
                    <button id="pwa-install-btn"
                        class="hidden items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-brand-600 text-white hover:bg-brand-700 transition-all shadow-lg shadow-brand-600/30"
                        aria-label="Install ARS Shop App">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Install App
                    </button>
                    <p class="text-xs text-gray-500 text-center md:text-right">&copy; <?= date('Y') ?> Easy Shopping A.R.S. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
        
        // Mobile Menu Functions
        function openMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('mobile-menu-overlay');
            if (menu && overlay) {
                menu.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('mobile-menu-overlay');
            if (menu && overlay) {
                menu.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
                document.body.style.overflow = '';
            }
        }
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeMobileMenu();
        });
        
        // Header scroll behavior
        (function() {
            const header = document.getElementById('main-header');
            const announcement = document.getElementById('announcement-bar');
            let lastScroll = 0;
            let ticking = false;
            
            function updateHeader() {
                const currentScroll = window.pageYOffset;
                
                // Add shadow on scroll
                if (currentScroll > 10) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                // Hide/show announcement bar
                if (announcement) {
                    if (currentScroll > 50) {
                        announcement.style.transform = 'translateY(-100%)';
                        announcement.style.opacity = '0';
                    } else {
                        announcement.style.transform = 'translateY(0)';
                        announcement.style.opacity = '1';
                    }
                }
                
                lastScroll = currentScroll;
                ticking = false;
            }
            
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    requestAnimationFrame(updateHeader);
                    ticking = true;
                }
            }, { passive: true });
            
            // Initial check
            updateHeader();
        })();
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // ── PWA: Service Worker Registration ──────────────────────────────
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/ARS/sw.js', { scope: '/ARS/' })
                    .then(reg => {
                        // Check for SW updates periodically
                        reg.addEventListener('updatefound', () => {
                            const newWorker = reg.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    newWorker.postMessage({ type: 'SKIP_WAITING' });
                                }
                            });
                        });
                    })
                    .catch(err => console.warn('[PWA] SW registration failed:', err));
            });
        }

        // ── PWA: Install Prompt ───────────────────────────────────────────
        let _deferredPrompt = null;
        const installBtn = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            _deferredPrompt = e;
            if (installBtn) {
                installBtn.classList.remove('hidden');
                installBtn.classList.add('flex');
            }
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (!_deferredPrompt) return;
                _deferredPrompt.prompt();
                const { outcome } = await _deferredPrompt.userChoice;
                _deferredPrompt = null;
                if (outcome === 'accepted') {
                    installBtn.classList.add('hidden');
                    installBtn.classList.remove('flex');
                }
            });
        }

        // Hide install button once the app is installed
        window.addEventListener('appinstalled', () => {
            if (installBtn) {
                installBtn.classList.add('hidden');
                installBtn.classList.remove('flex');
            }
            _deferredPrompt = null;
        });
    </script>
</body>
</html>
