# Falls Origin Coffee - Next Steps

## ✅ What's Been Completed

### Backend (100% Complete)
- ✅ Node.js/Express server with TypeScript
- ✅ PostgreSQL database schema (8 tables)
- ✅ Stripe payment integration
- ✅ Email service (6 email types)
- ✅ Delivery calculation with geocoding
- ✅ JWT authentication
- ✅ All API routes
- ✅ Security middleware
- ✅ Logging
- ✅ Dockerfile & Cloud Build config

### Frontend (Partial)
- ✅ UI design (Google AI Studio)
- ✅ API client created (`services/api.ts`)
- ✅ Shop page updated (API integration)
- ✅ Contact page updated (API integration)
- ⏳ Checkout page (needs Stripe integration)
- ⏳ Admin dashboard (needs API integration)
- ⏳ Track Order page (needs API integration)
- ⏳ Product page (needs review API integration)

---

## 🎯 Immediate Next Steps (2-4 hours)

### 1. Install Dependencies (5 minutes)

```bash
# Run the setup script
chmod +x setup.sh
./setup.sh

# Or manually:
npm install
cd server && npm install
```

### 2. Configure Environment Variables (15 minutes)

Edit `server/.env` with your credentials:

**Required Credentials:**
- PostgreSQL database URL
- Stripe keys (test or production)
- Email provider API keys (choose SendGrid, Mailgun, or SES)
- Geocoding API key (Google Maps or OpenCage)
- Admin password hash

**Generate admin password:**
```bash
node -e "console.log(require('bcrypt').hashSync('your-password', 10))"
```

### 3. Set Up Database (10 minutes)

```bash
# Create database and run migrations
psql "YOUR_DATABASE_URL" < server/migrations/001_initial_schema.sql
```

### 4. Update Remaining Frontend Pages (1-2 hours)

**Priority Order:**

#### A. Checkout Page (`pages/Checkout.tsx`)
- Replace mock payment with Stripe Payment Element
- Use `api.calculateDelivery()` for delivery method
- Use `api.createPaymentIntent()` to get client secret
- Use `api.confirmOrder()` after successful payment
- Handle errors gracefully

#### B. Track Order Page (`pages/TrackOrder.tsx`)
- Use `api.trackOrderByToken()` or `api.trackOrderByEmail()`
- Display order details from API response
- Show audit log timeline

#### C. Admin Dashboard (`pages/AdminDashboard.tsx`)
- Replace `StorageService` with `api` calls
- Use `api.adminLogin()` for authentication
- Use `api.getAdminOrders()`, `api.getAdminMessages()`, etc.
- Store JWT token in localStorage
- Add signature upload functionality

#### D. Product Page (`pages/ProductPage.tsx`)
- Use `api.getApprovedReviews()` to fetch reviews
- Display approved reviews only

### 5. Test Locally (30 minutes)

```bash
# Terminal 1 - Backend
cd server && npm run dev

# Terminal 2 - Frontend
npm run dev

# Visit http://localhost:5173
```

**Test Checklist:**
- [ ] Browse products
- [ ] Add to cart
- [ ] Complete checkout with test card: `4242 4242 4242 4242`
- [ ] Verify order confirmation email
- [ ] Track order
- [ ] Submit contact form
- [ ] Login to admin
- [ ] View orders in admin
- [ ] Update order status

### 6. Deploy to Cloud Run (30 minutes)

Follow instructions in `DEPLOYMENT.md`:

```bash
# Build and deploy
gcloud builds submit --tag gcr.io/YOUR_PROJECT_ID/falls-origin-coffee
gcloud run deploy falls-origin-coffee \
  --image gcr.io/YOUR_PROJECT_ID/falls-origin-coffee \
  --region us-central1 \
  --env-vars-file env.yaml
```

### 7. Configure Stripe Webhook (5 minutes)

1. Get your Cloud Run URL
2. Add webhook in Stripe Dashboard: `https://YOUR-URL/api/webhooks/stripe`
3. Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`
4. Copy webhook secret to `STRIPE_WEBHOOK_SECRET`
5. Redeploy

---

## 📋 Detailed Frontend Update Guide

### Checkout Page Example

```typescript
// pages/Checkout.tsx
import { api } from '../services/api';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement, useStripe, useElements } from '@stripe/react-stripe-js';

// 1. Calculate delivery when postal code changes
const handlePostalCodeChange = async (postalCode: string) => {
  const result = await api.calculateDelivery({
    address, city, province, postalCode
  });
  setDeliveryMethod(result.deliveryMethod);
  setShippingCost(result.shippingCost);
};

// 2. Create Payment Intent when ready to pay
const { clientSecret, orderId } = await api.createPaymentIntent({
  amount: total,
  email,
  customerName
});

// 3. Confirm order after successful payment
const result = await stripe.confirmPayment({
  elements,
  confirmParams: { return_url: window.location.origin }
});

if (result.paymentIntent?.status === 'succeeded') {
  await api.confirmOrder({
    paymentIntentId: result.paymentIntent.id,
    email, customerName, address, city, province, postalCode,
    items, total, deliveryMethod
  });
}
```

### Admin Dashboard Example

```typescript
// pages/AdminDashboard.tsx
import { api } from '../services/api';

// Login
const handleLogin = async (username: string, password: string) => {
  const { token } = await api.adminLogin(username, password);
  localStorage.setItem('admin_token', token);
  setIsLoggedIn(true);
};

// Fetch data
useEffect(() => {
  const fetchData = async () => {
    const [orders, messages, reviews] = await Promise.all([
      api.getAdminOrders(),
      api.getAdminMessages(),
      api.getAdminReviews()
    ]);
    setOrders(orders);
    setMessages(messages);
    setReviews(reviews);
  };
  fetchData();
}, []);

// Update order status
const updateStatus = async (orderId: string, status: string) => {
  await api.updateOrderStatus(orderId, status);
  refreshData();
};
```

---

## 🔍 Testing Checklist

### Functional Tests

- [ ] **Checkout Flow**
  - Add product to cart
  - Enter delivery address
  - See correct delivery method (Local/Postal)
  - Complete payment with test card
  - Receive confirmation email
  - Order appears in admin dashboard

- [ ] **Failed Payment**
  - Use decline test card: `4000 0000 0000 0002`
  - Verify no order is created
  - Verify no emails are sent

- [ ] **Local Delivery (<=200km)**
  - Use Toronto postal code: M5V 3A8
  - Verify "Local Delivery" method
  - Admin sets ETA
  - Customer receives ETA email
  - Track order shows ETA

- [ ] **Postal Shipping (>200km)**
  - Use Ottawa postal code: K1A 0B1
  - Verify "Postal Shipping" method
  - Admin adds tracking number
  - Customer receives tracking email
  - Track order shows tracking link

- [ ] **Contact Form**
  - Submit message
  - Verify success message
  - Check admin inbox
  - Verify admin email received

- [ ] **Reviews**
  - Mark order as Delivered
  - Submit review
  - Verify "Pending" status
  - Admin approves review
  - Review appears on product page

- [ ] **Admin Features**
  - Login with credentials
  - View all orders
  - Update order status
  - Set ETA
  - Add tracking number
  - Upload signature (for local delivery)
  - Approve/reject reviews
  - View messages

### Security Tests

- [ ] Admin routes blocked without token
- [ ] Invalid token returns 401
- [ ] Rate limiting works on contact form
- [ ] Webhook signature verification works
- [ ] SQL injection prevention (try `'; DROP TABLE orders; --`)
- [ ] XSS prevention (try `<script>alert('xss')</script>`)

---

## 🚨 Common Issues & Solutions

### Issue: "Cannot find module 'axios'"
**Solution:** Run `npm install` in the root directory

### Issue: "Database connection failed"
**Solution:** 
1. Check `DATABASE_URL` in `server/.env`
2. Ensure PostgreSQL is running
3. Verify migrations were run

### Issue: "Stripe webhook signature verification failed"
**Solution:**
1. Verify `STRIPE_WEBHOOK_SECRET` matches Stripe Dashboard
2. Ensure webhook URL is correct
3. Check Stripe webhook delivery logs

### Issue: "Email not sending"
**Solution:**
1. Verify email provider API keys
2. Check sender email is verified
3. Review backend logs: `cd server && npm run dev`

---

## 📞 Need Help?

1. Check the **Audit Report** for detailed implementation info
2. Check the **Deployment Summary** for Cloud Run setup
3. Review backend logs for errors
4. Check Stripe Dashboard for payment issues
5. Verify all environment variables are set correctly

---

## 🎉 You're Almost There!

The heavy lifting is done. Just need to:
1. Install dependencies
2. Configure credentials
3. Update 4 frontend pages
4. Test locally
5. Deploy to Cloud Run

**Estimated time:** 2-4 hours

Good luck! 🚀
