import axios, { AxiosInstance, AxiosError } from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';

class APIClient {
    private client: AxiosInstance;

    constructor() {
        this.client = axios.create({
            baseURL: API_BASE_URL,
            headers: {
                'Content-Type': 'application/json',
            },
        });

        // Add auth token to requests
        this.client.interceptors.request.use((config) => {
            const token = localStorage.getItem('admin_token');
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        });

        // Handle errors
        this.client.interceptors.response.use(
            (response) => response,
            (error: AxiosError) => {
                if (error.response?.status === 401) {
                    localStorage.removeItem('admin_token');
                    window.location.hash = '/admin';
                }
                return Promise.reject(error);
            }
        );
    }

    // Products
    async getProducts() {
        const { data } = await this.client.get('/products');
        return data;
    }

    async createProduct(product: any) {
        const { data } = await this.client.post('/products', product);
        return data;
    }

    async updateProduct(id: string, product: any) {
        const { data } = await this.client.put(`/products/${id}`, product);
        return data;
    }

    async deleteProduct(id: string) {
        await this.client.delete(`/products/${id}`);
    }

    // Checkout
    async calculateDelivery(address: any) {
        const { data } = await this.client.post('/checkout/calculate-delivery', address);
        return data;
    }

    async createPaymentIntent(params: any) {
        const { data } = await this.client.post('/checkout/create-payment-intent', params);
        return data;
    }

    async confirmOrder(params: any) {
        const { data } = await this.client.post('/checkout/confirm', params);
        return data;
    }

    // Orders
    async trackOrderByToken(token: string) {
        const { data } = await this.client.get(`/orders/track?token=${token}`);
        return data;
    }

    async trackOrderByEmail(orderId: string, email: string) {
        const { data } = await this.client.post('/orders/track', { orderId, email });
        return data;
    }

    async updateOrderStatus(orderId: string, status: string) {
        const { data } = await this.client.put(`/orders/${orderId}/status`, { status });
        return data;
    }

    async setOrderETA(orderId: string, eta: string) {
        const { data } = await this.client.put(`/orders/${orderId}/eta`, { eta });
        return data;
    }

    async setOrderTracking(orderId: string, trackingNumber: string, carrier: string) {
        const { data } = await this.client.put(`/orders/${orderId}/tracking`, {
            trackingNumber,
            carrier,
        });
        return data;
    }

    async uploadSignature(orderId: string, signatureFile: File) {
        const formData = new FormData();
        formData.append('signature', signatureFile);
        const { data } = await this.client.post(`/orders/${orderId}/signature`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return data;
    }

    // Admin
    async adminLogin(username: string, password: string) {
        const { data } = await this.client.post('/admin/login', { username, password });
        return data;
    }

    async getAdminOrders() {
        const { data } = await this.client.get('/admin/orders');
        return data;
    }

    async getAdminMessages() {
        const { data } = await this.client.get('/admin/messages');
        return data;
    }

    async markMessageAsRead(id: number) {
        await this.client.put(`/admin/messages/${id}/read`);
    }

    async getAdminReviews() {
        const { data } = await this.client.get('/admin/reviews');
        return data;
    }

    async approveReview(id: number) {
        await this.client.put(`/admin/reviews/${id}/approve`);
    }

    async rejectReview(id: number) {
        await this.client.put(`/admin/reviews/${id}/reject`);
    }

    async getAnalytics() {
        const { data } = await this.client.get('/admin/analytics');
        return data;
    }

    // Contact
    async submitContactForm(params: any) {
        const { data } = await this.client.post('/contact', params);
        return data;
    }

    // Reviews
    async getApprovedReviews() {
        const { data } = await this.client.get('/reviews/approved');
        return data;
    }

    async submitReview(params: any) {
        const { data } = await this.client.post('/reviews', params);
        return data;
    }
}

export const api = new APIClient();
