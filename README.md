# LagunaWISE — Ideology Division Website
### Digital Publication, Editorial, and Civic Advocacy Platform
> **⚠️ PROTOTYPE DEMONSTRATION NOTICE**: This website is an educational demonstration prototype developed for academic and civic research evaluation. All articles, editorial workflows, dispatches, and institutional designations featured herein are illustrative demonstrations.

![LagunaWISE Ideology Division](assets/images/lagunawise-banner.svg)

---

## 🏛️ Project Identity & Overview
* **Organization**: LagunaWISE
* **Division**: Ideology Division
* **Headquarters**: Barangay Barandal, Calamba City, Laguna 4027, Philippines
* **Contact & Inquiries**: [lagunawise.ph@gmail.com](mailto:lagunawise.ph@gmail.com)
* **Core Philosophy**: *"Understanding Ideas. Challenging Perspectives. Empowering Minds."*

The **Ideology Division** serves as the intellectual and educational arm of LagunaWISE. It provides an accessible digital publication and research portal where students, educators, civic advocates, and the reading public can examine multifaceted social issues, deconstruct online misinformation, evaluate political discourse, and cultivate responsible digital citizenship.

---

## 🚀 Key Platform Features & Recent Updates

### 1. Cardless Mobile Reading Experience & Zero Horizontal Scrolling
* **Cardless Mobile Reading View**: On mobile devices (`< lg`), outer containers strip borders, artificial shadows, and card bounding boxes (`border-0 lg:border`, `shadow-none lg:shadow-xs`, `rounded-none lg:rounded-lg`, `px-0 sm:px-6`). The article prose flows edge-to-edge with comfortable reading gutters (`px-4 sm:px-8 lg:p-10`), mirroring leading publications such as *The Atlantic*, *The New York Times*, and *Substack*.
* **Zero Horizontal Scroll Guarantee**: Enforced `overflow-x: hidden`, `max-width: 100vw`, `word-wrap: break-word`, and `overflow-wrap: break-word` on `html` and `body`. All mobile horizontal swipe ribbons use bounded `w-full max-w-full overflow-x-auto no-scrollbar` without negative margins (`-mx-*`), completely eliminating lateral wobble on iOS and Android devices.
* **Floating Mobile Reader Bar**: A sleek glassmorphism bottom utility bar on mobile screens providing:
  - Real-time reading progress percentage.
  - Interactive font size scaling toggle (standard vs. enlarged typography).
  - One-tap article bookmarking with local session feedback.
  - Quick-jump anchor to moderated discussions.
  - Native Web Share API integration with clipboard copy fallback.

### 2. Markdown Editorial Studio & Robust Markdown Parser
* **Markdown Composer** (`admin/article-create.php`, `admin/article-edit.php`):
  - Transitioned the manuscript editor to standard GitHub-Flavored Markdown (GFM).
  - Interactive toolbar with quick formatting inserts: `H2`, `H3`, `Bold`, `Italic`, `Blockquote`, `Ordered List`, `Unordered List`, `Link`, `Image`, `Inline Code`, and `Academic Cite`.
  - Live split-tab interface with a **Write** editor tab, instant **Live Preview** tab, and quick Markdown syntax reference cheat sheet.
* **Custom Semantic Markdown Parser** (`includes/functions.php`):
  - Custom functions `render_markdown()` and `render_article_content()` reliably convert Markdown to accessible, semantic HTML with paragraph `<p>` tags, formatted headings, responsive images, styled blockquotes, code blocks, and academic source tags.
  - Completely resolves wall-of-text formatting collapse.

### 3. Global Prototype Notice
* **Header & Footer Disclaimers**: Integrated prominent yellow/amber advisory notices into the global footer (`includes/footer.php`) visible on every public and staff page, clearly indicating the website's status as an **Academic & Research Demonstration Prototype**.

### 4. Distinctive Editorial Design System & Responsive Grids
* **Brand Identity**: Authentic LagunaWISE circular emblem featuring the Philippine map silhouette and crisp official wordmark.
* **Palette Tokens**: Deep Primary Navy (`#061638`), Secondary Navy (`#0B2252`), LagunaWISE Blue (`#1557A6`), Tech Blue (`#2799E8`), Ice Blue (`#DCEEFF`), Surface (`#F4F7FB`), and Silver (`#C5CEDB`).
* **Typography Pairing**: Plus Jakarta Sans for structural headings and UI badges; Source Serif 4 for an immersive, longform editorial reading experience.
* **Desktop Two-Column Elevated Layouts (~66% / 34%)**:
  - Homepage hero section and top stories.
  - Editorial reading room (`pages/article.php`) with citations, references, and moderated inquiry.
  - Article composer (`admin/article-create.php`) with right-hand publishing metadata and taxonomy panel.
  - Contributor dashboard (`pages/dashboard.php`) with personal manuscript tracking and fast actions.

### 5. Role-Based Access Control (RBAC) & Editorial Workflow
The platform implements four standardized authorization tiers:

| Role | Access Level | Description & Core Responsibilities |
| :--- | :--- | :--- |
| **Administrator** | System & CMS Master | Full system access. Manages user roles and accounts, taxonomy categories, system announcements, media assets, platform settings, and can publish articles directly. |
| **Editor** | Editorial Governance | Manages the Editorial Review Queue (`admin/submissions.php`). Reviews submitted manuscripts, performs peer review, requests revisions with mandatory constructive feedback, and approves/publishes articles. |
| **Contributor** | Content Creation | Default role for self-registered users. Access to Contributor Dashboard (`pages/dashboard.php`), Markdown article composer, private manuscript previews, revision workflows, and draft management. |
| **Reader** | Public Engagement | Authenticated public members. Can save articles to private bookmarks, participate in moderated discussions, adjust reading preferences, and receive dispatches. |

### 6. Automated Cloud Deployment & InfinityFree Support
* **Automated FTP Deployment Tool** (`cli/ftp_deploy.py` & `cli/ftp-deploy.ps1`):
  - Built-in Python script supporting automated incremental deployment directly to remote FTP servers (`ftpupload.net:21`).
  - Automatic directory recursion, retry mechanics, and upload verification.
* **InfinityFree Database Compatibility** (`database/infinityfree_setup.sql`):
  - Pre-packaged single-step database setup avoiding `CREATE DATABASE` and `USE` statements that trigger `#1044 Access Denied` errors on shared cPanel/vPanel hosts.
* **Route Aliases & Error Resilience**:
  - Direct root and admin shims (`create_article.php`, `admin/create_article.php`) routing to `admin/article-create.php`.
  - Comprehensive PDO exception handling to prevent HTTP 500 FastCGI crashes on remote shared hosts.

### 7. Defensive Web Security
* **SQL Injection Prevention**: Parameterized queries everywhere via PHP Data Objects (PDO).
* **Cryptographic CSRF Protection**: Session-bound 64-character tokens required for all mutating POST requests.
* **XSS Mitigation**: Context-appropriate escaping via `htmlspecialchars()` and strict HTML sanitization.
* **Session Hardening**: `HttpOnly`, `SameSite=Lax` cookies with `session_regenerate_id(true)` to prevent session fixation.
* **Rate Limiting**: Brute-force protection for login attempts and contact submissions.

---

## 🛠️ Technology Stack
* **Backend**: PHP 8.1+ / PHP 8.2 (running on local XAMPP 8.2.12 or remote InfinityFree FastCGI)
* **Database**: MariaDB 10.4+ / MySQL 8.0+ (`utf8mb4_unicode_ci`, InnoDB)
* **Web Server**: Apache 2.4+ (`mod_rewrite` clean URLs with query-parameter fallback)
* **Frontend**: HTML5, Tailwind CSS utility tokens, Vanilla JavaScript (ES6+), Font Awesome 6
* **Typography**: Plus Jakarta Sans, Source Serif 4, Inter
* **Deployment**: Python 3 `ftplib` automation tool for 1-click cloud sync

---

## 📂 Project Directory Structure

```
c:\xampp\htdocs\LW-ideology\
├── .agents/                     # Agent specifications and skills
├── actions/                     # Mutating backend action handlers
│   ├── article-action.php       # Delete draft & submit for review handler
│   ├── bookmark-action.php      # AJAX bookmark toggle handler
│   ├── comment-action.php       # Article discussion handler
│   └── newsletter-action.php    # Newsletter subscription handler
├── admin/                       # Administration CMS
│   ├── includes/                # CMS header, footer, and navigation
│   ├── index.php                # CMS dashboard & metric overview
│   ├── articles.php             # Article CRUD & publishing table
│   ├── article-create.php       # Markdown manuscript composer
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
│   ├── register.php             # Responsive 2-column registration
│   ├── logout.php               # Session termination
│   └── forgot-password.php      # Password recovery request
├── cli/                         # Administrative & deployment CLI scripts
│   ├── create-admin.php         # CLI script to create or promote administrator
│   ├── ftp_deploy.py            # Automated FTP upload deployment script
│   └── ftp-deploy.ps1           # PowerShell runner for FTP cloud deployment
├── config/                      # Core application configuration
│   ├── app.php                  # Base URL detection and app constants
│   ├── database.php             # PDO database connection factory
│   └── security.php             # CSRF tokens, sanitization, and rate limiter
├── database/                    # SQL migrations and clean seeds
│   ├── infinityfree_setup.sql   # 1-Click setup tailored for InfinityFree
│   ├── schema.sql               # 14 normalized InnoDB tables
│   └── seed.sql                 # Clean seed: 4 system roles, 8 taxonomy categories, 12 settings
├── documentation/               # Technical documentation & guides
│   ├── screenshots/             # UI screenshots across viewports
│   ├── DATABASE.md              # Complete schema and entity-relationship docs
│   ├── INSTALLATION.md          # Local XAMPP setup instructions
│   ├── INSTALLED_SKILLS.md      # Antigravity agent configuration
│   └── USER_GUIDE.md            # Editorial workflow walkthrough
├── includes/                    # Reusable public layout components
│   ├── auth.php                 # Session auth, role verification, and login helpers
│   ├── footer.php               # Institutional footer with prototype advisory notice
│   ├── functions.php            # Security sanitizers, Markdown parser, formatters
│   └── header.php               # Responsive navigation, mobile anti-overflow styles
├── pages/                       # Public interface pages
│   ├── about.php                # 2-column institutional charter & governance
│   ├── article.php              # Cardless mobile reading view with citations & bookmarks
│   ├── article-preview.php      # Private author/staff manuscript preview
│   ├── articles.php             # Articles directory with search & filters
│   ├── contact.php              # Inquiries and partnership form
│   ├── dashboard.php            # Contributor dashboard
│   ├── news.php                 # Announcements and dispatches
│   ├── search.php               # Full-site search results
│   ├── sitemap.php              # Human & machine-readable sitemap
│   ├── topic.php                # Taxonomy topic monographs
│   └── topics.php               # Taxonomy directory
├── tests/                       # Automated QA test suite
│   └── index.php                # 22 automated unit, integration, and security tests
├── create_article.php           # Root alias route to admin/article-create.php
├── DEPLOY_TO_INFINITYFREE.md    # Step-by-step InfinityFree cloud hosting guide
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
4. **Run Automated QA Test Suite**:
   ```powershell
   C:\xampp\php\php.exe tests\index.php
   ```
   Or visit in browser: `http://localhost/LW-ideology/tests/index.php` (Passes 22 / 22 tests).

---

## ☁️ 1-Click InfinityFree Cloud Deployment

To deploy all local files to InfinityFree:
```powershell
python cli/ftp_deploy.py
```
Or via PowerShell script:
```powershell
.\cli\ftp-deploy.ps1
```
For complete manual hosting instructions and database setup, consult [`DEPLOY_TO_INFINITYFREE.md`](DEPLOY_TO_INFINITYFREE.md).

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
* Contributors are redirected to their **Contributor Dashboard** (`pages/dashboard.php`), where they can immediately draft in Markdown, preview private manuscripts, and submit articles to the editorial review pipeline.

---

## 📚 Technical Documentation & Specifications
* **Cloud Deployment Guide**: [`DEPLOY_TO_INFINITYFREE.md`](DEPLOY_TO_INFINITYFREE.md)
* **Technical Stack Specification**: [`TECH_STACK.md`](TECH_STACK.md)
* **Database Schema & Data Dictionary**: [`documentation/DATABASE.md`](documentation/DATABASE.md)
* **Installation & Deployment Guide**: [`documentation/INSTALLATION.md`](documentation/INSTALLATION.md)
* **Platform User Manual**: [`documentation/USER_GUIDE.md`](documentation/USER_GUIDE.md)
* **Antigravity Installed Skills Guide**: [`documentation/INSTALLED_SKILLS.md`](documentation/INSTALLED_SKILLS.md)

---

## ⚖️ License & Intellectual Standards
© 2026 LagunaWISE Ideology Division. Nonpartisan educational and intellectual digital publication.
All articles and research toolkits may be cited and shared for educational purposes with attribution.
