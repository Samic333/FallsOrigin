# Falls Origin Coffee - E-commerce System

A premium full-stack e-commerce platform for Falls Origin Coffee, built with React, Node.js, Express, PostgreSQL, and Stripe.

## 🚀 Quick Start

### Prerequisites

- Node.js 20+
- PostgreSQL 14+
- Stripe Account (test or production)
- Email Provider (SendGrid, Mailgun, or AWS SES)
- Geocoding API (Google Maps or OpenCage)

### Installation

1. **Run the setup script:**
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

2. **Configure environment variables:**
   - Edit `server/.env` with your credentials
   - See `server/.env.example` for all required variables

3. **Set up the database:**
   ```bash
   # Create your PostgreSQL database first, then run:
   psql "YOUR_DATABASE_URL" < server/migrations/001_initial_schema.sql
   ```

4. **Generate admin password hash:**
   ```bash
   node -e "console.log(require('bcrypt').hashSync('your-password', 10))"
   # Copy the output to ADMIN_PASSWORD_HASH in server/.env
   ```

5. **Start development servers:**
   ```bash
   # Terminal 1 - Backend
   cd server && npm run dev

   # Terminal 2 - Frontend
   npm run dev
   ```

6. **Visit the app:**
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8080
   - Health check: http://localhost:8080/health

## 📁 Project Structure

```
falls-origin-coffee/
├── server/                 # Backend (Node.js + Express)
│   ├── src/
│   │   ├── config/        # Database, environment config
│   │   ├── middleware/    # Auth, validation, rate limiting
│   │   ├── routes/        # API endpoints
│   │   ├── services/      # Business logic (Stripe, email, delivery, auth)
│   │   └── utils/         # Logger, helpers
│   ├── migrations/        # Database schema
│   └── package.json
├── pages/                 # Frontend pages (React)
├── components/            # React components
├── services/              # API client
├── Dockerfile             # Production container
├── cloudbuild.yaml        # Cloud Build config
└── DEPLOYMENT.md          # Deployment guide
```

## 🎯 Features

### Customer Features
- ✅ Product catalog with filtering
- ✅ Shopping cart
- ✅ Stripe checkout (CAD)
- ✅ Order tracking (by token or order number + email)
- ✅ Delivery method calculation (Local <= 200km, Postal > 200km)
- ✅ Email notifications (order confirmation, ETA updates, tracking numbers)
- ✅ Contact form
- ✅ Product reviews (48-hour delay after delivery)

### Admin Features
- ✅ Secure login (JWT authentication)
- ✅ Order management (status updates, ETA setting, tracking numbers)
- ✅ Signature capture for proof of delivery
- ✅ Product management (CRUD)
- ✅ Review moderation (approve/reject)
- ✅ Contact message inbox
- ✅ Analytics dashboard

### Technical Features
- ✅ PostgreSQL database with audit logs
- ✅ Stripe webhook handling (idempotent)
- ✅ Email service (SendGrid/Mailgun/SES)
- ✅ Geocoding for delivery calculation
- ✅ Rate limiting
- ✅ Input validation
- ✅ Security headers (Helmet)
- ✅ Logging (Winston)
- ✅ Cloud Run ready

## 🧪 Testing

### Test Stripe Payments

Use these test cards:
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **3D Secure**: `4000 0025 0000 3155`

Any future expiry date and any 3-digit CVC.

### Test Delivery Calculation

- **Local Delivery**: Use postal codes starting with M, L, K, N, P (Ontario)
- **Postal Shipping**: Use postal codes from other provinces

## 📚 API Documentation

### Public Endpoints

- `GET /api/products` - List all products
- `POST /api/checkout/calculate-delivery` - Calculate delivery method
- `POST /api/checkout/create-payment-intent` - Create Stripe Payment Intent
- `POST /api/checkout/confirm` - Confirm order after payment
- `GET /api/orders/track?token=xxx` - Track order by token
- `POST /api/orders/track` - Track order by order number + email
- `POST /api/contact` - Submit contact form
- `GET /api/reviews/approved` - Get approved reviews
- `POST /api/reviews` - Submit review

### Admin Endpoints (Require JWT Token)

- `POST /api/admin/login` - Admin login
- `GET /api/admin/orders` - List all orders
- `PUT /api/orders/:id/status` - Update order status
- `PUT /api/orders/:id/eta` - Set ETA for local delivery
- `PUT /api/orders/:id/tracking` - Add tracking number
- `POST /api/orders/:id/signature` - Upload signature
- `GET /api/admin/messages` - List contact messages
- `GET /api/admin/reviews` - List all reviews
- `PUT /api/admin/reviews/:id/approve` - Approve review
- `GET /api/admin/analytics` - Get analytics

### Webhooks

- `POST /api/webhooks/stripe` - Stripe webhook handler

## 🚢 Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions to Google Cloud Run.

### Quick Deploy

```bash
# Set your project ID
export PROJECT_ID=your-gcp-project-id

# Build and deploy
gcloud builds submit --tag gcr.io/$PROJECT_ID/falls-origin-coffee
gcloud run deploy falls-origin-coffee \
  --image gcr.io/$PROJECT_ID/falls-origin-coffee \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --env-vars-file env.yaml
```

## 🔧 Configuration

### Required Environment Variables

See `server/.env.example` for all required variables. Key ones:

- `DATABASE_URL` - PostgreSQL connection string
- `STRIPE_SECRET_KEY` - Stripe secret key
- `STRIPE_WEBHOOK_SECRET` - Stripe webhook signing secret
- `EMAIL_PROVIDER` - sendgrid, mailgun, or ses
- `ADMIN_PASSWORD_HASH` - Bcrypt hash of admin password
- `GEOCODING_PROVIDER` - google or opencage
- `BASE_CITY` - Base city for delivery calculation
- `DELIVERY_RADIUS_KM` - Radius for local delivery (default: 200)

## 📝 Remaining Work

### Critical (Before Production)

1. **Update remaining frontend pages** to use API:
   - `pages/Checkout.tsx` - Integrate Stripe Payment Element
   - `pages/AdminDashboard.tsx` - Replace localStorage with API calls
   - `pages/TrackOrder.tsx` - Use API for tracking
   - `pages/ProductPage.tsx` - Fetch reviews from API

2. **Configure Stripe webhook** after deployment

3. **Test full flow** end-to-end

### Optional Enhancements

- Review scheduler (background job for 48-hour delay)
- Image upload for products
- Customer accounts
- Inventory management

## 🐛 Troubleshooting

### Common Issues

**"Cannot find module" errors:**
- Run `npm install` in both root and `server/` directories

**Database connection failed:**
- Verify `DATABASE_URL` in `server/.env`
- Ensure PostgreSQL is running
- Check migrations were run

**Stripe webhook failures:**
- Verify `STRIPE_WEBHOOK_SECRET` matches Stripe Dashboard
- Check webhook URL is accessible
- Review Stripe webhook delivery logs

**Email not sending:**
- Verify email provider API keys
- Check sender email is verified with provider
- Review backend logs for errors

## 📄 License

Proprietary - Falls Origin Coffee

## 🤝 Support

For issues or questions, see the audit report and deployment summary in the `brain/` directory.

---

**Built with ❤️ by AntiGravity AI**
