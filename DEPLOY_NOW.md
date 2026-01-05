# 🚀 deployment Instructions (Manual)

Since the `gcloud` command-line tool is not detected in your terminal, you can easily deploy via the Google Cloud Console.

## Step 1: Prepare Your Secrets
**CRITICAL:** You must have your real credentials ready. The app **will not start** without them.

Open `env.yaml` in this folder and ensure you have values for:
- `DATABASE_URL` (Connection string from your cloud database)
- `STRIPE_SECRET_KEY`
- `ADMIN_PASSWORD_HASH`

## Step 2: Get Your Link (Google Cloud Console)

1. **Go to Cloud Run:**
   - Visit: [https://console.cloud.google.com/run/create](https://console.cloud.google.com/run/create)

2. **Configure Service:**
   - **Service Name:** `falls-origin-coffee`
   - **Region:** `us-central1` (or your preferred region)
   - **Source:** Select **"Deploy one revision from an existing container image"** (if you have built it) OR **"Continuously deploy new revisions from a source repository"**.
     - *Recommended:* Choose **"Continuously deploy from a source repository"** -> **"Set up with Cloud Build"**.
     - Connect this GitHub repository.

3. **Authentication:**
   - Check **"Allow unauthenticated invocations"** (This makes your website public).

4. **Environment Variables (The Most Important Part):**
   - Expand the **"Container, Networking, Security"** section.
   - Click the **"Variables & Secrets"** tab.
   - Click **"Add Variable"** for EACH item in your `env.yaml` file.
   - *Example:*
     - Name: `DATABASE_URL`
     - Value: `postgresql://...`

5. **Deploy:**
   - Click **"Create"**.

## Step 3: Success!
Google will take 2-3 minutes to build and deploy. Once finished, you will see a green checkmark and your URL at the top:

**URL Example:** `https://falls-origin-coffee-45235-uc.a.run.app`

---

## 🛑 Troubleshooting

**"Application failed to start"**
- Check the **Logs** tab in Cloud Run.
- 99% of the time, this is because a required environment variable (like `DATABASE_URL`) is missing or incorrect.
