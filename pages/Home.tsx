import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, Coffee, ShieldCheck, Truck, MapPin } from 'lucide-react';
import { FALLS_ORIGIN_COFFEE } from '../constants';
import { StorageService } from '../services/storage';
import { Product } from '../types';

const Home: React.FC = () => {
  const [featuredProduct, setFeaturedProduct] = useState<Product | null>(null);

  useEffect(() => {
    // Pull the primary product from storage so admin changes reflect here
    const products = StorageService.getProducts();
    const main = products.find(p => p.id === FALLS_ORIGIN_COFFEE.id) || FALLS_ORIGIN_COFFEE;
    setFeaturedProduct(main);
  }, []);

  return (
    <div className="flex flex-col">
      {/* Hero Section */}
      <section className="relative h-[90vh] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-black">
          <img
            src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&q=80&w=2000"
            alt="Coffee Hero"
            className="w-full h-full object-cover opacity-50 scale-105"
          />
        </div>
        <div className="relative z-10 text-center max-w-4xl px-4">
          <h1 className="text-5xl md:text-8xl font-serif font-bold text-white mb-6 tracking-tight leading-tight uppercase">
            The Soul of <span className="italic text-amber-600">Ethiopia</span>, <br />
            Roasted in Canada.
          </h1>
          <p className="text-stone-300 text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed uppercase tracking-tight font-medium opacity-60">
            Discover the vibrant, floral profile of heirloom Yirgacheffe beans. Hand-roasted in small batches for the ultimate clarity of flavor.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
            <Link
              to="/shop"
              className="px-10 py-5 bg-white text-black font-black uppercase tracking-[0.4em] text-[11px] hover:bg-amber-600 hover:text-white transition-all flex items-center group rounded-2xl"
            >
              Shop Collection <ArrowRight className="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </Link>
            <Link
              to="/track"
              className="px-10 py-5 bg-stone-900/50 backdrop-blur-md text-white font-black uppercase tracking-[0.4em] text-[11px] border border-white/5 hover:border-white transition-all rounded-2xl"
            >
              Track Order
            </Link>
          </div>
        </div>
        <div className="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce">
          <div className="w-px h-12 bg-gradient-to-b from-amber-600 to-transparent"></div>
        </div>
      </section>

      {/* Feature Section */}
      <section className="py-24 bg-stone-950 border-y border-white/5">
        <div className="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-16">
          <div className="text-center group">
            <div className="w-16 h-16 bg-white/5 border border-white/5 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-amber-600 transition-all duration-500">
              <ShieldCheck className="w-8 h-8 text-white" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-[0.4em] mb-4 text-white">Direct Trade</h3>
            <p className="text-white/30 text-xs leading-relaxed uppercase tracking-widest font-bold">We work directly with farmers to ensure fair compensation and sustainable practices at every origin.</p>
          </div>
          <div className="text-center group">
            <div className="w-16 h-16 bg-white/5 border border-white/5 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-amber-600 transition-all duration-500">
              <Truck className="w-8 h-8 text-white" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-[0.4em] mb-4 text-white">Fast Shipping</h3>
            <p className="text-white/30 text-xs leading-relaxed uppercase tracking-widest font-bold">Free local delivery in the GTA for orders over $50. Tracked postal shipping across Canada.</p>
          </div>
          <div className="text-center group">
            <div className="w-16 h-16 bg-white/5 border border-white/5 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-amber-600 transition-all duration-500">
              <Coffee className="w-8 h-8 text-white" />
            </div>
            <h3 className="text-xs font-black uppercase tracking-[0.4em] mb-4 text-white">Fresh Roast</h3>
            <p className="text-white/30 text-xs leading-relaxed uppercase tracking-widest font-bold">We roast to order. Your coffee arrives with its roast date clearly marked, ensuring peak freshness.</p>
          </div>
        </div>
      </section>

      {/* Product Highlight */}
      {featuredProduct && (
        <section className="py-32 bg-[#0a0a0a] overflow-hidden">
          <div className="max-w-7xl mx-auto px-4 flex flex-col lg:flex-row items-center gap-24">
            <div className="w-full lg:w-1/2 relative group">
              <div className="absolute -inset-10 bg-amber-600/5 blur-[100px] group-hover:bg-amber-600/10 transition-all duration-1000"></div>
              <div className="relative rounded-[3rem] bg-stone-900 border border-white/5 shadow-2xl w-full aspect-[4/5] flex items-center justify-center p-12 overflow-hidden">
                <img
                  src={featuredProduct.image}
                  alt="Falls Origin Coffee Bag"
                  className="w-full h-full object-contain transition-transform duration-[2s] group-hover:scale-110"
                />
              </div>
            </div>
            <div className="w-full lg:w-1/2">
              <div className="flex items-center space-x-4 mb-8">
                <div className="w-2 h-2 rounded-full bg-amber-600"></div>
                <span className="text-amber-600 font-black uppercase tracking-[0.5em] text-[10px]">Small Batch Limited Batch</span>
              </div>
              <h2 className="text-5xl md:text-7xl font-serif font-bold text-white mb-8 leading-[1.1] tracking-tighter uppercase">
                {featuredProduct.name.split('–')[1] || featuredProduct.name}
              </h2>
              <div className="flex items-center space-x-6 mb-10">
                <span className="text-4xl font-serif font-bold text-white">${featuredProduct.price.toFixed(2)}</span>
                <span className="px-4 py-1.5 bg-white/5 border border-white/10 text-white/30 text-[9px] font-black rounded-full uppercase tracking-widest">
                  {featuredProduct.weight}
                </span>
              </div>
              <p className="text-white/40 text-lg mb-12 leading-relaxed font-medium uppercase tracking-tight">
                Experience the clarity of high-altitude heirloom beans. This single-origin roast from Yirgacheffe region is the pinnacle of our collection.
              </p>
              <Link
                to={`/product/${featuredProduct.id}`}
                className="inline-block px-12 py-6 bg-white text-black font-black uppercase tracking-[0.5em] text-[11px] hover:bg-amber-600 hover:text-white transition-all rounded-2xl shadow-2xl"
              >
                Acquire Now
              </Link>
            </div>
          </div>
        </section>
      )}
    </div>
  );
};

export default Home;