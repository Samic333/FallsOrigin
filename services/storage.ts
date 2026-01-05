
import { Order, ContactMessage, Review, Product, Analytics, OrderStatus } from '../types';

const KEYS = {
  ORDERS: 'foc_orders',
  MESSAGES: 'foc_messages',
  REVIEWS: 'foc_reviews',
  PRODUCTS: 'foc_products',
  STATS: 'foc_stats'
};

export const StorageService = {
  // Products
  getProducts: (): Product[] => {
    const data = localStorage.getItem(KEYS.PRODUCTS);
    return data ? JSON.parse(data) : [];
  },
  saveProduct: (product: Product) => {
    const products = StorageService.getProducts();
    const existingIndex = products.findIndex(p => p.id === product.id);
    if (existingIndex > -1) {
      products[existingIndex] = product;
    } else {
      products.push(product);
    }
    localStorage.setItem(KEYS.PRODUCTS, JSON.stringify(products));
  },
  deleteProduct: (id: string) => {
    const products = StorageService.getProducts().filter(p => p.id !== id);
    localStorage.setItem(KEYS.PRODUCTS, JSON.stringify(products));
  },

  // Stats
  incrementViews: () => {
    const current = Number(localStorage.getItem(KEYS.STATS) || 0);
    localStorage.setItem(KEYS.STATS, String(current + 1));
  },
  getAnalytics: (): Analytics => {
    const orders = StorageService.getOrders();
    const views = Number(localStorage.getItem(KEYS.STATS) || 0);
    const paidOrders = orders.filter(o => o.status !== OrderStatus.CANCELLED && o.status !== OrderStatus.REFUNDED);
    const totalSales = paidOrders.reduce((acc, o) => acc + o.total, 0);
    const totalOrders = orders.length;
    const conversionRate = views > 0 ? (totalOrders / views) * 100 : 0;

    return { views, totalSales, totalOrders, conversionRate };
  },

  // Orders
  getOrders: (): Order[] => {
    const data = localStorage.getItem(KEYS.ORDERS);
    return data ? JSON.parse(data) : [];
  },
  saveOrder: (order: Order) => {
    const orders = StorageService.getOrders();
    const existingIndex = orders.findIndex(o => o.id === order.id);
    if (existingIndex > -1) {
      orders[existingIndex] = order;
    } else {
      orders.push(order);
    }
    localStorage.setItem(KEYS.ORDERS, JSON.stringify(orders));
  },

  // Messages
  getMessages: (): ContactMessage[] => {
    const data = localStorage.getItem(KEYS.MESSAGES);
    return data ? JSON.parse(data) : [];
  },
  saveMessage: (msg: ContactMessage) => {
    const msgs = StorageService.getMessages();
    msgs.push(msg);
    localStorage.setItem(KEYS.MESSAGES, JSON.stringify(msgs));
  },
  updateMessageRead: (id: string) => {
    const msgs = StorageService.getMessages();
    const msg = msgs.find(m => m.id === id);
    if (msg) msg.read = true;
    localStorage.setItem(KEYS.MESSAGES, JSON.stringify(msgs));
  },

  // Reviews
  getReviews: (): Review[] => {
    const data = localStorage.getItem(KEYS.REVIEWS);
    return data ? JSON.parse(data) : [];
  },
  saveReview: (review: Review) => {
    const reviews = StorageService.getReviews();
    reviews.push(review);
    localStorage.setItem(KEYS.REVIEWS, JSON.stringify(reviews));
  },
  updateReviewStatus: (id: string, status: 'approved' | 'rejected') => {
    let reviews = StorageService.getReviews();
    const review = reviews.find(r => r.id === id);
    if (review) {
      review.status = status;
    }
    if (status === 'rejected') {
      reviews = reviews.filter(r => r.id !== id);
    }
    localStorage.setItem(KEYS.REVIEWS, JSON.stringify(reviews));
  }
};
