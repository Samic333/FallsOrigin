    </main>
    <footer class="bg-[#0a0a0a] border-t border-white/5 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-24">
                <div class="md:col-span-2">
                    <a href="<?php echo $base_path ?? ''; ?>index.php" class="flex flex-col items-flex-start space-y-2 mb-10 group text-decoration-none">
                        <div class="flex items-center space-x-4">
                            <svg viewBox="0 0 100 100" fill="currentColor" class="w-12 h-12 text-white group-hover:text-amber-600 transition-colors duration-500">
                                <path d="M48 5h4v55c0 10-1.5 20-3 30h-1c-1.5-10-3-20-3-30V5z" />
                                <path d="M36 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                                <path d="M60.5 15h3.5v45c0 8-1.5 16-3 24h-1c-1.5-8-3-16-3-24V15z" opacity="0.8" />
                            </svg>
                            <span class="text-3xl font-black tracking-[0.2em] uppercase text-white">Falls Origin</span>
                        </div>
                        <span style="font-weight: 400; letter-spacing: 0.4em; text-transform: uppercase; font-size: 0.6rem; color: rgba(255,255,255,0.4); padding-left: 64px; margin-top: -8px;">Coffee</span>
                    </a>
                    <p class="text-white/60 text-sm max-w-sm uppercase font-medium tracking-tight leading-relaxed">
                        <?php echo __('footer_desc'); ?>
                    </p>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-[0.5em] text-white mb-8"><?php echo __('collection'); ?></h4>
                    <ul class="space-y-4">
                        <li><a href="<?php echo $base_path ?? ''; ?>shop.php" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('shop_now'); ?></a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>index.php#collection" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('view_collection'); ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-[0.5em] text-white mb-8">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="<?php echo $base_path ?? ''; ?>contact.php" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('contact_us'); ?></a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>track-order.php" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('track_order'); ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-12 border-t border-white/[0.02] flex flex-col md:flex-row justify-between items-center gap-8">
                <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.5em]">
                    &copy; <?php echo date('Y'); ?> FALLS ORIGIN COFFEE. ALL RIGHTS RESERVED.
                </p>
                <div class="flex space-x-12 opacity-60">
                    <span class="text-[9px] font-black text-white uppercase tracking-[0.5em]">Niagara Falls, CA</span>
                    <span class="text-[9px] font-black text-white uppercase tracking-[0.5em]">2,100M ASL</span>
                </div>
            </div>
        </div>
    </footer>
    <!-- Global Toast Container -->
    <div id="toastContainer" class="fixed top-24 right-6 z-[100] flex flex-col gap-3 pointer-events-none"></div>

    <script>
        // Mobile Drawer Toggle
        function toggleMobileMenu() {
            const drawer = document.getElementById('mobileDrawer');
            const backdrop = document.getElementById('mobileMenuBackdrop');
            if (!drawer || !backdrop) return;

            const isOpen = !drawer.classList.contains('translate-x-full');
            console.log('Mobile menu toggle. Current state (open):', isOpen);

            if (!isOpen) {
                // Open
                backdrop.classList.remove('hidden');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    backdrop.classList.add('opacity-100');
                    drawer.classList.remove('translate-x-full');
                    document.body.style.overflow = 'hidden';
                }, 10);
            } else {
                // Close
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                drawer.classList.add('translate-x-full');
                document.body.style.overflow = '';
                setTimeout(() => {
                    backdrop.classList.add('hidden');
                }, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const btns = ['mobileMenuBtn', 'closeMenuBtn', 'mobileMenuBackdrop'];
            btns.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        toggleMobileMenu();
                    });
                }
            });
        });

        // Global Toast Notification System
        function showToast(message, cartLink = false) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'bg-[#1a1a1a] border border-amber-600/30 text-white px-6 py-4 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] flex items-center justify-between gap-6 pointer-events-auto transform translate-y-[-10px] opacity-0 transition-all duration-300';
            
            let html = `<span class="text-[11px] font-bold tracking-wide uppercase text-white/90">${message}</span>`;
            if (cartLink) {
                html += `<a href="cart.php" class="text-amber-500 text-[10px] uppercase font-black tracking-widest hover:text-white transition-colors bg-white/5 px-3 py-1.5 rounded-full border border-amber-500/20">Review</a>`;
            }
            toast.innerHTML = html;
            container.appendChild(toast);
            
            requestAnimationFrame(() => toast.classList.remove('translate-y-[-10px]', 'opacity-0'));

            setTimeout(() => {
                toast.classList.add('translate-y-[-10px]', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // Async Add to Cart
        function addToCart(event, productId) {
            event.preventDefault(); // Stop anchor from navigating
            event.stopPropagation(); // Stop parent clicks
            
            const btn = event.currentTarget;
            const originalText = btn.innerText;
            btn.innerText = 'WAIT...';
            btn.style.opacity = '0.5';

            fetch(`cart.php?action=add&id=${productId}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                btn.innerText = originalText;
                btn.style.opacity = '1';
                if(res.ok) {
                    showToast('Coffee Secured', true);
                    let cartBadge = document.querySelector('a[href*="cart.php"] span');
                    if(cartBadge) {
                        cartBadge.textContent = parseInt(cartBadge.textContent) + 1;
                    } else {
                        const cartIcon = document.querySelector('a[href*="cart.php"]');
                        if (cartIcon) {
                            cartIcon.innerHTML += '<span class="absolute -top-2 -right-2 bg-amber-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full">1</span>';
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
