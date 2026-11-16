# LagunaWISE Ideology Division — Installation & Local Deployment Guide

This guide details the complete local deployment process on **XAMPP (Apache + MariaDB/MySQL + PHP 8.1+)**.

---

## 1. System Requirements
* **Web Server**: Apache 2.4+ (Included with XAMPP)
* **Database**: MySQL 8.0+ or MariaDB 10.4+
* **PHP**: PHP 8.1+ or PHP 8.2+ with `pdo_mysql`, `mbstring`, `fileinfo`, `session` extensions enabled
* **Environment**: Local XAMPP directory: `C:\xampp\htdocs\LW-ideology\` (or `C:\xampp\htdocs\lagunawise-ideology\`)

---

## 2. Directory Placement
Ensure the project files reside inside the Apache `htdocs` directory:
```
C:\xampp\htdocs\LW-ideology\
```
A directory junction or folder alias may be created for `lagunawise-ideology`:
```powershell
cmd /c mklink /J "C:\xampp\htdocs\lagunawise-ideology" "C:\xampp\htdocs\LW-ideology"
```
Both URLs will serve the exact same platform:
* `http://localhost/LW-ideology/`
* `http://localhost/lagunawise-ideology/`

---

## 3. Starting Services in XAMPP
1. Open the **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
2. Click **Start** adjacent to **Apache**. Verify that the module turns green and port 80 / 443 displays.
3. Click **Start** adjacent to **MySQL**. Verify that the module turns green and port 3306 displays.

*Alternatively, from PowerShell / Command Line:*
```powershell
# Start MariaDB/MySQL
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini","--standalone"

# Start Apache HTTPD
Start-Process "C:\xampp\apache\bin\httpd.exe" -ArgumentList "-d C:/xampp/apache"
```

---

## 4. Database Setup & SQL Migration

### Option A: Using phpMyAdmin (Web GUI)
1. Open your browser and navigate to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click **New** in the left sidebar.
3. Set the database name to:
   ```
   lagunawise_ideology
   ```
4. Choose collation: `utf8mb4_unicode_ci` and click **Create**.
5. Select `lagunawise_ideology` from the left sidebar, click the **Import** tab.
6. Choose `database/schema.sql` from the project directory and click **Import**.
7. In the same manner, import `database/seed.sql` to populate the 4 system roles, 8 taxonomy categories, and 12 baseline website configuration settings.
   > **Note**: Per the strict project policy, `seed.sql` does NOT insert fake users or fabricated articles. The database is initialized in a pristine, zero-dummy-record state.

### Option B: Using the Command Line / PowerShell
Run the following commands in PowerShell from the project root:
```powershell
# Import Schema
Get-Content "database\schema.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root

# Import Seed Data
Get-Content "database\seed.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root
```

---

## 5. Database Connection Configuration
Review `config/database.php`. By default, standard fresh local XAMPP installations use `root` with an empty password:
```php
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'lagunawise_ideology';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
```
If your local MySQL environment requires a custom password or alternate port, specify environment variables or adjust the fallback parameters in `config/database.php`.

---

## 6. Accessing the Platform

### Public Website
Open your browser and navigate to:
```
http://localhost/LW-ideology/
```
Or:
```
http://localhost/lagunawise-ideology/
```

### Automated QA Test Suite
Verify database integrity, empty states, security filters, and role permissions:
```
http://localhost/LW-ideology/tests/index.php
```
Or via CLI:
```powershell
C:\xampp\php\php.exe tests\index.php
```
All 22 automated unit, integration, and security tests should report **PASS (100%)**.

---

## 7. Account Setup (Strict No-Fake-Content Policy)

In accordance with strict project guidelines, the database is intentionally initialized without fake demo accounts or dummy articles.

### Creating the First Administrator Account
You have two straightforward methods to create your administrative account:

#### Method 1: Web Self-Bootstrap (Recommended)
1. Navigate to `http://localhost/LW-ideology/auth/register.php`.
2. Fill out the registration form (Name, Username, Email, Password, Confirm Password, and accept Terms).
3. Because the user table is initially empty, the application **automatically assigns the Administrator role (`id: 1`)** to this very first registration.
4. You are immediately logged in and redirected to the **Administrative CMS** (`admin/index.php`).

#### Method 2: Command Line Interface (CLI)
You can create an administrator at any time using the provided CLI tool:
```powershell
C:\xampp\php\php.exe cli/create-admin.php --name="System Administrator" --username="admin" --email="admin@lagunawise.org" --password="YourSecurePassword123!"
```
If the username or email already exists, this command safely elevates the account to the **Administrator** role.

### Creating Contributor Accounts
All subsequent public registrations via `auth/register.php` are automatically granted the **Contributor role (`id: 3`)**.
* Contributors are immediately redirected to their **Contributor Dashboard** (`pages/dashboard.php`).
* Contributors have full permissions to compose articles, save drafts, preview manuscripts, and submit them for review to editors.

---

## 8. Apache `mod_rewrite` & Clean URLs
The included `.htaccess` file enables clean URL rewrites:
* `/articles/understanding-misinformation` &rarr; `pages/article.php?slug=understanding-misinformation`
* `/topics/media-literacy` &rarr; `pages/topic.php?slug=media-literacy`

If your local Apache configuration does not have `mod_rewrite` enabled, the entire website features automatic fallback query-based URLs (e.g. `pages/article.php?slug=...`) with no loss in functionality.
