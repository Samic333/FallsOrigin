
import React from 'react';
import { X, Minus, Plus, ShoppingCart, Trash2 } from 'lucide-react';
import { CartItem } from '../types';

interface CartDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  items: CartItem[];
  onUpdateQuantity: (id: string, delta: number) => void;
  onRemove: (id: string) => void;
  onCheckout: () => void;
}

const CartDrawer: React.FC<CartDrawerProps> = ({
  isOpen,
  onClose,
  items,
  onUpdateQuantity,
  onRemove,
  onCheckout
}) => {
  const total = items.reduce((acc, item) => acc + item.product.price * item.quantity, 0);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[60] overflow-hidden">
      <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={onClose} />
      <div className="absolute inset-y-0 right-0 max-w-md w-full bg-stone-900 shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
        <div className="px-6 py-6 border-b border-stone-800 flex justify-between items-center">
          <h2 className="text-lg font-bold uppercase tracking-widest flex items-center">
            <ShoppingCart className="w-5 h-5 mr-3 text-amber-600" />
            Your Cart
          </h2>
          <button onClick={onClose} className="p-2 text-stone-400 hover:text-white transition-colors">
            <X className="w-6 h-6" />
          </button>
        </div>

        <div className="flex-grow overflow-y-auto p-6 no-scrollbar">
          {items.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full text-stone-500">
              <ShoppingCart className="w-16 h-16 mb-4 opacity-20" />
              <p>Your cart is empty.</p>
              <button
                onClick={onClose}
                className="mt-6 px-8 py-3 bg-white text-black font-bold uppercase text-xs tracking-widest hover:bg-stone-200 transition-colors"
              >
                Go Shop
              </button>
            </div>
          ) : (
            <div className="space-y-8">
              {items.map((item) => (
                <div key={item.product.id} className="flex space-x-4">
                  <div className="w-20 h-20 bg-stone-800 rounded flex-shrink-0 overflow-hidden">
                    <img src={item.product.image} alt={item.product.name} className="w-full h-full object-cover" />
                  </div>
                  <div className="flex-grow">
                    <div className="flex justify-between">
                      <h3 className="text-sm font-medium uppercase tracking-tight">{item.product.name}</h3>
                      <button onClick={() => onRemove(item.product.id)} className="text-stone-600 hover:text-red-500">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                    <p className="text-xs text-stone-500 mb-2">{item.product.weight}</p>
                    <div className="flex items-center justify-between">
                      <div className="flex items-center border border-stone-800 rounded">
                        <button
                          onClick={() => onUpdateQuantity(item.product.id, -1)}
                          className="p-1 hover:text-white text-stone-500"
                        >
                          <Minus className="w-4 h-4" />
                        </button>
                        <span className="px-3 text-sm">{item.quantity}</span>
                        <button
                          onClick={() => onUpdateQuantity(item.product.id, 1)}
                          className="p-1 hover:text-white text-stone-500"
                        >
                          <Plus className="w-4 h-4" />
                        </button>
                      </div>
                      <span className="text-sm font-bold text-amber-600">
                        ${(item.product.price * item.quantity).toFixed(2)}
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {items.length > 0 && (
          <div className="p-6 bg-stone-950 border-t border-stone-800">
            <div className="flex justify-between items-center mb-6">
              <span className="text-stone-400 uppercase tracking-widest text-xs">Subtotal</span>
              <span className="text-xl font-serif font-bold">${total.toFixed(2)} CAD</span>
            </div>
            <p className="text-[10px] text-stone-500 text-center mb-6 italic">
              Shipping and taxes calculated at checkout.
            </p>
            <button
              onClick={onCheckout}
              className="w-full py-4 bg-white text-black font-bold uppercase text-sm tracking-[0.2em] hover:bg-amber-600 hover:text-white transition-all duration-300 transform active:scale-[0.98]"
            >
              Secure Checkout
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default CartDrawer;
