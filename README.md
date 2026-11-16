# LagunaWISE — Ideology Division Website
### Digital Publication, Editorial, and Civic Advocacy Platform

![LagunaWISE Ideology Division](assets/images/lagunawise-banner.svg)

---

## 🏛️ Project Identity & Overview
* **Organization**: LagunaWISE
* **Division**: Ideology Division
* **Headquarters**: Barangay Barandal, Calamba City, Laguna 4027, Philippines
* **Contact & Partnerships**: [lagunawise.ph@gmail.com](mailto:lagunawise.ph@gmail.com)
* **Core Philosophy**: *"Understanding Ideas. Challenging Perspectives. Empowering Minds."*

The **Ideology Division** serves as the intellectual and educational division of LagunaWISE. It provides an accessible digital publication platform where readers, students, educators, and community advocates can explore peer-reviewed articles, examine multifaceted social issues, deconstruct online misinformation, and cultivate responsible digital citizenship.

---

## 🚀 Key Platform Features

### 1. Distinctive Editorial Design System & Responsive Grids
* **Brand Identity**: Authentic LagunaWISE circular emblem with the Philippine map silhouette and official LagunaWISE wordmark.
* **Palette Tokens**: Deep Primary Navy (`#061638`), Secondary Navy (`#0B2252`), LagunaWISE Blue (`#1557A6`), Tech Blue (`#2799E8`), Ice Blue (`#DCEEFF`), Surface (`#F4F7FB`), and Silver (`#C5CEDB`).
* **Typography Pairing**: Plus Jakarta Sans for structural headings and UI controls; Source Serif 4 for an immersive, longform editorial reading experience.
* **Two-Column Responsive Layouts (~66% / 34%)**:
  - Homepage hero section (mission & core pillars).
  - Editorial reading room (`pages/article.php`): comfortable reading width with secondary metadata & author sidebar.
  - Article editor (`admin/article-create.php`, `admin/article-edit.php`): main manuscript composer with right-hand publishing controls.
  - Contributor dashboard (`pages/dashboard.php`): manuscript management with right-hand profile, bookmarks, and fast-action panel.
  - Authentication views (`auth/login.php`, `auth/register.php`): branded institutional sidebar with accessible credential form.
  - About view (`pages/about.php`): division charter with governance structure sidebar.
* **Three-Column Responsive Grids (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3`)**:
  - Published articles directory (`index.php`, `pages/articles.php`, `pages/topic.php`).
  - Featured taxonomy topic cards.
  - News and community announcements (`pages/news.php`).
  - Administrative metric summary panels and media library cards.
* **Integrated Mobile Experience**:
  - Fluid containers using `max-w-*` and CSS grids (zero fixed pixel widths, zero horizontal overflow).
  - Mobile header with LagunaWISE emblem, touch-friendly hamburger trigger, and slide-down navigation drawer with contextual login/dashboard CTAs.

### 2. Strict No-Fake-Content Policy & Genuine Empty States
In compliance with project specifications, **zero fictional accounts, fabricated articles, fake comments, fake announcements, fake activity logs, or dummy newsletter subscribers** are inserted into the system.
When database tables contain no records, clean, institutional empty states are displayed:
* Articles: *"No articles have been published yet."*
* Announcements: *"No announcements are currently available."*
* Reader Bookmarks: *"You haven't bookmarked any articles yet."*
* Editorial Submissions: *"No submissions are awaiting review."*
* Contributor Dashboard: Displays genuine 0-counts and prompts to draft the first manuscript.

### 3. Role-Based Access Control (RBAC) & Editorial Workflow
Four standardized authorization roles enforced on the backend:
1. **Administrator** (`id: 1`): Full system control, role assignments, category moderation, announcement releases, global settings, and editorial publishing.
2. **Editor** (`id: 2`): Submission review queue (`admin/submissions.php`), manuscript editing, revision requests with feedback notes, and publishing approval.
3. **Contributor** (`id: 3`): Default role for public self-registration. Immediate access to compose articles, save drafts, preview private manuscripts, submit for review, and update work.
4. **Reader** (`id: 4`): Public engagement, commentary, bookmarking, and reading lists.

### 4. Functional Article Creation & Review Pipeline
* **Manuscript Composer** (`admin/article-create.php` & `admin/article-edit.php`):
  - Article title, subtitle, category assignment, cover image file upload or preset selector.
  - Rich text formatting toolbar (H2, H3, P, Bold, Italic, Blockquote, Lists, Links, Image insertion, Cite).
  - Dedicated **References & Sources** field for academic rigor.
  - Optional comma-separated **Tags** for enhanced searchability.
  - Functional buttons: **Save Draft**, **Preview Manuscript**, **Submit for Review**, **Update Article**, and **Delete Draft**.
* **Private Manuscript Preview** (`pages/article-preview.php`):
  - Author and editorial staff can preview unpublished manuscripts formatted exactly as the public reading room.
* **Dedicated Editorial Review Pipeline** (`admin/submissions.php`):
  - Filter by review status (Pending Review, In Revision, Approved, Archived).
  - Editorial actions modal: **Approve Manuscript**, **Request Revision** (with mandatory feedback instructions), **Publish Live**, or **Archive**.

### 5. Administrative CMS (`/admin/`)
* **Overview Dashboard**: Real-time MySQL counters, live submission pipeline status, and security audit log.
* **Article Management**: Full CRUD lifecycle (Draft &rarr; Pending Review &rarr; Revision Requested &rarr; Approved &rarr; Published &rarr; Archived).
* **Category Taxonomy**: Topic management with custom icons and color tokens.
* **User Governance**: Role assignment, account status toggling, and registration logs.
* **Media Asset Library**: Upload, preview, and path copying for visual assets (up to 5MB) with non-executable storage protection.
* **Platform Settings**: Dynamic configuration for headquarters address, partnership emails, and social links.

### 6. Defensive Web Security
* **SQL Injection Prevention**: Parameterized queries everywhere via PHP Data Objects (PDO).
* **Cryptographic CSRF Protection**: Session-bound 64-character tokens required for all mutating POST requests.
* **XSS Mitigation**: Context-appropriate escaping via `htmlspecialchars()` and strict HTML sanitization.
* **Session Hardening**: `HttpOnly`, `SameSite=Lax` cookies with `session_regenerate_id(true)` to prevent fixation.
* **Rate Limiting**: Brute-force protection for login attempts and contact submissions.

---

## 🛠️ Technology Stack
* **Backend**: PHP 8.1+ / PHP 8.2 (Running on local XAMPP 8.2.12)
* **Database**: MariaDB 10.4+ / MySQL 8.0+ (`utf8mb4_unicode_ci`, InnoDB)
* **Web Server**: Apache 2.4+ (`mod_rewrite` clean URLs with query-parameter fallback)
* **Frontend**: HTML5, Tailwind CSS utility tokens, Vanilla JavaScript (ES6+), Font Awesome 6
* **Agent Skills**: `php-development`, `frontend-skill`, `web-application-security`, `accessibility`

---

## 📂 Project Directory Structure

```
c:\xampp\htdocs\LW-ideology\
├── .agents/
│   └── skills/                  # Installed Antigravity agent skills
├── actions/                     # Mutating backend action handlers
│   ├── article-action.php       # Delete draft & submit for review handler
│   ├── bookmark-action.php      # AJAX bookmark toggle handler
│   ├── comment-action.php       # Article discussion handler
│   └── newsletter-action.php    # Newsletter subscription handler
├── admin/                       # Administration CMS
│   ├── includes/                # CMS header, footer, and navigation
│   ├── index.php                # CMS dashboard & metric overview
│   ├── articles.php             # Article CRUD & publishing table
│   ├── article-create.php       # Rich-text manuscript composer
│   ├── article-edit.php         # Manuscript editor & peer-review feedback
│   ├── submissions.php          # Dedicated editorial review queue
│   ├── categories.php           # Topic taxonomy management
│   ├── users.php                # User role governance
│   ├── comments.php             # Discussion moderation
│   ├── announcements.php        # News & campaign updates
│   ├── media.php                # Media library asset manager
│   ├── settings.php             # Website global settings
│   └── profile.php              # Personal credentials & password change
├── assets/
│   ├── images/                  # Official LagunaWISE logos, SVGs, and covers
│   └── uploads/                 # Uploaded media storage (protected by .htaccess)
├── auth/                        # Authentication endpoints
│   ├── login.php                # Responsive 2-column login with rate limiting
│   ├── register.php             # Responsive 2-column registration (Contributor / initial Admin)
│   ├── logout.php               # Session termination
│   └── forgot-password.php      # Password recovery request
├── cli/                         # Command-line administrative utilities
│   └── create-admin.php         # CLI script to create or promote administrator
├── config/                      # Core application configuration
│   ├── app.php                  # Base URL detection and app constants
│   ├── database.php             # PDO database connection factory
│   └── security.php             # CSRF tokens, sanitization, and rate limiter
├── database/                    # SQL migrations and clean seeds
│   ├── schema.sql               # 14 normalized InnoDB tables
│   └── seed.sql                 # Clean seed: 4 system roles, 8 taxonomy categories, 12 settings
├── documentation/               # Technical documentation
│   ├── screenshots/             # 14 UI screenshots across viewports
│   ├── DATABASE.md
│   ├── INSTALLATION.md
│   ├── INSTALLED_SKILLS.md
│   └── USER_GUIDE.md
├── includes/                    # Reusable public layout components
│   ├── auth.php                 # Session auth, role verification, and login helpers
│   ├── footer.php               # Institutional footer & newsletter callout
│   ├── functions.php            # Security sanitizers, formatters, and helpers
│   └── header.php               # Responsive navigation & mobile drawer
├── pages/                       # Public interface pages
│   ├── about.php                # 2-column institutional charter & governance
│   ├── article.php              # 2-column reading view with citation & bookmarking
│   ├── article-preview.php      # Private author/staff manuscript preview
│   ├── articles.php             # 3-column articles directory with search & filters
│   ├── contact.php              # Inquiries and partnership form
│   ├── dashboard.php            # 2-column contributor dashboard
│   ├── news.php                 # 3-column announcements and campaigns
│   ├── search.php               # Full-site search results
│   ├── sitemap.php              # Human & machine-readable sitemap
│   ├── topic.php                # 3-column taxonomy topic monographs
│   └── topics.php               # 3-column taxonomy directory
├── tests/                       # Automated QA test suite
│   └── index.php                # 22 automated unit, integration, and security tests
├── .htaccess                    # Apache security and clean URL rewrites
├── robots.txt                   # Search crawler directives
└── README.md
```

---

## ⚡ Quick Start Local Execution

1. **Verify Services**: Ensure Apache and MySQL are running in XAMPP.
2. **Database Migration**:
   ```powershell
   Get-Content "database\schema.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root
   Get-Content "database\seed.sql" | & "C:\xampp\mysql\bin\mysql.exe" -u root
   ```
3. **Open Website**:
   ```
   http://localhost/LW-ideology/
   ```
   Or:
   ```
   http://localhost/lagunawise-ideology/
   ```
4. **Run Automated QA Test Suite**:
   ```powershell
   C:\xampp\php\php.exe tests\index.php
   ```
   Or visit in browser: `http://localhost/LW-ideology/tests/index.php` (Passes 22 / 22 tests).

---

## 👤 User Registration & Authentication System

### Initial Administrator Setup
By design, the platform does not pre-seed fake users:
1. **Option A (Web Self-Bootstrap)**: Visit `auth/register.php`. The very first registered account automatically receives the **Administrator** role (`id: 1`). All subsequent registrations automatically receive the **Contributor** role (`id: 3`).
2. **Option B (Command Line Utility)**: Create or promote an administrator from PowerShell:
   ```powershell
   C:\xampp\php\php.exe cli/create-admin.php --name="System Administrator" --username="admin" --email="admin@lagunawise.org" --password="YourSecurePassword123!"
   ```

### Public Contributor Registration
* Visitors can register at `auth/register.php` with their name, username, email, and password.
* Every newly registered user is granted the **Contributor** role immediately.
* Contributors are redirected to their **Contributor Dashboard** (`pages/dashboard.php`), where they can immediately draft, preview, and submit articles to the editorial review pipeline.

---

## ⚖️ License & Intellectual Standards
© 2026 LagunaWISE Ideology Division. Nonpartisan educational and intellectual digital publication.
All articles and research toolkits may be cited and shared for educational purposes with attribution.
