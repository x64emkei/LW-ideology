# LAGUNAWISE — IDEOLOGY DIVISION
## Comprehensive Master Project Plan & Architecture

---

## 1. Project Overview & Identity
* **Organization**: LagunaWISE
* **Division**: Ideology Division
* **Platform Type**: Digital Publication, Editorial, and Advocacy Platform
* **Mission**: Foster critical thinking, media & information literacy, digital citizenship, and reasoned discourse on contemporary social issues through rigorous editorial standards and an accessible intellectual digital hub.
* **Location**: Barangay Barandal, Calamba City, Laguna
* **Official Contact**: lagunawise.ph@gmail.com

---

## 2. Technology Stack & Architectural Principles
* **Backend**: PHP 8.1+ (Running on local XAMPP 8.2.12 with PDO, MySQL/MariaDB)
* **Frontend**: Semantic HTML5, Tailwind CSS (utility design system with custom CSS tokens), Vanilla JavaScript (ES6+ modular, no heavy framework dependencies), Lucide & Font Awesome icons
* **Database**: MySQL 8.0+ / MariaDB with `utf8mb4_unicode_ci` and `InnoDB` engine
* **Security Standards**:
  - Parameterized queries everywhere via PDO
  - Bcrypt hashing via `password_hash($pwd, PASSWORD_DEFAULT)`
  - Cryptographically secure CSRF tokens for all state-changing POST requests
  - Strict input sanitization and output escaping with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`
  - Granular server-side Role-Based Access Control (RBAC)
  - Content Security Policy (CSP), X-Frame-Options, X-Content-Type-Options headers
* **Design System & Palette**:
  - Primary Navy: `#061638`
  - Secondary Navy: `#0B2252`
  - LagunaWISE Blue: `#1557A6`
  - Technology Blue: `#2799E8`
  - Light Blue Accent: `#DCEEFF`
  - Light Background: `#F4F7FB`
  - Body Text: `#26344A`
  - Muted Text: `#718096`
  - Typography: Plus Jakarta Sans / Inter for UI & Headings; Source Serif 4 for longform editorial reading

---

## 3. Database Entity-Relationship (ER) Documentation

### 3.1 Tables & Schema Relationships
```
[roles] 1 ────< [user_roles] >──── 1 [users]
                                       │
            ┌──────────────────────────┼──────────────────────────┐
            │                          │                          │
            ▼ 1                        ▼ 1                        ▼ 1
       [articles]                 [comments]                 [bookmarks]
        │    │                         │                          │
        │    └─< [editorial_reviews]   │                          │
        │                              │                          │
        ▼ 1                            ▼                          ▼
[article_categories]             [activity_logs]            (user-specific)
        ▲
        │
[categories]

[announcements]
[newsletter_subscribers]
[media]
[website_settings]
```

### 3.2 Detailed Table Specifications
1. **`roles`**: `id`, `name` (super_admin, admin, editor, writer, reader), `display_name`, `description`, `created_at`
2. **`users`**: `id`, `name`, `username`, `email`, `password_hash`, `bio`, `avatar`, `status` (active, inactive, suspended), `created_at`, `updated_at`
3. **`user_roles`**: `user_id`, `role_id`, `assigned_at` (composite primary key)
4. **`categories`**: `id`, `name`, `slug`, `description`, `icon`, `is_featured`, `created_at`
5. **`articles`**: `id`, `author_id`, `title`, `slug`, `subtitle`, `excerpt`, `content`, `cover_image`, `reading_time`, `status` (draft, pending_review, approved, published, archived), `views_count`, `published_at`, `created_at`, `updated_at`
6. **`article_categories`**: `article_id`, `category_id` (composite primary key)
7. **`comments`**: `id`, `article_id`, `user_id`, `author_name`, `author_email`, `content`, `status` (approved, pending, spam, rejected), `created_at`
8. **`bookmarks`**: `id`, `user_id`, `article_id`, `created_at` (unique user + article)
9. **`announcements`**: `id`, `title`, `slug`, `summary`, `content`, `badge_type` (announcement, campaign, activity, initiative), `is_published`, `published_at`, `created_at`
10. **`newsletter_subscribers`**: `id`, `email`, `status` (subscribed, unsubscribed), `token`, `subscribed_at`
11. **`media`**: `id`, `uploader_id`, `filename`, `original_name`, `file_path`, `mime_type`, `file_size`, `created_at`
12. **`editorial_reviews`**: `id`, `article_id`, `reviewer_id`, `action` (submitted, approved, rejected, revision_requested, published), `feedback`, `created_at`
13. **`activity_logs`**: `id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`
14. **`website_settings`**: `setting_key` (PK), `setting_value`, `category`, `description`, `updated_at`

---

## 4. Route & Page Inventory

### 4.1 Public Pages
* `/` or `/index.php`: Editorial Homepage featuring hero, LagunaWISE identity, featured articles, topic highlights, news & updates, mission statement, newsletter subscription.
* `/pages/about.php`: About the Ideology Division, educational philosophy, leadership/team, principles of media literacy and digital citizenship.
* `/pages/articles.php`: Full article catalog with real-time category filtering, search query input, date sorting, pagination, and featured insight.
* `/pages/article.php?slug={slug}`: Immersive editorial reader with reading progress, font size adjuster, table of contents, author card, bookmarking toggle, interactive comment section, and related articles.
* `/pages/topics.php`: Curated topic collection (Misinformation, Media Literacy, Social Discourse, Digital Citizenship, Philosophy, AI & Society).
* `/pages/topic.php?slug={slug}`: Deep-dive topic page with topic manifesto and dynamically linked articles.
* `/pages/news.php`: Organizational announcements, campaigns, division initiatives, and event briefs.
* `/pages/contact.php`: Contact form, office location in Calamba City, partnership details, and official social channels.
* `/pages/search.php`: Comprehensive site search covering articles, categories, and announcements with highlighted terms.

### 4.2 Authentication Flow (`/auth/`)
* `/auth/login.php`: Secure authentication with rate limiting protection, session regeneration, and role-based redirect.
* `/auth/register.php`: Public registration defaulting safely to `reader` role with validation.
* `/auth/logout.php`: Session destruction, cookie invalidation, and redirect.
* `/auth/forgot-password.php`: Secure password reset request flow.

### 4.3 Reader & Contributor Portal
* `/pages/dashboard.php`: Adaptive user portal:
  - **Reader view**: Profile details, reading list / bookmarked articles, submitted comments history, newsletter preferences.
  - **Writer view**: Personal drafts, submissions undergoing editorial review, published articles counter, revision feedback from editors.
  - **Article Creation/Edit**: Integrated responsive WYSIWYG/Markdown editor with draft saving and review submission.

### 4.4 Editorial & Administrative CMS (`/admin/`)
* `/admin/index.php` or `/admin/dashboard.php`: High-level metrics (total articles, users, comments, newsletter subscribers, review queue), recent activity logs.
* `/admin/articles.php`: Editorial workflow management (status filters: All, Published, Pending Review, Draft, Archived; single-click publish/approve/reject/revision).
* `/admin/article-create.php` & `/admin/article-edit.php`: Full article management with cover image uploader, category multi-select, excerpt, and editorial notes.
* `/admin/categories.php`: Category management (CRUD, slug auto-generation, featured toggle).
* `/admin/users.php`: Role assignment (promote/demote users between Reader, Writer, Editor, Admin, Super Admin), account activation/suspension.
* `/admin/comments.php`: Moderation queue (approve, mark spam, delete).
* `/admin/announcements.php`: Create and schedule announcements and division campaigns.
* `/admin/media.php`: Media gallery browser and asset uploader with MIME & file size validation.
* `/admin/settings.php`: Dynamic site configuration (title, contact email, social media links, footer text, maintenance toggles).
* `/admin/profile.php`: Personal credentials update with current password verification.

---

## 5. Role-Permission Matrix

| Action / Capability | Public Visitor | Reader | Writer | Editor | Admin | Super Admin |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| Browse published articles & news | Yes | Yes | Yes | Yes | Yes | Yes |
| Search and filter content | Yes | Yes | Yes | Yes | Yes | Yes |
| Subscribe to newsletter | Yes | Yes | Yes | Yes | Yes | Yes |
| Bookmark articles | No | Yes | Yes | Yes | Yes | Yes |
| Post comments (moderated) | No | Yes | Yes | Yes | Yes | Yes |
| Access User Dashboard | No | Yes | Yes | Yes | Yes | Yes |
| Write / Edit own drafts | No | No | Yes | Yes | Yes | Yes |
| Submit article for review | No | No | Yes | Yes | Yes | Yes |
| Review & approve submissions | No | No | No | Yes | Yes | Yes |
| Publish articles to live site | No | No | No | Yes | Yes | Yes |
| Access `/admin/` CMS | No | No | No | Yes | Yes | Yes |
| Manage Categories | No | No | No | No | Yes | Yes |
| Manage Announcements | No | No | No | No | Yes | Yes |
| Moderate Comments | No | No | No | Yes | Yes | Yes |
| Media Library Management | No | No | No | Yes | Yes | Yes |
| User Role Management | No | No | No | No | No | Yes |
| System Settings & Logs | No | No | No | No | Yes | Yes |

---

## 6. Implementation Checklist

- [x] Stage 1: Discovery & Skills Installation (Installed: php-development, frontend-skill, web-application-security, accessibility)
- [x] Stage 2: Architecture & Project Plan Documentation
- [ ] Stage 3: Database Schema & Demonstration Seed Generation (`database/schema.sql`, `database/seed.sql`)
- [ ] Stage 4: Core PHP Configuration & Helper Libraries (`config/`, `includes/functions.php`, `includes/auth.php`, `includes/security.php`)
- [ ] Stage 5: Master Layout Components (`includes/header.php`, `includes/footer.php`, `includes/navigation.php`)
- [ ] Stage 6: Public Website Development (Homepage, About, Articles, Article Reader, Topics, Topic Detail, News, Contact, Search)
- [ ] Stage 7: Authentication & Dashboard Systems (Login, Register, Logout, Forgot Password, Reader/Writer Dashboard)
- [ ] Stage 8: Administrative CMS (`/admin/` Dashboard, Articles, Categories, Users, Announcements, Media, Comments, Settings)
- [ ] Stage 9: Backend Action Handlers (Bookmark toggle, Comment submission, Newsletter subscribe, Editorial review action)
- [ ] Stage 10: XAMPP Local Deployment & Verification (Apache & MySQL start, database migration, local URL validation)
- [ ] Stage 11: End-to-End Workflow Testing & Validation (Public, Reader, Writer, Editor, Admin, Security)
- [ ] Stage 12: Screenshots, Documentation (`DATABASE.md`, `INSTALLATION.md`, `USER_GUIDE.md`, `README.md`)
