# LagunaWISE Ideology Division — InfinityFree Hosting Deployment Guide

This edition of **LagunaWISE — Ideology Division** is pre-configured specifically for **InfinityFree (free web hosting)** and standard cPanel/VistaPanel environments.

---

## What Was Pre-Configured for InfinityFree:

1. **One-Click Database Setup (`database/infinityfree_setup.sql`)**:
   - Removed `CREATE DATABASE` and `USE` statements that cause `#1044 Access Denied` errors in InfinityFree phpMyAdmin.
   - Combines the complete schema and clean seed taxonomy (Roles, Categories, Website Settings) in a single file.
2. **Dynamic Base URL (`config/app.php`)**:
   - Automatically adapts whether uploaded to the root `/htdocs/` (e.g., `https://yourdomain.infinityfreeapp.com/`) or a subfolder.
   - Enhanced HTTPS & reverse proxy cookie detection.
3. **InfinityFree-Ready `.htaccess`**:
   - `RewriteBase /` set for root `/htdocs/` installation.
   - Fallback notes if `Options -Indexes` causes a 500 error on certain server clusters.
4. **Visual Diagnostic Assistant (`config/database.php`)**:
   - If database credentials are not configured or connection fails, the application displays a friendly setup checklist instead of a generic white screen or raw PHP error.

---

## Step-by-Step Deployment Instructions

### Step 1: Create Your Hosting Account & Domain on InfinityFree
1. Register/log in to [InfinityFree](https://dash.infinityfree.com).
2. Click **Create Account**.
3. Choose a free subdomain (e.g., `lagunawise-ideology.infinityfreeapp.com`) or enter your custom domain.
4. Complete account creation and wait a few minutes for the account to become **Active**.

---

### Step 2: Create Your MySQL Database
1. In the InfinityFree Client Area, click on your hosting account.
2. Click **Control Panel** (vPanel).
3. Under the **Databases** section, click **MySQL Databases**.
4. In the "Create New Database" box, enter a name (e.g., `ideology`) and click **Create Database**.
5. Note down your full database details from the table:
   - **MySQL Database Name**: e.g. `if0_12345678_ideology`
   - **MySQL Host Name**: e.g. `sql100.infinityfree.com` or `sql202.epizy.com` *(NOTE: Never use "localhost")*
   - **MySQL User Name**: e.g. `if0_12345678`
   - **MySQL Password**: Your vPanel account password (can be viewed/copied from the InfinityFree Client Area under Account Details).

---

### Step 3: Import Database Schema & Seeds in phpMyAdmin
1. In the InfinityFree Control Panel, click **phpMyAdmin**.
2. Click **Connect** next to your newly created database.
3. Inside phpMyAdmin, click the **Import** tab at the top.
4. Click **Choose File** and select:
   ```
   database/infinityfree_setup.sql
   ```
5. Click **Go** at the bottom.
6. You will see a success banner indicating that all tables, roles, and default categories were imported successfully!

---

### Step 4: Configure Database Credentials in `config/database.php`
*(Already pre-configured with your credentials!)*

```php
// =========================================================================
// INFINITYFREE DATABASE CREDENTIALS:
// =========================================================================
private const DB_HOST = 'sql101.infinityfree.com'; // InfinityFree MySQL Hostname
private const DB_PORT = '3306';                    // Default MySQL port
private const DB_NAME = 'if0_43456590_ideology';   // Created Database Name
private const DB_USER = 'if0_43456590';            // InfinityFree MySQL Username
private const DB_PASS = 'Goq4bpkrc1';              // InfinityFree Account Password
// =========================================================================
```

---

### Step 5: Upload Files to InfinityFree

#### Option A: Using FileZilla / FTP (Recommended)
1. In your InfinityFree Client Area, find your **FTP Details** (FTP Host, FTP Username, FTP Password).
2. Connect using FileZilla or your preferred FTP client.
3. Open the **`htdocs`** folder on the remote server.
4. Upload all files and folders from this folder (`LW-ideology-infinityfree`) directly into `htdocs/`.
   *(Ensure `index.php` is located directly in `htdocs/index.php`)*.

#### Option B: Using the Ready-Made ZIP via File Manager
1. We have provided a ready-to-upload archive: `LW-ideology-infinityfree.zip`.
2. In InfinityFree Control Panel, open **Online File Manager** (Monsta FTP).
3. Navigate into `htdocs/`.
4. Upload the files or unzip the archive.

---

### Step 6: Initialize the First Administrator Account
LagunaWISE features a **Zero-Setup Security Bootstrap**:
1. Open your website in your browser (e.g. `https://lagunawise-ideology.infinityfreeapp.com/`).
2. Click **Login** in the top navigation or visit `/auth/register.php`.
3. Fill out the registration form with your name, email, and a secure password.
4. Because this is the first account registered in the clean database, **the system automatically assigns you the Administrator role (`roles.name = 'administrator'`) with full access to `/admin/`!**
5. Subsequent public registrations will be standard contributors/readers.

---

## Troubleshooting & FAQs

### Q1: I get a "500 Internal Server Error" when visiting the site.
- **Cause**: Some InfinityFree servers disallow the `Options -Indexes` directive in `.htaccess`.
- **Solution**: Open `.htaccess` in your file manager and change line 7:
  ```apache
  # Options -Indexes
  ```
  (Add a `#` at the beginning to comment it out and save).

### Q2: I see "Database Connection Failed".
- **Cause**: The host, database name, or password in `config/database.php` does not match.
- **Checklist**:
  - Did you use `localhost`? **InfinityFree does NOT support `localhost`**. You must use `sqlXXX.infinityfree.com` or `sqlXXX.epizy.com`.
  - Is your database name prefixed with your account ID? E.g., `if0_12345678_ideology`.
  - Did you use your vPanel password (not your email login password)?

### Q3: Clean URLs (e.g. `/articles/slug-name`) give a 404 error.
- If you uploaded files into a subfolder rather than the root `htdocs/` (e.g. `htdocs/ideology/`), open `.htaccess` and change:
  ```apache
  RewriteBase /ideology/
  ```

---

## Folder Structure Summary

```
/htdocs/
  ├── actions/                 # Form & API handlers (CSRF, bookmarks, articles)
  ├── admin/                   # Administrative editorial & moderation panel
  ├── assets/                  # CSS/JS, logos, avatars, upload storage
  ├── auth/                    # Login, self-bootstrap registration, forgot password
  ├── cli/                     # CLI scripts (for local development)
  ├── config/                  # App configuration, database connection, security
  │   ├── app.php              # Timezone, session, base URL auto-detection
  │   ├── database.php         # InfinityFree PDO database handler
  │   └── security.php         # CSRF token validator & XSS defense
  ├── database/                # SQL setup
  │   └── infinityfree_setup.sql # 1-click schema + seeds for phpMyAdmin
  ├── includes/                # Shared layout header, footer, auth, helpers
  ├── pages/                   # Public views (About, Articles, Topics, Contact)
  ├── .htaccess                # Apache routing & security rules
  ├── index.php                # Main landing page
  ├── robots.txt               # Search engine directives
  └── DEPLOY_TO_INFINITYFREE.md# This guide
```
