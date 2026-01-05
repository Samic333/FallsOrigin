import React, { useState, useEffect } from 'react';
import { HashRouter as Router, Routes, Route, Navigate, Link } from 'react-router-dom';
import { Shield, ArrowLeft } from 'lucide-react';
import Layout from './components/Layout';
import CartDrawer from './components/CartDrawer';
import Home from './pages/Home';
import Shop from './pages/Shop';
import ProductPage from './pages/ProductPage';
import Checkout from './pages/Checkout';
import TrackOrder from './pages/TrackOrder';
import Contact from './pages/Contact';
import AdminDashboard from './pages/AdminDashboard';
import Policies from './pages/Policies';
import { CartItem, Product } from './types';
import { StorageService } from './services/storage';
import { FALLS_ORIGIN_COFFEE, ROAST_DETAILS } from './constants';
import { LanguageProvider } from './contexts/LanguageContext';

const App: React.FC = () => {
  const [cart, setCart] = useState<CartItem[]>([]);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [isAdminLoggedIn, setIsAdminLoggedIn] = useState(false);

  useEffect(() => {
    // Aggressive Data Sync:
    // Ensure the new brand asset (Falls Origin bag) is loaded into local storage
    const products = StorageService.getProducts();
    const defaultProductInStore = products.find(p => p.id === FALLS_ORIGIN_COFFEE.id);

    // If no products exist, or the image URL in storage is different from the master constant, refresh it.
    if (products.length === 0 || (defaultProductInStore && defaultProductInStore.image !== FALLS_ORIGIN_COFFEE.image)) {
      const initial: Product = {
        ...FALLS_ORIGIN_COFFEE,
        roastIntensity: ROAST_DETAILS.intensity,
        roastNotes: ROAST_DETAILS.notes,
        type: 'Single Origin'
      };
      StorageService.saveProduct(initial);
    }

    StorageService.incrementViews();

    const saved = localStorage.getItem('foc_cart');
    if (saved) setCart(JSON.parse(saved));
  }, []);

  useEffect(() => {
    localStorage.setItem('foc_cart', JSON.stringify(cart));
  }, [cart]);

  const addToCart = (product: Product, quantity: number = 1) => {
    setCart(prev => {
      const existing = prev.find(i => i.product.id === product.id);
      if (existing) {
        return prev.map(i => i.product.id === product.id ? { ...i, quantity: i.quantity + quantity } : i);
      }
      return [...prev, { product, quantity }];
    });
    setIsCartOpen(true);
  };

  const updateQuantity = (id: string, delta: number) => {
    setCart(prev => prev.map(item =>
      item.product.id === id ? { ...item, quantity: Math.max(1, item.quantity + delta) } : item
    ));
  };

  const removeFromCart = (id: string) => {
    setCart(prev => prev.filter(i => i.product.id !== id));
  };

  const clearCart = () => setCart([]);

  return (
    <LanguageProvider>
      <Router>
        <Layout
          cartCount={cart.reduce((a, b) => a + b.quantity, 0)}
          onOpenCart={() => setIsCartOpen(true)}
        >
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/shop" element={<Shop onAddToCart={(p) => addToCart(p, 1)} />} />
            <Route path="/product/:id" element={<ProductPage onAddToCart={addToCart} />} />
            <Route path="/checkout" element={<Checkout items={cart} onClearCart={clearCart} />} />
            <Route path="/track" element={<TrackOrder />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/policies" element={<Policies />} />

            <Route
              path="/admin"
              element={
                isAdminLoggedIn ? (
                  <AdminDashboard onLogout={() => setIsAdminLoggedIn(false)} />
                ) : (
                  <div className="h-[90vh] flex items-center justify-center bg-[#050505] px-4">
                    <div className="max-w-md w-full bg-[#0a0a0a] border border-white/5 p-12 rounded-[2.5rem] shadow-2xl">
                      <div className="flex flex-col items-center mb-12">
                        <div className="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center mb-6 border border-white/5">
                          <Shield className="w-6 h-6 text-amber-600" />
                        </div>
                        <h2 className="text-2xl font-serif font-bold text-center uppercase tracking-tight">System Access</h2>
                      </div>
                      <div className="space-y-6">
                        <input
                          type="password"
                          placeholder="Security Cipher"
                          className="w-full bg-white/5 border border-white/10 p-5 rounded-2xl text-white focus:outline-none focus:border-amber-600 transition-all text-center tracking-[0.5em]"
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') setIsAdminLoggedIn(true);
                          }}
                        />
                        <button
                          onClick={() => setIsAdminLoggedIn(true)}
                          className="w-full py-5 bg-white text-black font-black uppercase text-[10px] tracking-[0.4em] hover:bg-amber-600 hover:text-white transition-all rounded-2xl"
                        >
                          Authorize
                        </button>

                        <Link
                          to="/"
                          className="flex items-center justify-center space-x-2 text-[10px] text-white/20 hover:text-amber-600 transition-all uppercase tracking-[0.3em] font-black pt-4 group"
                        >
                          <ArrowLeft className="w-3 h-3 group-hover:-translate-x-1 transition-transform" />
                          <span>Return to Store</span>
                        </Link>

                        <p className="text-[9px] text-white/10 text-center uppercase tracking-[0.3em] font-black pt-6">Roastery Management Portal</p>
                      </div>
                    </div>
                  </div>
                )
              }
            />
          </Routes>

          <CartDrawer
            isOpen={isCartOpen}
            onClose={() => setIsCartOpen(false)}
            items={cart}
            onUpdateQuantity={updateQuantity}
            onRemove={removeFromCart}
            onCheckout={() => {
              setIsCartOpen(false);
              window.location.hash = '/checkout';
            }}
          />
        </Layout>
      </Router>
    </LanguageProvider>
  );
};

export default App;