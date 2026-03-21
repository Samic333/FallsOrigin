import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ShoppingBag, Star, Coffee, Filter, ChevronDown } from 'lucide-react';
import { api } from '../services/api';
import { Product } from '../types';

const Shop: React.FC<{ onAddToCart: (p: any) => void }> = ({ onAddToCart }) => {
  const [products, setProducts] = useState<Product[]>([]);
  const [filter, setFilter] = useState('All');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const data = await api.getProducts();
        setProducts(data);
      } catch (error) {
        console.error('Failed to fetch products:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchProducts();
  }, []);

  const categories = ['All', 'Single Origin', 'House Blend', 'Reserve'];
  const filteredProducts = filter === 'All'
    ? products
    : products.filter(p => p.type === filter || (filter === 'Single Origin' && p.origin));

  return (
    <div className="py-24 bg-[#050505] min-h-screen">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {/* Modern Asymmetric Header */}
        <div className="relative mb-32 pt-12">
          <div className="absolute -left-12 top-0 text-[180px] font-serif font-black text-white/[0.02] select-none pointer-events-none uppercase tracking-tighter leading-none hidden lg:block">
            Origins
          </div>
          <div className="relative z-10 flex flex-col lg:flex-row lg:items-end justify-between gap-12">
            <div className="max-w-2xl">
              <div className="flex items-center space-x-4 mb-8">
                <div className="h-[2px] w-16 bg-amber-600"></div>
                <span className="text-amber-600 font-black uppercase tracking-[0.5em] text-[10px]">Small Batch Roastery</span>
              </div>
              <h1 className="text-7xl md:text-9xl font-serif font-bold text-white tracking-tighter uppercase leading-[0.85]">
                Curated <br />
                <span className="text-amber-600 italic">Roasts</span>
              </h1>
              <p className="mt-8 text-white/30 text-lg max-w-md font-medium leading-relaxed uppercase tracking-tight">
                Direct trade heirloom beans, roasted in the heart of Toronto for the discerning palate.
              </p>
            </div>
            <div className="flex flex-col items-end">
              <div className="text-white/10 text-[64px] font-serif italic font-bold mb-4">
                {filteredProducts.length.toString().padStart(2, '0')}
              </div>
              <div className="text-white/20 text-[10px] font-black uppercase tracking-[0.5em] border-t border-white/5 pt-4">
                Available Editions
              </div>
            </div>
          </div>
        </div>

        {/* Filter Bar */}
        <div className="flex flex-col md:flex-row justify-between items-center mb-20 border-b border-white/5 pb-8 gap-8">
          <div className="flex space-x-8 overflow-x-auto no-scrollbar pb-4 md:pb-0 w-full md:w-auto">
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setFilter(cat)}
                className={`text-[10px] font-black uppercase tracking-[0.4em] transition-all whitespace-nowrap relative pb-2 ${filter === cat ? 'text-white' : 'text-white/20 hover:text-white/40'
                  }`}
              >
                {cat}
                {filter === cat && (
                  <div className="absolute bottom-0 left-0 w-full h-[2px] bg-amber-600 animate-in fade-in zoom-in duration-500"></div>
                )}
              </button>
            ))}
          </div>
          <div className="flex items-center space-x-6 text-white/40">
            <div className="flex items-center space-x-2 cursor-pointer hover:text-white transition-colors group">
              <Filter className="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" />
              <span className="text-[10px] font-black uppercase tracking-widest">Refine</span>
            </div>
            <div className="flex items-center space-x-2 cursor-pointer hover:text-white transition-colors">
              <span className="text-[10px] font-black uppercase tracking-widest">Sort By</span>
              <ChevronDown className="w-4 h-4" />
            </div>
          </div>
        </div>

        {/* Enhanced Product Grid */}
        {filteredProducts.length === 0 ? (
          <div className="py-48 text-center border-2 border-dashed border-white/5 rounded-[4rem]">
            <Coffee className="w-20 h-20 text-white/5 mx-auto mb-8 animate-pulse" />
            <p className="text-white/20 uppercase tracking-[0.4em] font-black text-xs">No active batches in this category.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-24">
            {filteredProducts.map((product) => (
              <div key={product.id} className="group flex flex-col">
                <Link
                  to={`/product/${product.id}`}
                  className="relative aspect-[4/5] bg-stone-900 rounded-[3.5rem] overflow-hidden flex items-center justify-center p-12 transition-all duration-700 hover:shadow-[0_0_80px_rgba(217,119,6,0.1)] border border-white/5"
                >
                  {/* Background Aura */}
                  <div className="absolute inset-0 bg-gradient-to-br from-amber-600/5 via-transparent to-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>

                  <img
                    src={product.image}
                    alt={product.name}
                    className="h-full w-full object-contain transition-transform duration-[1.5s] cubic-bezier(0.4, 0, 0.2, 1) group-hover:scale-110 group-hover:-rotate-2 z-10"
                  />

                  {/* Badge */}
                  <div className="absolute top-10 right-10 z-20">
                    <div className="bg-black/60 backdrop-blur-2xl px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.4em] rounded-full text-amber-600 border border-amber-600/20 shadow-xl">
                      {product.type || 'Seasonal'}
                    </div>
                  </div>

                  {/* Quick Look Overlay */}
                  <div className="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-all duration-500 z-20">
                    <div className="p-8 border border-white/10 rounded-full scale-90 group-hover:scale-100 transition-transform duration-500">
                      <span className="text-[10px] font-black uppercase tracking-[0.5em] text-white">View Roast Detail</span>
                    </div>
                  </div>
                </Link>

                <div className="mt-10 px-4">
                  <div className="flex justify-between items-start mb-6">
                    <div className="flex-grow pr-4">
                      <div className="flex items-center space-x-3 mb-3">
                        <span className="text-[9px] font-black uppercase tracking-widest text-white/20">{product.origin}</span>
                        <div className="w-1 h-1 rounded-full bg-amber-600/30"></div>
                        <div className="flex items-center text-amber-600/60">
                          {[1, 2, 3, 4, 5].map(i => <Star key={i} className="w-2.5 h-2.5 fill-current" />)}
                        </div>
                      </div>
                      <h3 className="text-3xl font-serif font-bold uppercase tracking-tight text-white group-hover:text-amber-600 transition-colors duration-500">
                        <Link to={`/product/${product.id}`}>{product.name.split('–')[1] || product.name}</Link>
                      </h3>
                    </div>
                  </div>

                  <p className="text-white/30 text-xs mb-10 line-clamp-2 font-medium leading-relaxed uppercase tracking-wide">
                    {product.description}
                  </p>

                  <div className="flex items-center justify-between pt-8 border-t border-white/5">
                    <div>
                      <span className="block text-[9px] text-white/20 font-black uppercase tracking-widest mb-1">MSRP</span>
                      <p className="text-3xl font-serif font-bold text-white">${product.price.toFixed(2)}</p>
                    </div>
                    <button
                      onClick={() => onAddToCart(product)}
                      className="h-16 w-16 bg-white hover:bg-amber-600 text-black hover:text-white transition-all duration-700 rounded-2xl flex items-center justify-center group/btn shadow-2xl relative overflow-hidden"
                    >
                      <ShoppingBag className="w-5 h-5 relative z-10 transition-transform duration-500 group-hover/btn:scale-110" />
                      <div className="absolute inset-0 bg-amber-600 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-500"></div>
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Brand Sign-off */}
        <div className="mt-48 pt-24 border-t border-white/5 flex flex-col items-center text-center">
          <div className="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center mb-8 border border-white/5">
            <Coffee className="w-5 h-5 text-amber-600" />
          </div>
          <h2 className="text-4xl font-serif font-bold text-white/10 uppercase tracking-[0.2em] mb-4">Falls Origin</h2>
          <p className="text-[10px] font-black text-white/5 uppercase tracking-[0.5em]">Small Batch Excellence • Toronto, CA</p>
        </div>
      </div>
    </div>
  );
};

export default Shop;