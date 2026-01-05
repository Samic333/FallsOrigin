import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { ShoppingBag, Menu, X, User, Globe } from 'lucide-react';
import { Logo, BrandTitle } from './Logo';

import { useLanguage } from '../contexts/LanguageContext';

interface LayoutProps {
  children: React.ReactNode;
  cartCount: number;
  onOpenCart: () => void;
}

const Layout: React.FC<LayoutProps> = ({ children, cartCount, onOpenCart }) => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const { lang, setLang, t } = useLanguage();
  const location = useLocation();
  const isAdminPage = location.pathname.startsWith('/admin');

  const navLinks = [
    { name: t.home, href: '/' },
    { name: t.shop, href: '/shop' },
    { name: t.track, href: '/track' },
    { name: t.contact, href: '/contact' },
  ];

  const handleSocialClick = (platform: string) => {
    alert(`${platform} Coming Soon`);
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#050505]">
      <header className="fixed top-0 w-full z-50 bg-[#0a0a0a]/90 backdrop-blur-xl border-b border-white/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-24">
            <div className="flex items-center">
              <Link to="/" className="flex items-center space-x-4 group">
                <Logo className="w-10 h-10 text-white group-hover:text-amber-600 transition-colors duration-500" />
                <BrandTitle className="hidden sm:flex text-white" />
              </Link>
            </div>

            {/* Desktop Nav */}
            {!isAdminPage && (
              <nav className="hidden md:flex space-x-10">
                {navLinks.map((link) => (
                  <Link
                    key={link.name}
                    to={link.href}
                    className="text-white/60 hover:text-white text-[11px] font-bold tracking-[0.4em] transition-all uppercase"
                  >
                    {link.name}
                  </Link>
                ))}
              </nav>
            )}

            <div className="flex items-center space-x-2 sm:space-x-6">
              {/* Language Selector */}
              <div className="flex items-center bg-white/5 rounded-full p-1 border border-white/5 mr-2">
                <button
                  onClick={() => setLang('en')}
                  className={`px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full transition-all ${lang === 'en' ? 'bg-amber-600 text-white' : 'text-white/20 hover:text-white/40'}`}
                >
                  EN
                </button>
                <button
                  onClick={() => setLang('fr')}
                  className={`px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full transition-all ${lang === 'fr' ? 'bg-amber-600 text-white' : 'text-white/20 hover:text-white/40'}`}
                >
                  FR
                </button>
              </div>

              {!isAdminPage && (
                <>
                  <button
                    onClick={onOpenCart}
                    className="relative p-2 text-white/60 hover:text-white transition-colors"
                  >
                    <ShoppingBag className="w-5 h-5" />
                    {cartCount > 0 && (
                      <span className="absolute -top-1 -right-1 bg-white text-black text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                        {cartCount}
                      </span>
                    )}
                  </button>
                  <Link to="/admin" className="p-2 text-white/60 hover:text-white transition-colors">
                    <User className="w-5 h-5" />
                  </Link>
                  <button
                    className="md:hidden p-2 text-white/60"
                    onClick={() => setIsMenuOpen(!isMenuOpen)}
                  >
                    {isMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                  </button>
                </>
              )}
              {isAdminPage && (
                <div className="flex items-center space-x-2 text-amber-600/40 text-[9px] font-black uppercase tracking-widest">
                  <Globe className="w-3 h-3" />
                  <span>System Language</span>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Mobile Nav */}
        {!isAdminPage && isMenuOpen && (
          <div className="md:hidden bg-[#0a0a0a] border-b border-white/5 animate-in slide-in-from-top duration-300">
            <div className="px-4 pt-4 pb-8 space-y-4">
              {navLinks.map((link) => (
                <Link
                  key={link.name}
                  to={link.href}
                  onClick={() => setIsMenuOpen(false)}
                  className="block py-4 text-white/80 hover:text-amber-600 text-sm font-bold tracking-[0.2em] border-b border-white/5 uppercase"
                >
                  {link.name}
                </Link>
              ))}
            </div>
          </div>
        )}
      </header>

      <main className={`flex-grow ${!isAdminPage ? 'pt-24' : ''}`}>
        {children}
      </main>

      {!isAdminPage && (
        <footer className="bg-[#050505] border-t border-white/5 py-24">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-16">
              <div className="col-span-1 md:col-span-2">
                <Link to="/" className="flex items-center space-x-4 mb-8">
                  <Logo className="w-8 h-8 text-amber-600" />
                  <BrandTitle className="text-white" />
                </Link>
                <p className="text-white/40 max-w-sm mb-8 leading-relaxed text-sm font-medium">
                  {t.footerDesc}
                </p>
                <div className="flex space-x-6">
                  <button
                    onClick={() => handleSocialClick('Instagram')}
                    className="text-white/30 hover:text-white transition-colors text-[10px] uppercase tracking-widest font-black cursor-pointer"
                  >
                    Instagram
                  </button>
                  <button
                    onClick={() => handleSocialClick('Twitter')}
                    className="text-white/30 hover:text-white transition-colors text-[10px] uppercase tracking-widest font-black cursor-pointer"
                  >
                    Twitter
                  </button>
                </div>
              </div>
              <div>
                <h4 className="text-white font-black uppercase tracking-[0.3em] text-[10px] mb-6">{t.discovery}</h4>
                <ul className="space-y-4">
                  <li><Link to="/shop" className="text-white/40 hover:text-white text-xs uppercase tracking-widest font-bold transition-colors">{t.ourRoast}</Link></li>
                  <li><Link to="/track" className="text-white/40 hover:text-white text-xs uppercase tracking-widest font-bold transition-colors">{t.orderStatus}</Link></li>
                  <li><Link to="/contact" className="text-white/40 hover:text-white text-xs uppercase tracking-widest font-bold transition-colors">{t.contact}</Link></li>
                </ul>
              </div>
              <div>
                <h4 className="text-white font-black uppercase tracking-[0.3em] text-[10px] mb-6">{t.legal}</h4>
                <ul className="space-y-4">
                  <li><Link to="/policies" className="text-white/40 hover:text-white text-xs uppercase tracking-widest font-bold transition-colors">{t.shipping}</Link></li>
                  <li><Link to="/policies" className="text-white/40 hover:text-white text-xs uppercase tracking-widest font-bold transition-colors">{t.refunds}</Link></li>
                  <li><Link to="/policies" className="text-white/40 hover:text-white text-xs uppercase tracking-widest font-bold transition-colors">{t.terms}</Link></li>
                </ul>
              </div>
            </div>
            <div className="border-t border-white/5 mt-20 pt-10 flex flex-col md:flex-row justify-between items-center text-[9px] uppercase tracking-[0.4em] font-black text-white/20">
              <p>{t.footerCopyright}</p>
              <p>{t.footerLocation}</p>
            </div>
          </div>
        </footer>
      )}
    </div>
  );
};

export default Layout;