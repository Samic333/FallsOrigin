
export enum OrderStatus {
  PAID = 'Paid',
  ACCEPTED = 'Accepted',
  PREPARING = 'Preparing',
  OUT_FOR_DELIVERY = 'Out for Delivery',
  SHIPPED = 'Shipped',
  DELIVERED = 'Delivered',
  CANCELLED = 'Cancelled',
  REFUNDED = 'Refunded'
}

export enum DeliveryMethod {
  LOCAL = 'Local Delivery',
  POSTAL = 'Postal Shipping'
}

export interface Product {
  id: string;
  name: string;
  description: string;
  price: number;
  weight: string;
  image: string;
  origin: string;
  roastIntensity: number;
  roastNotes: string[];
  type?: string; // e.g., 'Single Origin', 'Blend', 'Espresso'
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface Review {
  id: string;
  orderId: string;
  customerName: string;
  rating: number;
  comment: string;
  status: 'pending' | 'approved' | 'rejected';
  createdAt: string;
}

export interface ContactMessage {
  id: string;
  name: string;
  email: string;
  subject: string;
  message: string;
  createdAt: string;
  read: boolean;
}

export interface Order {
  id: string;
  email: string;
  customerName: string;
  address: string;
  city: string;
  province: string;
  postalCode: string;
  items: CartItem[];
  total: number;
  status: OrderStatus;
  deliveryMethod: DeliveryMethod;
  eta?: string;
  trackingNumber?: string;
  carrier?: string;
  signature?: string; // Base64 signature
  deliveredAt?: string;
  createdAt: string;
  auditLog: { status: OrderStatus; timestamp: string }[];
}

export interface Analytics {
  views: number;
  totalSales: number;
  totalOrders: number;
  conversionRate: number;
}
