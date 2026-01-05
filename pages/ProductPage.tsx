import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ShoppingBag, Minus, Plus, Globe, Shield, Leaf, Droplets, Star } from 'lucide-react';
import { api } from '../services/api';
import { Product, Review } from '../types';
import { Logo } from '../components/Logo';

const ProductPage: React.FC<{ onAddToCart: (p: Product, q: number) => void }> = ({ onAddToCart }) => {
  const { id } = useParams<{ id: string }>();
  const [product, setProduct] = useState<Product | null>(null);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      if (!id) return;
      setLoading(true);
      try {
        const products = await api.getProducts();
        const found = products.find(p => p.id === id);
        setProduct(found || null);

        if (found) {
          const reviewsData = await api.getApprovedReviews();
          setReviews(reviewsData.filter(r => r.productId === id));
        }
      } catch (err) {
        console.error('Failed to load product data', err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [id]);

  if (loading) {
    return <div className="h-screen bg-[#050505] flex items-center justify-center text-white/20 text-xs uppercase tracking-widest">Loading Roast Profile...</div>;
  }

  if (!product) {
    return (
      <div className="h-screen flex flex-col items-center justify-center bg-[#050505] text-white">
        <Logo className="w-12 h-12 text-white/10 mb-8" />
        <p className="text-[10px] font-black uppercase tracking-[0.4em] text-white/20">Roast variant not found.</p>
        <Link to="/shop" className="mt-12 px-8 py-4 border border-white/10 text-[10px] font-black uppercase tracking-widest hover:border-white transition-all">Back to Roastery</Link>
      </div>
    );
  }

  return (
    <div className="bg-[#050505] pb-32">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <nav className="flex items-center space-x-4 text-[9px] font-black text-white/20 uppercase tracking-[0.4em] mb-16">
          <Link to="/" className="hover:text-white transition-colors">Home</Link>
          <div className="w-1.5 h-1.5 rounded-full bg-white/5"></div>
          <Link to="/shop" className="hover:text-white transition-colors">Collection</Link>
          <div className="w-1.5 h-1.5 rounded-full bg-white/5"></div>
          <span className="text-white/40">{product.origin} Heirloom</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">
          <div className="space-y-12">
            <div className="relative group bg-stone-900 rounded-[2.5rem] overflow-hidden border border-white/5 shadow-2xl aspect-[4/5] flex flex-col items-center justify-center">
              {/* Luxury Studio lighting effects */}
              <div className="absolute inset-0 bg-gradient-to-b from-white/[0.03] via-transparent to-black/40 z-10 pointer-events-none"></div>

              <img
                src={product.image}
                className="relative z-0 h-full w-full object-contain p-8 transition-transform duration-[3s] group-hover:scale-110"
                alt="Falls Origin Coffee Premium Packaging"
              />

              <div className="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-700 bg-black/40 backdrop-blur-sm z-20 pointer-events-none p-12 text-center">
                <Logo className="w-12 h-12 text-white mb-6" />
                <h2 className="text-2xl font-bold tracking-[0.2em] uppercase text-white mb-2">Authenticated</h2>
                <p className="text-[10px] font-black tracking-[0.4em] uppercase text-white/40">Limited Batch Edition 01</p>
              </div>
            </div>

            {/* Tech Specs */}
            <div className="grid grid-cols-2 gap-6">
              <div className="bg-[#0a0a0a] p-8 rounded-3xl border border-white/5">
                <Shield className="w-5 h-5 text-amber-600 mb-4" />
                <h4 className="text-[9px] font-black uppercase tracking-[0.4em] text-white/60 mb-2">Preservation</h4>
                <p className="text-[10px] text-white/30 leading-relaxed uppercase font-bold tracking-tight">Industrial matte barrier shielding heirloom oils from UV light.</p>
              </div>
              <div className="bg-[#0a0a0a] p-8 rounded-3xl border border-white/5">
                <Droplets className="w-5 h-5 text-amber-600 mb-4" />
                <h4 className="text-[9px] font-black uppercase tracking-[0.4em] text-white/60 mb-2">Oxygen Shield</h4>
                <p className="text-[10px] text-white/30 leading-relaxed uppercase font-bold tracking-tight">Built-in degassing valve for maximum shelf-life and aroma.</p>
              </div>
            </div>

            {/* Reviews Section */}
            {reviews.length > 0 && (
              <div className="bg-[#0a0a0a] p-10 rounded-[2.5rem] border border-white/5">
                <h3 className="text-sm font-bold uppercase tracking-widest text-white/50 mb-8 flex items-center">
                  <Star className="w-4 h-4 mr-3 text-amber-600" /> Community Tasting Notes
                </h3>
                <div className="space-y-8">
                  {reviews.map(review => (
                    <div key={review.id} className="border-b border-white/5 last:border-0 pb-6 last:pb-0">
                      <div className="flex items-center space-x-2 mb-2">
                        {[1, 2, 3, 4, 5].map(s => (
                          <Star key={s} className={`w-2.5 h-2.5 ${s <= review.rating ? 'text-amber-600 fill-current' : 'text-stone-800'}`} />
                        ))}
                        <span className="text-[10px] font-bold text-white/40 ml-2 uppercase tracking-wide">Verified Buyer</span>
                      </div>
                      <p className="text-white/60 text-sm italic mb-2">"{review.comment}"</p>
                      <p className="text-[9px] text-white/20 uppercase tracking-widest">— {review.userName}</p>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          <div className="flex flex-col h-full pt-4 lg:sticky lg:top-32">
            <div className="mb-12">
              <div className="flex items-center space-x-3 mb-8">
                <div className="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></div>
                <span className="text-amber-600 font-black uppercase tracking-[0.4em] text-[10px]">
                  Batch Status: Fresh Roast
                </span>
              </div>

              <h1 className="text-5xl lg:text-7xl font-serif font-bold text-white mb-8 leading-[1.05] tracking-tighter uppercase">
                {product.name}
              </h1>

              <div className="flex items-baseline space-x-6 mb-12">
                <span className="text-5xl font-serif font-medium text-white">${product.price.toFixed(2)}</span>
                <span className="text-white/20 font-bold uppercase tracking-[0.4em] text-[10px]">{product.weight}</span>
              </div>

              <div className="border-l border-white/10 pl-8 mb-12">
                <p className="text-white/40 text-lg leading-relaxed font-medium italic">
                  "{product.description}"
                </p>
              </div>

              <div className="grid grid-cols-2 gap-10">
                <div className="flex items-start space-x-4">
                  <Globe className="w-4 h-4 text-amber-600 mt-1" />
                  <div>
                    <h4 className="text-[9px] font-black uppercase tracking-[0.4em] mb-1 text-white/20">Provenance</h4>
                    <p className="text-white font-bold text-xs uppercase tracking-tight">{product.origin}</p>
                  </div>
                </div>
                <div className="flex items-start space-x-4">
                  <Leaf className="w-4 h-4 text-amber-600 mt-1" />
                  <div>
                    <h4 className="text-[9px] font-black uppercase tracking-[0.4em] mb-1 text-white/20">Elevation</h4>
                    <p className="text-white font-bold text-xs uppercase tracking-tight">2,100M ASL</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="mt-auto space-y-10">
              <div className="flex items-center space-x-6">
                <div className="flex items-center border border-white/5 rounded-2xl p-1 bg-white/[0.02]">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="p-4 text-white/20 hover:text-white transition-colors"
                  >
                    <Minus className="w-5 h-5" />
                  </button>
                  <span className="w-14 text-center font-bold text-2xl text-white">{quantity}</span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="p-4 text-white/20 hover:text-white transition-colors"
                  >
                    <Plus className="w-5 h-5" />
                  </button>
                </div>
                <button
                  onClick={() => onAddToCart(product, quantity)}
                  className="flex-grow py-6 bg-white text-black font-black uppercase text-[11px] tracking-[0.6em] hover:bg-amber-600 hover:text-white transition-all duration-700 rounded-2xl flex items-center justify-center group shadow-2xl"
                >
                  <ShoppingBag className="w-5 h-5 mr-4 group-hover:scale-110 transition-transform" />
                  Acquire Roast
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductPage;