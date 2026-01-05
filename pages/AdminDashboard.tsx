
import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard,
  Package,
  MessageSquare,
  Star,
  LogOut,
  Plus,
  Search,
  Trash2,
  Edit2,
  TrendingUp,
  Users,
  DollarSign,
  Coffee,
  X as XIcon,
  Upload,
  Check,
  Clock,
  Sparkles,
  Tag,
  ExternalLink,
  Lock,
  Truck,
  PenTool
} from 'lucide-react';
import { api } from '../services/api';
import { Order, OrderStatus, ContactMessage, Review, Product, Analytics } from '../types';

const AdminDashboard: React.FC = () => {
  const navigate = useNavigate();
  // Auth State
  const [token, setToken] = useState(localStorage.getItem('admin_token'));
  const [isLoginLoading, setIsLoginLoading] = useState(false);
  const [loginError, setLoginError] = useState('');
  const [loginCreds, setLoginCreds] = useState({ username: '', password: '' });

  // Data State
  const [activeTab, setActiveTab] = useState<'analytics' | 'orders' | 'products' | 'messages' | 'reviews'>('analytics');
  const [orders, setOrders] = useState<Order[]>([]);
  const [messages, setMessages] = useState<ContactMessage[]>([]);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [analytics, setAnalytics] = useState<Analytics>({ views: 0, totalSales: 0, totalOrders: 0, conversionRate: 0 });

  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [showProductModal, setShowProductModal] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const [newProduct, setNewProduct] = useState<Partial<Product>>({
    roastIntensity: 3,
    roastNotes: ['Citrus', 'Honey', 'Floral'],
    type: 'Single Origin',
    weight: '500 g / 1.1 lb'
  });

  // Fetch Data on Auth
  useEffect(() => {
    if (token) {
      refreshData();
    }
  }, [token]);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoginLoading(true);
    setLoginError('');
    try {
      const response = await api.adminLogin(loginCreds.username, loginCreds.password);
      localStorage.setItem('admin_token', response.token);
      setToken(response.token);
    } catch (err: any) {
      setLoginError(err.response?.data?.error || 'Invalid credentials');
    } finally {
      setIsLoginLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('admin_token');
    setToken(null);
  };

  const refreshData = async () => {
    setIsLoading(true);
    try {
      const [ordersData, messagesData, reviewsData, analyticsData, productsData] = await Promise.all([
        api.getAdminOrders(),
        api.getAdminMessages(),
        api.getAdminReviews(),
        api.getAdminAnalytics(),
        api.getProducts() // Public endpoint is fine for list
      ]);

      setOrders(ordersData);
      setMessages(messagesData);
      setReviews(reviewsData);
      setAnalytics(analyticsData);
      setProducts(productsData);
    } catch (err) {
      console.error('Failed to fetch admin data:', err);
      if ((err as any)?.response?.status === 401) handleLogout();
    } finally {
      setIsLoading(false);
    }
  };

  const updateOrderStatus = async (orderId: string, newStatus: OrderStatus) => {
    try {
      await api.updateOrderStatus(orderId, newStatus);
      refreshData();
      if (selectedOrder) setSelectedOrder({ ...selectedOrder, status: newStatus });
    } catch (err) {
      alert('Failed to update status');
    }
  };

  const setOrderEta = async (orderId: string, eta: string) => {
    const time = prompt('Enter ETA (e.g. "Today by 5pm" or "2 hours"):', eta || '');
    if (!time) return;
    try {
      await api.setOrderEta(orderId, time);
      refreshData();
    } catch (err) {
      alert('Failed to set ETA');
    }
  };

  const addTracking = async (orderId: string) => {
    const number = prompt('Enter Tracking Number:', '');
    const carrier = prompt('Enter Carrier (e.g. Canada Post, Fedex):', 'Canada Post');
    if (!number || !carrier) return;
    try {
      await api.addTracking(orderId, number, carrier);
      refreshData();
    } catch (err) {
      alert('Failed to add tracking');
    }
  };

  // Product Management (Mock implementation for complex file uploads, but structure is ready)
  const saveProduct = async () => {
    // Note: Real file upload would require a FormData implementation in API
    // For now, we'll assume the user is updating text fields or using existing image URLs
    // In a real app, integrate api.createProduct / api.updateProduct here
    alert('Product editing requires backend file upload implementation. This is a placeholder.');
    setShowProductModal(false);
  };

  const deleteProduct = async (id: string) => {
    if (!confirm("Are you sure?")) return;
    try {
      await api.deleteProduct(id);
      refreshData();
    } catch (err) {
      alert('Failed to delete product');
    }
  };

  const approveReview = async (id: string) => {
    try {
      await api.approveReview(id);
      refreshData();
    } catch (err) {
      alert('Failed to approve review');
    }
  };

  if (!token) {
    return (
      <div className="min-h-screen bg-black flex items-center justify-center p-4">
        <div className="bg-stone-900 p-8 rounded-2xl border border-stone-800 w-full max-w-md">
          <div className="text-center mb-8">
            <Coffee className="w-12 h-12 text-amber-600 mx-auto mb-4" />
            <h1 className="text-2xl font-serif font-bold text-white">Falls Origin Admin</h1>
            <p className="text-stone-500 text-sm mt-2">Secure Gateway</p>
          </div>
          <form onSubmit={handleLogin} className="space-y-6">
            <div className="space-y-4">
              <input
                required
                className="w-full bg-black border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-amber-600"
                placeholder="Username"
                value={loginCreds.username}
                onChange={e => setLoginCreds({ ...loginCreds, username: e.target.value })}
              />
              <input
                required
                type="password"
                className="w-full bg-black border border-stone-800 p-4 rounded text-white focus:outline-none focus:border-amber-600"
                placeholder="Password"
                value={loginCreds.password}
                onChange={e => setLoginCreds({ ...loginCreds, password: e.target.value })}
              />
            </div>
            {loginError && <p className="text-red-500 text-xs text-center">{loginError}</p>}
            <button
              disabled={isLoginLoading}
              className="w-full py-4 bg-amber-600 text-white font-bold uppercase text-xs tracking-[0.2em] rounded hover:bg-amber-700 transition-all disabled:opacity-50"
            >
              {isLoginLoading ? 'Authenticating...' : 'Access Portal'}
            </button>
          </form>
        </div>
      </div>
    );
  }

  return (
    <div className="flex h-screen bg-[#050505] overflow-hidden text-white">
      {/* Sidebar */}
      <aside className="w-72 bg-[#0a0a0a] border-r border-white/5 flex flex-col">
        <div className="p-10">
          <div className="flex items-center space-x-3 mb-12">
            <div className="w-10 h-10 bg-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-600/20">
              <Coffee className="w-6 h-6 text-white" />
            </div>
            <div className="flex flex-col">
              <span className="text-sm font-black uppercase tracking-[0.3em]">Falls Origin</span>
              <span className="text-[9px] text-white/30 uppercase tracking-[0.2em]">Management</span>
            </div>
          </div>

          <Link
            to="/"
            className="flex items-center space-x-3 px-6 py-4 mb-8 bg-white/5 hover:bg-white/10 border border-white/5 rounded-2xl transition-all group"
          >
            <ExternalLink className="w-4 h-4 text-amber-600 group-hover:scale-110 transition-transform" />
            <span className="text-[10px] font-black uppercase tracking-[0.2em] text-white/60 group-hover:text-white">View Store</span>
          </Link>

          <nav className="space-y-3">
            {[
              { id: 'analytics', label: 'Dashboard', icon: LayoutDashboard },
              { id: 'orders', label: 'Orders', icon: Package, badge: orders.filter(o => o.status === OrderStatus.PAID).length },
              { id: 'products', label: 'Inventory', icon: Coffee },
              { id: 'messages', label: 'Concierge', icon: MessageSquare, badge: messages.filter(m => !m.read).length },
              { id: 'reviews', label: 'Reviews', icon: Star, badge: reviews.filter(r => !r.approved).length }
            ].map((item) => (
              <button
                key={item.id}
                onClick={() => setActiveTab(item.id as any)}
                className={`w-full flex items-center px-6 py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all group ${activeTab === item.id
                    ? 'bg-white text-black shadow-xl shadow-white/5'
                    : 'text-white/40 hover:text-white hover:bg-white/5'
                  }`}
              >
                <item.icon className={`w-4 h-4 mr-4 ${activeTab === item.id ? 'text-black' : 'text-white/20 group-hover:text-amber-600'}`} />
                {item.label}
                {item.badge ? (
                  <span className={`ml-auto w-5 h-5 rounded-full flex items-center justify-center text-[9px] ${activeTab === item.id ? 'bg-black text-white' : 'bg-amber-600 text-white'}`}>
                    {item.badge}
                  </span>
                ) : null}
              </button>
            ))}
          </nav>
        </div>

        <div className="mt-auto p-10">
          <button
            onClick={handleLogout}
            className="w-full flex items-center px-6 py-4 text-white/20 hover:text-red-500 text-[11px] font-black uppercase tracking-widest transition-all group"
          >
            <LogOut className="w-4 h-4 mr-4 group-hover:scale-110 transition-transform" />
            Sign Out
          </button>
        </div>
      </aside>

      {/* Main Area */}
      <main className="flex-grow flex flex-col overflow-hidden relative">
        {isLoading && (
          <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-amber-600 to-transparent opacity-50 animate-pulse"></div>
        )}
        <header className="h-28 border-b border-white/5 flex items-center justify-between px-12 bg-[#050505]/80 backdrop-blur-2xl">
          <div>
            <h2 className="text-2xl font-serif font-bold uppercase tracking-tight">
              {activeTab === 'analytics' ? 'System Overview' : activeTab.charAt(0).toUpperCase() + activeTab.slice(1)}
            </h2>
            <p className="text-[10px] text-white/30 uppercase tracking-[0.3em] mt-1">Status: Operational • Connected</p>
          </div>
          <div className="flex items-center space-x-6">
            <button onClick={refreshData} className="text-xs text-white/40 hover:text-white uppercase font-bold tracking-widest">Refresh Data</button>
            {activeTab === 'products' && (
              <button
                onClick={() => setShowProductModal(true)}
                className="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-full text-[11px] font-black uppercase tracking-widest transition-all flex items-center"
              >
                <Plus className="w-4 h-4 mr-2" /> New Roast
              </button>
            )}
          </div>
        </header>

        <div className="flex-grow overflow-y-auto p-12 no-scrollbar">
          {activeTab === 'analytics' && (
            <div className="space-y-12 animate-in fade-in duration-700">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                {[
                  { label: 'Website Traffic', value: analytics.views?.toLocaleString() || '0', icon: Users, sub: 'Total Views' },
                  { label: 'Total Revenue', value: `$${analytics.totalSales?.toFixed(2) || '0.00'}`, icon: DollarSign, sub: 'CAD Gross' },
                  { label: 'Order Volume', value: analytics.totalOrders || 0, icon: Package, sub: 'All Time' },
                  { label: 'Conversion', value: `${analytics.conversionRate?.toFixed(1) || '0'}%`, icon: TrendingUp, sub: 'Rate' }
                ].map((stat, i) => (
                  <div key={i} className="bg-[#0a0a0a] border border-white/5 p-8 rounded-[2rem] relative overflow-hidden group">
                    <stat.icon className="w-6 h-6 text-white/40 mb-6" />
                    <p className="text-[10px] text-white/30 font-black uppercase tracking-[0.3em] mb-2">{stat.label}</p>
                    <h3 className="text-3xl font-serif font-bold mb-2">{stat.value}</h3>
                    <p className="text-[10px] text-white/20 uppercase tracking-widest">{stat.sub}</p>
                  </div>
                ))}
              </div>
            </div>
          )}

          {activeTab === 'orders' && (
            <div className="grid grid-cols-1 xl:grid-cols-3 gap-12">
              <div className="xl:col-span-2 space-y-4">
                {orders.map(order => (
                  <div
                    key={order.id}
                    onClick={() => setSelectedOrder(order)}
                    className={`p-8 rounded-3xl border transition-all cursor-pointer ${selectedOrder?.id === order.id ? 'bg-white/5 border-amber-600/50' : 'bg-[#0a0a0a] border-white/5 hover:border-white/10'}`}
                  >
                    <div className="flex justify-between items-center">
                      <div className="flex items-center space-x-6">
                        <div className={`w-12 h-12 rounded-2xl flex items-center justify-center ${order.status === OrderStatus.PAID ? 'bg-blue-600/10 text-blue-500' : 'bg-white/5 text-white/20'}`}>
                          <Package className="w-5 h-5" />
                        </div>
                        <div>
                          <h4 className="text-sm font-bold uppercase tracking-wide">{order.customerName}</h4>
                          <p className="text-[10px] text-white/30 uppercase tracking-widest mt-1">{order.id}</p>
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-bold mb-2">${order.total.toFixed(2)}</p>
                        <span className={`px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-[0.2em] ${order.status === OrderStatus.DELIVERED ? 'bg-green-600/20 text-green-500' :
                            order.status === OrderStatus.PAID ? 'bg-blue-600/20 text-blue-500' : 'bg-white/5 text-white/40'
                          }`}>
                          {order.status}
                        </span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Order Detail View */}
              <div className="xl:col-span-1 bg-[#0a0a0a] border border-white/5 rounded-[2.5rem] p-8 h-fit sticky top-0">
                {selectedOrder ? (
                  <div className="space-y-8 animate-in slide-in-from-right duration-500">
                    <div>
                      <h3 className="text-xl font-serif font-bold mb-2">Order Details</h3>
                      <p className="text-xs text-white/40 uppercase tracking-widest">{selectedOrder.id}</p>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <button onClick={() => updateOrderStatus(selectedOrder.id, OrderStatus.PREPARING)} className="p-4 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-bold uppercase tracking-widest transition-all">Start Preparing</button>
                      <button onClick={() => updateOrderStatus(selectedOrder.id, OrderStatus.DELIVERED)} className="p-4 rounded-xl bg-green-900/20 hover:bg-green-900/30 text-green-500 text-xs font-bold uppercase tracking-widest transition-all">Mark Delivered</button>
                    </div>

                    <div className="space-y-4">
                      <div className="flex justify-between items-center p-4 bg-white/5 rounded-xl">
                        <span className="text-xs text-white/40 uppercase tracking-widest">Delivery Type</span>
                        <span className="text-xs font-bold uppercase">{selectedOrder.deliveryMethod}</span>
                      </div>
                      {selectedOrder.deliveryMethod === 'Local Delivery' ? (
                        <button onClick={() => setOrderEta(selectedOrder.id, selectedOrder.eta)} className="w-full p-4 bg-amber-600/10 hover:bg-amber-600/20 text-amber-600 rounded-xl text-xs font-bold uppercase tracking-widest flex items-center justify-center">
                          <Clock className="w-4 h-4 mr-2" /> {selectedOrder.eta || 'Set ETA'}
                        </button>
                      ) : (
                        <button onClick={() => addTracking(selectedOrder.id)} className="w-full p-4 bg-blue-600/10 hover:bg-blue-600/20 text-blue-500 rounded-xl text-xs font-bold uppercase tracking-widest flex items-center justify-center">
                          <Truck className="w-4 h-4 mr-2" /> {selectedOrder.trackingNumber || 'Add Tracking'}
                        </button>
                      )}
                    </div>

                    <div className="border-t border-white/5 pt-8">
                      <h4 className="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Items</h4>
                      <div className="space-y-3">
                        {selectedOrder.items.map(item => (
                          <div key={item.product.id} className="flex justify-between text-sm">
                            <span>{item.quantity}x {item.product.name}</span>
                            <span className="text-white/40">${(item.product.price * item.quantity).toFixed(2)}</span>
                          </div>
                        ))}
                      </div>
                    </div>

                    <div className="border-t border-white/5 pt-8">
                      <h4 className="text-xs font-bold uppercase tracking-widest text-white/40 mb-4">Customer</h4>
                      <div className="text-sm space-y-2 text-white/60">
                        <p><span className="text-white/20 mr-2">NAME:</span> {selectedOrder.customerName}</p>
                        <p><span className="text-white/20 mr-2">EMAIL:</span> {selectedOrder.email}</p>
                        <p><span className="text-white/20 mr-2">ADDR:</span> {selectedOrder.address}, {selectedOrder.city}, {selectedOrder.postalCode}</p>
                      </div>
                    </div>
                  </div>
                ) : (
                  <div className="h-full flex flex-col items-center justify-center text-white/20">
                    <Package className="w-12 h-12 mb-4" />
                    <p className="text-xs font-bold uppercase tracking-widest">Select an order</p>
                  </div>
                )}
              </div>
            </div>
          )}

          {activeTab === 'products' && (
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
              {products.map(p => (
                <div key={p.id} className="bg-[#0a0a0a] border border-white/5 rounded-[2.5rem] overflow-hidden group flex flex-col">
                  <div className="relative h-64 overflow-hidden bg-stone-900 flex items-center justify-center p-8">
                    <img
                      src={p.image}
                      className="max-h-full max-w-full object-contain"
                      alt={p.name}
                    />
                    <div className="absolute top-6 right-6 flex space-x-2">
                      <button
                        onClick={() => deleteProduct(p.id)}
                        className="p-3 bg-red-600/20 text-red-500 rounded-full hover:bg-red-600 hover:text-white transition-all"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                  <div className="p-8">
                    <h4 className="text-lg font-bold uppercase tracking-tight mb-2">{p.name}</h4>
                    <p className="text-amber-600 font-bold mb-4">${p.price.toFixed(2)}</p>
                    <p className="text-white/40 text-xs line-clamp-2 mb-4">{p.description}</p>
                    <div className="flex flex-wrap gap-2">
                      <span className="px-2 py-1 bg-white/5 text-[9px] uppercase tracking-widest rounded">{p.type}</span>
                      <span className="px-2 py-1 bg-white/5 text-[9px] uppercase tracking-widest rounded">{p.origin}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {activeTab === 'reviews' && (
            <div className="space-y-4">
              {reviews.map(review => (
                <div key={review.id} className="bg-[#0a0a0a] border border-white/5 p-8 rounded-2xl flex justify-between items-start">
                  <div>
                    <div className="flex items-center space-x-2 mb-2">
                      {[1, 2, 3, 4, 5].map(s => (
                        <Star key={s} className={`w-3 h-3 ${s <= review.rating ? 'text-amber-600 fill-current' : 'text-stone-800'}`} />
                      ))}
                    </div>
                    <h4 className="font-bold text-sm mb-1">{review.userName}</h4>
                    <p className="text-white/40 text-xs italic">"{review.comment}"</p>
                    <p className="text-white/20 text-[9px] mt-2 uppercase tracking-widest">{new Date(review.createdAt).toLocaleDateString()}</p>
                  </div>
                  {!review.approved && (
                    <button onClick={() => approveReview(review.id)} className="px-4 py-2 bg-green-600/20 text-green-500 rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">
                      Approve
                    </button>
                  )}
                </div>
              ))}
              {reviews.length === 0 && <p className="text-white/20 text-center uppercase tracking-widest text-xs mt-12">No reviews found.</p>}
            </div>
          )}

          {activeTab === 'messages' && (
            <div className="space-y-4">
              {messages.map(msg => (
                <div key={msg.id} className="bg-[#0a0a0a] border border-white/5 p-8 rounded-2xl">
                  <div className="flex justify-between mb-4">
                    <h4 className="font-bold text-sm">{msg.name} <span className="text-white/40 font-normal">({msg.email})</span></h4>
                    <span className="text-white/20 text-[9px] uppercase tracking-widest">{new Date(msg.createdAt).toLocaleDateString()}</span>
                  </div>
                  <h5 className="text-amber-600 text-xs font-bold uppercase tracking-widest mb-2">{msg.subject}</h5>
                  <p className="text-white/60 text-sm leading-relaxed">{msg.message}</p>
                </div>
              ))}
              {messages.length === 0 && <p className="text-white/20 text-center uppercase tracking-widest text-xs mt-12">No messages found.</p>}
            </div>
          )}

        </div>
      </main>
    </div>
  );
};

export default AdminDashboard;