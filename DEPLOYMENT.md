# Falls Origin Coffee - Deployment Guide

## Prerequisites

1. **Google Cloud Project** with billing enabled
2. **PostgreSQL Database** (Cloud SQL or external provider like Supabase, Neon, etc.)
3. **Stripe Account** (test or production)
4. **Email Provider** (SendGrid, Mailgun, or AWS SES)
5. **Geocoding API** (Google Maps or OpenCage)

---

## Step 1: Set Up Database

### Option A: Cloud SQL (Recommended for production)

```bash
# Create Cloud SQL PostgreSQL instance
gcloud sql instances create falls-coffee-db \
  --database-version=POSTGRES_14 \
  --tier=db-f1-micro \
  --region=us-central1

# Create database
gcloud sql databases create falls_coffee --instance=falls-coffee-db

# Get connection string
gcloud sql instances describe falls-coffee-db --format="value(connectionName)"
```

### Option B: External Provider (Easier for testing)

Use Supabase, Neon, or any PostgreSQL provider and get the connection string.

### Run Migrations

```bash
# Connect to your database and run the migration
psql "YOUR_DATABASE_URL" < server/migrations/001_initial_schema.sql
```

---

## Step 2: Configure Environment Variables

Create a `.env` file in the `server/` directory (copy from `.env.example`):

```bash
cd server
cp .env.example .env
```

**Fill in all required values:**

1. **Database**: Set `DATABASE_URL`
2. **Stripe**: Add your Stripe keys
3. **Email**: Configure your email provider
4. **Admin**: Generate password hash:
   ```bash
   node -e "console.log(require('bcrypt').hashSync('YOUR_PASSWORD', 10))"
   ```
5. **Geocoding**: Add API key for Google Maps or OpenCage

---

## Step 3: Local Testing

```bash
# Install dependencies
cd server && npm install
cd .. && npm install

# Start backend (from server directory)
cd server && npm run dev

# Start frontend (from root directory)
npm run dev
```

Visit `http://localhost:5173` and test the full flow.

---

## Step 4: Deploy to Cloud Run

### A. Enable Required APIs

```bash
gcloud services enable run.googleapis.com
gcloud services enable cloudbuild.googleapis.com
gcloud services enable containerregistry.googleapis.com
```

### B. Set Environment Variables in Cloud Run

Create a file `env.yaml` with all your environment variables:

```yaml
NODE_ENV: production
DATABASE_URL: "postgresql://..."
STRIPE_SECRET_KEY: "sk_..."
STRIPE_PUBLISHABLE_KEY: "pk_..."
STRIPE_WEBHOOK_SECRET: "whsec_..."
EMAIL_PROVIDER: "sendgrid"
SENDGRID_API_KEY: "SG...."
EMAIL_FROM: "orders@fallsorigincoffee.com"
EMAIL_FROM_NAME: "Falls Origin Coffee"
ADMIN_EMAIL: "admin@fallsorigincoffee.com"
JWT_SECRET: "your-super-secret-jwt-key"
JWT_EXPIRATION: "24h"
ADMIN_PASSWORD_HASH: "$2b$10$..."
BASE_CITY: "Toronto, ON, Canada"
BASE_LATITUDE: "43.6532"
BASE_LONGITUDE: "-79.3832"
DELIVERY_RADIUS_KM: "200"
GEOCODING_PROVIDER: "google"
GOOGLE_MAPS_API_KEY: "AIza..."
FRONTEND_URL: "https://falls-origin-coffee-XXXXX.run.app"
REVIEW_EMAIL_DELAY_HOURS: "48"
RATE_LIMIT_WINDOW_MS: "900000"
RATE_LIMIT_MAX_REQUESTS: "100"
```

### C. Build and Deploy

```bash
# Set your project ID
export PROJECT_ID=your-gcp-project-id
gcloud config set project $PROJECT_ID

# Build the Docker image
gcloud builds submit --tag gcr.io/$PROJECT_ID/falls-origin-coffee

# Deploy to Cloud Run
gcloud run deploy falls-origin-coffee \
  --image gcr.io/$PROJECT_ID/falls-origin-coffee \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --env-vars-file env.yaml \
  --memory 512Mi \
  --cpu 1 \
  --max-instances 10

# Get the service URL
gcloud run services describe falls-origin-coffee \
  --platform managed \
  --region us-central1 \
  --format="value(status.url)"
```

---

## Step 5: Configure Stripe Webhook

1. Go to Stripe Dashboard → Developers → Webhooks
2. Add endpoint: `https://YOUR-CLOUD-RUN-URL/api/webhooks/stripe`
3. Select events:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
4. Copy the webhook signing secret and update `STRIPE_WEBHOOK_SECRET` in Cloud Run
5. Redeploy with updated env vars:
   ```bash
   gcloud run services update falls-origin-coffee \
     --env-vars-file env.yaml \
     --region us-central1
   ```

---

## Step 6: Set Up Custom Domain (Optional)

```bash
# Map custom domain
gcloud run domain-mappings create \
  --service falls-origin-coffee \
  --domain fallsorigincoffee.com \
  --region us-central1

# Follow instructions to update DNS records
```

---

## Step 7: Verify Deployment

### Test Checklist:

- [ ] Visit the URL and see the homepage
- [ ] Add product to cart
- [ ] Complete checkout with Stripe test card: `4242 4242 4242 4242`
- [ ] Verify order appears in admin dashboard
- [ ] Check that confirmation emails were sent
- [ ] Test order tracking
- [ ] Submit contact form
- [ ] Test admin login

### Monitor Logs:

```bash
gcloud run logs read falls-origin-coffee \
  --region us-central1 \
  --limit 50
```

---

## Troubleshooting

### Database Connection Issues
- Ensure `DATABASE_URL` is correct
- For Cloud SQL, you may need to enable Cloud SQL Admin API
- Check that database migrations were run

### Email Not Sending
- Verify email provider API keys
- Check Cloud Run logs for email errors
- Ensure sender email is verified with your provider

### Stripe Webhook Failures
- Verify webhook secret matches Stripe Dashboard
- Check that webhook URL is accessible
- Review Stripe Dashboard → Developers → Webhooks → Recent deliveries

### Build Failures
- Check `cloudbuild.yaml` syntax
- Ensure all dependencies are in `package.json`
- Review Cloud Build logs

---

## Production Checklist

- [ ] Use production Stripe keys
- [ ] Set strong `JWT_SECRET`
- [ ] Change admin password from default
- [ ] Configure email sender domain
- [ ] Set up database backups
- [ ] Enable Cloud Run logging and monitoring
- [ ] Configure custom domain with SSL
- [ ] Set up Cloud Armor for DDoS protection (optional)
- [ ] Configure Cloud CDN for static assets (optional)

---

## Maintenance

### Update Application

```bash
# Make code changes, then:
gcloud builds submit --tag gcr.io/$PROJECT_ID/falls-origin-coffee
gcloud run deploy falls-origin-coffee \
  --image gcr.io/$PROJECT_ID/falls-origin-coffee \
  --region us-central1
```

### Database Backups

```bash
# For Cloud SQL
gcloud sql backups create --instance=falls-coffee-db

# For external providers, use their backup tools
```

### View Metrics

```bash
# Cloud Run metrics
gcloud run services describe falls-origin-coffee \
  --region us-central1 \
  --format="value(status.url)"

# Then visit Google Cloud Console → Cloud Run → falls-origin-coffee → Metrics
```

---

## Support

For issues, check:
1. Cloud Run logs
2. Database connection
3. Stripe webhook deliveries
4. Email provider logs
