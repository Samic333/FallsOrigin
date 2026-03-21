
import React, { useState, useEffect } from 'react';
import { useLocation, Link } from 'react-router-dom';
import { Search, Package, MapPin, Truck, CheckCircle2, Clock, ExternalLink, AlertTriangle } from 'lucide-react';
import { Order, OrderStatus, DeliveryMethod } from '../types';
import { api } from '../services/api';

const TrackOrder: React.FC = () => {
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);
  const token = queryParams.get('token');
  const initialId = queryParams.get('id') || '';
  const initialEmail = queryParams.get('email') || '';

  const [orderId, setOrderId] = useState(initialId);
  const [email, setEmail] = useState(initialEmail);
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Auto-fetch if token is present
  useEffect(() => {
    if (token) {
      handleFetchByToken(token);
    } else if (initialId && initialEmail) {
      handleSearch(null);
    }
  }, [token, initialId, initialEmail]);

  const handleFetchByToken = async (trackingToken: string) => {
    setLoading(true);
    setError('');
    try {
      const data = await api.trackOrderByToken(trackingToken);
      setOrder(data);
    } catch (err: any) {
      setError('Invalid or expired tracking link.');
      setOrder(null);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = async (e: React.FormEvent | null) => {
    e?.preventDefault();
    if (!orderId || !email) return;

    setLoading(true);
    setError('');

    try {
      const data = await api.trackOrderByEmail(orderId, email);
      setOrder(data);
    } catch (err: any) {
      setError(err.response?.data?.error || 'Order not found. Please check your order ID and email.');
      setOrder(null);
    } finally {
      setLoading(false);
    }
  };

  const statusSteps = [
    OrderStatus.PAID,
    OrderStatus.ACCEPTED,
    OrderStatus.PREPARING,
    order?.deliveryMethod === DeliveryMethod.LOCAL ? OrderStatus.OUT_FOR_DELIVERY : OrderStatus.SHIPPED,
    OrderStatus.DELIVERED
  ];

  const getCurrentStepIndex = () => {
    if (!order) return -1;
    // Handle Refunded/Cancelled statuses
    if (order.status === 'Refunded' || order.status === 'Cancelled') return -1;
    return statusSteps.indexOf(order.status);
  };

  return (
    <div className="py-20 bg-stone-950 min-h-screen">
      <div className="max-w-4xl mx-auto px-4">
        <h1 className="text-4xl font-serif font-bold mb-12 text-center">Track Your Roast</h1>

        {/* Search Form */}
        {!order && !loading && (
          <form onSubmit={handleSearch} className="bg-stone-900 p-8 rounded-2xl shadow-xl border border-stone-800 mb-12 max-w-2xl mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div className="relative">
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-stone-500 w-5 h-5" />
                <input
                  required
                  placeholder="Order ID (e.g. ORD-123)"
                  className="w-full bg-stone-950 border border-stone-800 p-4 pl-12 rounded text-white focus:outline-none focus:border-amber-600 transition-all uppercase"
                  value={orderId}
                  onChange={e => setOrderId(e.target.value)}
                />
              </div>
              <input
                required
                type="email"
                placeholder="Email Address"
                className="w-full bg-stone-950 border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-amber-600 transition-all"
                value={email}
                onChange={e => setEmail(e.target.value)}
              />
            </div>
            <button
              disabled={loading}
              className="w-full py-4 bg-white text-black font-bold uppercase text-xs tracking-[0.2em] hover:bg-amber-600 hover:text-white transition-all disabled:opacity-50"
            >
              {loading ? 'Searching...' : 'Locate Order'}
            </button>
            {error && <p className="mt-4 text-red-500 text-xs text-center">{error}</p>}
          </form>
        )}

        {loading && (
          <div className="py-20 text-center">
            <div className="w-10 h-10 border-4 border-amber-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p className="text-stone-500 text-xs uppercase tracking-widest">Retrieving Order Data...</p>
          </div>
        )}

        {order && (
          <div className="space-y-8 animate-in fade-in duration-500">
            <Link to="/track" onClick={() => setOrder(null)} className="text-xs text-stone-500 hover:text-white uppercase tracking-widest mb-4 inline-block">&larr; Track Another Order</Link>

            {/* Status Timeline */}
            <div className="bg-stone-900 p-8 rounded-2xl border border-stone-800">
              <div className="flex justify-between items-center mb-12">
                <div>
                  <h2 className="text-xl font-bold uppercase tracking-tight">{order.id}</h2>
                  <p className="text-stone-500 text-xs uppercase tracking-widest mt-1">Placed on {new Date(order.createdAt).toLocaleDateString()}</p>
                </div>
                <div className={`px-4 py-2 border rounded text-xs font-bold uppercase tracking-widest ${order.status === 'Refunded' || order.status === 'Cancelled' ? 'bg-red-500/10 text-red-500 border-red-500/30' : 'bg-amber-600/10 text-amber-600 border-amber-600/30'
                  }`}>
                  {order.status}
                </div>
              </div>

              {order.status !== 'Refunded' && order.status !== 'Cancelled' ? (
                <div className="relative">
                  <div className="absolute top-5 left-5 right-5 h-1 bg-stone-800 -z-0"></div>
                  <div
                    className="absolute top-5 left-5 h-1 bg-amber-600 transition-all duration-1000 -z-0"
                    style={{ width: `${(getCurrentStepIndex() / (statusSteps.length - 1)) * 100}%` }}
                  ></div>
                  <div className="flex justify-between relative z-10">
                    {statusSteps.map((step, idx) => {
                      const isCompleted = idx <= getCurrentStepIndex();
                      return (
                        <div key={step} className="flex flex-col items-center">
                          <div className={`w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 ${isCompleted ? 'bg-amber-600 text-white' : 'bg-stone-800 text-stone-600'}`}>
                            {isCompleted ? <CheckCircle2 className="w-6 h-6" /> : <Clock className="w-6 h-6" />}
                          </div>
                          <span className={`mt-4 text-[10px] font-bold uppercase tracking-widest text-center max-w-[80px] ${isCompleted ? 'text-white' : 'text-stone-600'}`}>
                            {step}
                          </span>
                        </div>
                      );
                    })}
                  </div>
                </div>
              ) : (
                <div className="bg-stone-950 p-6 rounded-xl border border-stone-800 flex items-center text-red-400">
                  <AlertTriangle className="w-6 h-6 mr-4" />
                  <div>
                    <p className="font-bold text-sm uppercase tracking-widest">Order {order.status}</p>
                    <p className="text-xs mt-1 opacity-70">This order has been {order.status.toLowerCase()}. Please check your email for details.</p>
                  </div>
                </div>
              )}
            </div>

            {/* Delivery Details */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div className="bg-stone-900 p-8 rounded-2xl border border-stone-800">
                <h3 className="text-xs font-bold uppercase tracking-[0.2em] text-stone-500 mb-6">Delivery Details</h3>
                <div className="space-y-4">
                  <div className="flex items-center">
                    {order.deliveryMethod === DeliveryMethod.LOCAL ? <MapPin className="text-amber-600 w-5 h-5 mr-4" /> : <Truck className="text-amber-600 w-5 h-5 mr-4" />}
                    <div>
                      <p className="text-sm font-bold uppercase">{order.deliveryMethod}</p>
                      <p className="text-stone-500 text-xs mt-1">{order.address}, {order.city}</p>
                    </div>
                  </div>
                  {order.eta && (
                    <div className="flex items-center p-4 bg-amber-600/5 border border-amber-600/20 rounded-lg">
                      <Clock className="text-amber-600 w-5 h-5 mr-4" />
                      <div>
                        <p className="text-xs text-stone-400 uppercase tracking-widest">Estimated Arrival</p>
                        <p className="text-sm font-bold text-white mt-1">{order.eta}</p>
                      </div>
                    </div>
                  )}
                  {order.trackingNumber && (
                    <div className="flex items-center p-4 bg-blue-600/5 border border-blue-600/20 rounded-lg">
                      <Package className="text-blue-500 w-5 h-5 mr-4" />
                      <div>
                        <p className="text-xs text-stone-400 uppercase tracking-widest">{order.carrier} Tracking</p>
                        <a href={`https://www.canadapost-postescanada.ca/track-reperage/en#/resultList?searchFor=${order.trackingNumber}`} target="_blank" className="text-sm font-bold text-blue-500 mt-1 flex items-center hover:underline">
                          {order.trackingNumber} <ExternalLink className="w-3 h-3 ml-2" />
                        </a>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              <div className="bg-stone-900 p-8 rounded-2xl border border-stone-800">
                <h3 className="text-xs font-bold uppercase tracking-[0.2em] text-stone-500 mb-6">Order Contents</h3>
                <div className="space-y-4">
                  {order.items.map((item: any) => (
                    <div key={item.product.id} className="flex justify-between items-center text-sm">
                      <span className="text-stone-300">{item.quantity}x {item.product.name}</span>
                      <span className="font-bold">${(item.product.price * item.quantity).toFixed(2)}</span>
                    </div>
                  ))}
                  <div className="pt-4 border-t border-stone-800 flex justify-between font-serif font-bold text-lg">
                    <span>Total</span>
                    <span className="text-amber-600">${order.total.toFixed(2)}</span>
                  </div>
                </div>
              </div>
            </div>

            {order.status === OrderStatus.DELIVERED && (
              <div className="bg-stone-900 p-12 rounded-2xl border border-amber-600/20 text-center">
                <h3 className="text-2xl font-serif font-bold mb-4">How was the roast?</h3>
                <p className="text-stone-500 mb-8">We hope you're enjoying your Falls Origin coffee. Your feedback helps us perfect our craft.</p>
                <Link to={`/contact?subject=Review%20for%20Order%20${order.id}`} className="px-8 py-4 bg-white text-black font-bold uppercase text-xs tracking-widest hover:bg-amber-600 hover:text-white transition-all">
                  Leave a Review
                </Link>
              </div>
            )}

            {/* Audit Log Display */}
            <div className="border-t border-stone-800 pt-8 mt-8">
              <h3 className="text-xs font-bold uppercase tracking-[0.2em] text-stone-500 mb-6">Order History</h3>
              <div className="space-y-4">
                {(order.audit_log || order.auditLog)?.map((log: any, i: number) => (
                  <div key={i} className="flex text-xs">
                    <span className="text-stone-500 w-32">{new Date(log.timestamp).toLocaleString()}</span>
                    <span className="text-white font-bold uppercase w-24">{log.status}</span>
                    <span className="text-stone-400">{log.notes}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default TrackOrder;
