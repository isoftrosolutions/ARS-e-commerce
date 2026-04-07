    </main>

    <!-- Footer -->
    <footer class="site-footer bg-dark text-light pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Left Section: Brand & Tagline -->
                <div class="col-lg-4 col-md-12">
                    <div class="footer-brand mb-4">
                        <a href="index.php" class="d-flex align-items-center text-decoration-none mb-3">
                            <div class="brand-logo me-2" style="width: 45px; height: 45px; background: var(--ars-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="bi bi-bag-check-fill fs-4"></i>
                            </div>
                            <span class="brand-text fw-bold text-white fs-3">Easy Shopping <span class="text-primary">A.R.S</span></span>
                        </a>
                        <p class="text-light small lh-lg pe-lg-4 text-white">
                            Your premier destination for quality products and exceptional service. We bridge the gap between premium global trends and the local heart of Nepal, delivering excellence to your doorstep.
                        </p>
                        <div class="shop-address mt-3">
                            <div class="d-flex align-items-start gap-2 text-light small">
                                <i class="bi bi-geo-alt-fill text-primary mt-1"></i>
                                <span><strong>Shop Address:</strong> [INSERT SHOP ADDRESS HERE]</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Section: Contact Info -->
                <div class="col-lg-5 col-md-8">
                    <h6 class="text-uppercase fw-bold mb-4 text-white letter-spacing-1">Contact Details</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <div class="small text-muted mb-1">Email Support</div>
                                    <a href="mailto:easyshoppinga.r.s1@gmail.com" class="text-decoration-none text-light hover-primary d-flex align-items-center gap-2">
                                        <i class="bi bi-envelope-fill text-primary"></i>
                                        easyshoppinga.r.s1@gmail.com
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <div class="small text-muted mb-1">Support Contact</div>
                                    <a href="tel:+9779812231441" class="text-decoration-none text-light hover-primary d-flex align-items-center gap-2">
                                        <i class="bi bi-headset text-primary"></i>
                                        +977 981-2231441
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <div class="small text-muted mb-1">Head Office</div>
                                    <a href="tel:+9779820210361" class="text-decoration-none text-light hover-primary d-flex align-items-center gap-2">
                                        <i class="bi bi-telephone-fill text-primary"></i>
                                        +977 9820210361
                                    </a>
                                </li>
                                <li class="mb-3">
                                    <div class="small text-muted mb-1">Asst. Head Office</div>
                                    <a href="tel:+9779706800854" class="text-decoration-none text-light hover-primary d-flex align-items-center gap-2">
                                        <i class="bi bi-phone-vibrate text-primary"></i>
                                        +977 9706800854
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right Section: Social Media -->
                <div class="col-lg-3 col-md-4">
                    <h6 class="text-uppercase fw-bold mb-4 text-white letter-spacing-1">Follow Us</h6>
                    <p class="text-light small mb-4">Stay updated with our latest arrivals and exclusive offers on social media.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://www.facebook.com/easyshoppinga.r.s1" target="_blank" rel="noopener noreferrer" class="social-btn" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://www.tiktok.com/@easyshopinga.r.s1?_r=1&_t=ZS-95ESeTLKtsG" target="_blank" rel="noopener noreferrer" class="social-btn" title="TikTok">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        <a href="https://www.instagram.com/easyshoppinga.r.s1?igsh=MWEwa3E1bHl1dGRxdA==" target="_blank" rel="noopener noreferrer" class="social-btn" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/@easyshoppinga.r.s1" target="_blank" rel="noopener noreferrer" class="social-btn" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Strip -->
            <div class="footer-bottom pt-4 border-top border-secondary">
                <div class="row align-items-center g-3">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="small text-light mb-0">
                            &copy; <?= date('Y') ?> <span class="text-white fw-semibold">Easy Shopping A.R.S.</span> All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="payment-methods d-flex align-items-center justify-content-center justify-content-md-end gap-3 opacity-75">
                            <span class="text-light x-small text-uppercase">We Accept</span>
                            <span class="badge bg-light text-dark py-1">eSewa</span>
                            <span class="badge bg-light text-danger py-1">FonePay</span>
                            <span class="badge bg-light text-primary py-1">VISA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .site-footer {
            background-color: #0f172a !important; /* Rich Dark Slate */
            font-family: 'Inter', sans-serif;
        }
        .letter-spacing-1 { letter-spacing: 1px; }
        .x-small { font-size: 10px; }
        
        .hover-primary { transition: color 0.2s ease; }
        .hover-primary:hover { color: var(--ars-primary) !important; }

        .social-btn-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            color: #cbd5e1;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .social-btn-sm:hover {
            background: var(--ars-primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .social-btn {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            color: #cbd5e1;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .social-btn:hover {
            background: var(--ars-primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2);
        }
        
        .footer-bottom { border-color: rgba(255, 255, 255, 0.05) !important; }

        @media (max-width: 767.98px) {
            .site-footer { text-align: center; }
            .brand-logo { margin: 0 auto; }
            .hover-primary { justify-content: center; }
            .social-btn { margin: 0 auto; }
            .payment-methods { justify-content: center !important; margin-top: 10px; }
        }
    </style>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary btn-lg rounded-circle shadow" aria-label="Back to top" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <i class="bi bi-chevron-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Header scroll effect
        (function() {
            const header = document.getElementById('mainHeader');
            const announcement = document.querySelector('.announcement-bar');
            
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                
                if (header) {
                    if (currentScroll > 10) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                }
                
                if (announcement) {
                    if (currentScroll > 50) {
                        announcement.classList.add('hidden');
                    } else {
                        announcement.classList.remove('hidden');
                    }
                }
            }, { passive: true });
        })();
        
        // Back to top button
        (function() {
            const btn = document.getElementById('backToTop');
            if (btn) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        btn.style.display = 'flex';
                    } else {
                        btn.style.display = 'none';
                    }
                }, { passive: true });
                
                btn.addEventListener('click', function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        })();
        
        // Close mobile menu on link click
        document.querySelectorAll('.mobile-nav-link:not([data-bs-toggle])').forEach(function(link) {
            link.addEventListener('click', function() {
                const collapse = document.getElementById('mobileMenu');
                if (collapse && collapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                    if (bsCollapse) bsCollapse.hide();
                }
            });
        });
    </script>
</body>
</html>
