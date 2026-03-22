    </main>
    <footer class="bg-[#0a0a0a] border-t border-white/5 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-24">
                <div class="md:col-span-2">
                    <a href="index.php" class="flex flex-col items-flex-start space-y-2 mb-10 group text-decoration-none">
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
                        <li><a href="shop.php" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('shop_now'); ?></a></li>
                        <li><a href="index.php#collection" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('view_collection'); ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-[0.5em] text-white mb-8">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="contact.php" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('contact_us'); ?></a></li>
                        <li><a href="track-order.php" class="text-white/60 hover:text-amber-600 text-[11px] font-black uppercase tracking-widest transition-colors"><?php echo __('track_order'); ?></a></li>
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
</body>
</html>
