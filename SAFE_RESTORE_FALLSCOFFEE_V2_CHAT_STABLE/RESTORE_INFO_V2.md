# Falls Origin Coffee - V2 Chat Stable Backup

## Status: OPERATIONAL
**Point of Capture**: March 24, 2026
**Key Features**:
- Threaded Chat System (Unlimited replies)
- Fix: Admin Readability & Standard Casing
- Fix: Product Image rendering from DB
- Fix: Namecheap Email alignment (@fallscoffee.ca)

## How to Restore:
1. Copy all files from this directory to your web root.
2. Import `schema_v2_stable.sql` via phpMyAdmin.
3. Verify `includes/config.php` has correct DB credentials for the destination.
4. Verify `ADMIN_EMAIL` is set to `admin@fallscoffee.ca` for mail dispatch.

## Critical Settings:
- ADMIN_EMAIL: admin@fallscoffee.ca (Matches verified Namecheap sender)
- DB_CHARSET: utf8mb4
