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

### 1. Distinctive Editorial Design System
* **Brand Identity**: Authentic LagunaWISE circular emblem with the Philippine map silhouette and official LagunaWISE wordmark with social channels.
* **Palette Tokens**: Deep Primary Navy (`#061638`), Secondary Navy (`#0B2252`), LagunaWISE Blue (`#1557A6`), Tech Blue (`#2799E8`), and Light Blue (`#DCEEFF`).
* **Typography Pairing**: Plus Jakarta Sans for structural headings and UI controls; Source Serif 4 for an immersive, longform editorial reading experience.

### 2. Full-Featured Public Publication
* **Hero Spotlight & Inquiry Pillars**: Introduces the four pillars of Critical Thinking, Media Literacy, Digital Citizenship, and Informed Discourse.
* **Articles & Insights Directory**: Real-time keyword search, topic categorization, date and view sorting, pagination, and empty-state fallbacks.
* **Longform Article Reader**: Reading progress, estimated reading time, author credential cards, citation generator, bookmarking, and social sharing.
* **Moderated Inquiry Discussions**: Public discussion forum on published monographs adhering to the nonpartisan Editorial Charter.
* **Topic Deep Dives**: Dedicated topic pages with topic manifestos and dynamically linked articles.
* **News & Community Campaigns**: Announcements, youth workshop schedules, and calls for student research papers.
* **Fortnightly Dispatch**: Duplicate-checked newsletter subscription connected to MySQL.

### 3. Role-Based Access Control (RBAC) & Editorial Workflow
Five distinct authorization roles enforced on the backend:
1. **Super Administrator**: Full system control, role assignments, audit logs, and global site governance.
2. **Administrator**: Content governance, category moderation, writer management, and announcement releases.
3. **Editor**: Review queue, manuscript edits, revision requests with feedback notes, and publishing approval.
4. **Writer / Contributor**: Article draft drafting, revision submissions, and author profile curation.
5. **Reader**: Public discourse engagement, commentary, reading lists, and article bookmarks.

### 4. Administrative CMS (`/admin/`)
* **Overview Dashboard**: Real-time counters (articles, users, comments, subscribers), review queue, and system activity audit logs.
* **Article Management**: Full CRUD lifecycle (Draft &rarr; Pending Review &rarr; Revision Requested &rarr; Approved &rarr; Published &rarr; Archived).
* **Category Taxonomy**: Topic management with custom icons and color tokens.
* **User Governance**: Role assignment, account status toggling, and registration logs.
* **Media Asset Library**: Upload, preview, and path copying for visual assets (up to 5MB) with non-executable storage protection.
* **Platform Settings**: Dynamic configuration for headquarters address, partnership emails, and social links.

### 5. Defensive Web Security
* **SQL Injection Prevention**: Parameterized queries everywhere via PHP Data Objects (PDO).
* **Cryptographic CSRF Protection**: Session-bound 64-character tokens required for all mutating POST requests.
* **XSS Mitigation**: Context-appropriate escaping via `htmlspecialchars()` and strict HTML sanitization stripping scripts and inline event handlers.
* **Session Hardening**: `HttpOnly`, `SameSite=Lax` cookies with `session_regenerate_id(true)` to prevent fixation.
* **Rate Limiting**: Brute-force protection for login attempts and contact submissions.
* **Mandatory Initial Admin Password Change**: Enforces password rotation upon first login of demonstration administrator credentials.

---

## 🛠️ Technology Stack
* **Backend**: PHP 8.1+ / PHP 8.2 (Running on local XAMPP 8.2.12)
* **Database**: MariaDB 10.4+ / MySQL 8.0+ (`utf8mb4_unicode_ci`, InnoDB)
* **Web Server**: Apache 2.4+ (`mod_rewrite` clean URLs)
* **Frontend**: HTML5, Tailwind CSS utility tokens, Vanilla JavaScript (ES6+), Font Awesome 6
* **Agent Skills**: `php-development`, `frontend-skill`, `web-application-security`, `accessibility`

---

## 📂 Project Directory Structure

```
c:\xampp\htdocs\LW-ideology\
├── .agents/
│   └── skills/                  # Installed Antigravity agent skills
│       ├── accessibility/
│       ├── frontend-skill/
│       ├── php-development/
│       └── web-application-security/
├── actions/                     # Mutating backend action handlers
│   ├── bookmark-action.php
│   ├── comment-action.php
│   └── newsletter-action.php
├── admin/                       # Administration CMS
│   ├── includes/                # CMS header, footer, and sidebar navigation
│   ├── index.php                # CMS dashboard & activity audit
│   ├── articles.php             # Article CRUD & publishing workflow
│   ├── article-create.php       # Manuscript composer
│   ├── article-edit.php         # Manuscript editor & peer-review feedback
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
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── forgot-password.php
├── config/                      # Core application configuration
│   ├── app.php
│   ├── database.php
│   └── security.php
├── database/                    # SQL migrations and demonstration seeds
│   ├── schema.sql               # 14 normalized InnoDB tables
│   └── seed.sql                 # Sample monographs, categories, and users
├── documentation/               # Technical documentation
│   ├── DATABASE.md
│   ├── INSTALLATION.md
│   ├── INSTALLED_SKILLS.md
│   └── USER_GUIDE.md
├── includes/                    # Reusable public layout components
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── pages/                       # Public interface pages
│   ├── about.php
│   ├── article.php
│   ├── articles.php
│   ├── contact.php
│   ├── dashboard.php
│   ├── news.php
│   ├── search.php
│   ├── sitemap.php
│   ├── topic.php
│   └── topics.php
├── tests/                       # Automated QA and functional test suite
│   └── index.php
├── .htaccess                    # Apache security and clean URL rewrites
├── robots.txt                   # Search crawler directives
├── PROJECT_PLAN.md              # Master architecture and specifications
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
4. **Run Quality Assurance Audit**:
   ```
   http://localhost/LW-ideology/tests/index.php
   ```

---

## 🔑 Demonstration Accounts

| Role | Username | Initial Password | Purpose |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin` | `admin123!` | System settings, roles, audit logs |
| **Editor** | `elena.vance` | `editor123!` | Peer review queue, publishing approval |
| **Writer** | `marcus.reyes` | `writer123!` | Draft articles, review feedback |
| **Writer** | `sophia.delacruz` | `writer123!` | Educational toolkits, manuscripts |
| **Reader** | `juan.reader` | `reader123!` | Bookmarks, discussion comments |

---

## ⚖️ License & Intellectual Standards
© 2026 LagunaWISE Ideology Division. Nonpartisan educational and intellectual digital publication.
All articles and research toolkits may be cited and shared for educational purposes with attribution.
