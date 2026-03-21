# Deployment Guide: Falls Origin Coffee

Follow these steps to deploy the migrated project to your Namecheap shared hosting account.

## Prerequisites
1. Namecheap Shared Hosting account with cPanel Access.
2. PHP 8.0 or higher.
3. MySQL 8.0 or higher.
4. Stripe Account (for API keys).

## Step 1: Database Setup
1. Log in to your Namecheap cPanel.
2. Navigate to **MySQL® Databases**.
3. Create a new database named `falls_origin_db`.
4. Create a new user (e.g., `falls_admin`) and assign it to the database with all privileges.
5. Go to **phpMyAdmin**, select your database, and import the `database/schema.sql` file.

## Step 2: Configuration
1. Open `public_html/includes/config.php`.
2. Update the `DB_PASS`, `DB_USER`, and `DB_NAME` constants with your cPanel database credentials.
3. Update `STRIPE_SECRET_KEY` and `STRIPE_PUBLISHABLE_KEY` with your Stripe API keys.
4. Set the `ADMIN_EMAIL` to your desired notification address.

## Step 3: File Upload
1. Use the cPanel **File Manager** or an FTP client (like FileZilla).
2. Upload the entire contents of the `public_html/` folder to your site's home directory (usually `/home/username/public_html/` or a subdomain folder).

## Step 4: Verification
1. Navigate to your website URL in a browser.
2. Verify the Home, Shop, and Product pages load correctly.
3. Attempt a test purchase using a Stripe test card.
4. Log in to the admin panel at `yourdomain.com/admin/login.php`.
   - **Default Credentials**: `admin` / `password123`
   - *Recommendation: Change the password immediately in the database or via the admin interface.*

## Security Recommendations
- Add an `.htaccess` rule to deny direct access to the `config/` directory:
```apache
<Directory "config">
    Order deny,allow
    Deny from all
</Directory>
```
- Ensure `uploads/` directory has `755` permissions for image storage.
