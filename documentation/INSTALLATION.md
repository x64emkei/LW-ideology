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
7. In the same manner, import `database/seed.sql` to populate demonstration articles, taxonomy categories, and seed users.

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
Verify database integrity, security filters, and role permissions:
```
http://localhost/LW-ideology/tests/index.php
```

---

## 7. Demonstration Accounts & First-Time Login

| Role | Username | Initial Password | Description |
| :--- | :--- | :--- | :--- |
| **Super Administrator** | `admin` | `admin123!` | Complete system governance, role management, settings. |
| **Editor** | `elena.vance` | `editor123!` | Peer review queue, manuscript edits, publishing approvals. |
| **Writer / Fellow** | `marcus.reyes` | `writer123!` | Draft creation, revision submission, author profile. |
| **Writer / Specialist** | `sophia.delacruz` | `writer123!` | Draft creation, community research submissions. |
| **Public Reader** | `juan.reader` | `reader123!` | Bookmarks, discussion comments, reading lists. |

### Mandatory First-Login Password Change
As mandated by security standards, the initial administrator account has the `must_change_password` flag set. Upon first logging into `admin`, the application will prompt the administrator to enter a new secure password in `admin/profile.php`. Once changed, the flag is cleared.

---

## 8. Apache `mod_rewrite` & Clean URLs
The included `.htaccess` file enables clean URL rewrites:
* `/articles/understanding-misinformation` &rarr; `pages/article.php?slug=understanding-misinformation`
* `/topics/media-literacy` &rarr; `pages/topic.php?slug=media-literacy`

If your local Apache configuration does not have `mod_rewrite` enabled, the entire website features automatic fallback query-based URLs (e.g. `pages/article.php?slug=...`) with no loss in functionality.
