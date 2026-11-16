# LagunaWISE Ideology Division — Database Architecture & Specification

## Database Overview
* **Database Name**: `lagunawise_ideology`
* **Default Collation**: `utf8mb4_unicode_ci`
* **Storage Engine**: `InnoDB` (ACID Compliant with strict Foreign Key constraints)
* **Access Protocol**: PHP Data Objects (PDO) with strictly parameterized queries
* **Content Policy**: Strict zero-dummy-record policy. Initial seed consists strictly of the 4 system roles, 8 taxonomy categories, and 12 baseline website settings.

---

## Entity Relationship Overview

```
                      [roles]
                         │
                         ▼ (1:N)
[users] ─────────► [user_roles]
   │
   ├──────────────► [articles] ◄──────────────► [article_categories] ◄── [categories]
   │                   │
   │                   ├──────────────────────► [editorial_reviews]
   │                   │
   │                   └──────────────────────► [comments]
   │
   ├──────────────► [bookmarks]
   ├──────────────► [media]
   └──────────────► [activity_logs]

[announcements]
[newsletter_subscribers]
[website_settings]
```

---

## Detailed Table Dictionary

### 1. `roles`
Stores authorization privilege levels.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `name` (VARCHAR(50), UNIQUE): Machine name:
  - `administrator` (ID: 1) — Full administrative governance, settings, user management, publishing
  - `editor` (ID: 2) — Editorial review queue, manuscript edits, revision requests, publishing
  - `contributor` (ID: 3) — Publicly registered authors; manuscript composition, drafts, review submissions
  - `reader` (ID: 4) — Public community readers; bookmarking, moderated discussions
* `display_name` (VARCHAR(100)): Human-readable title
* `description` (TEXT): Scope of authorization
* `created_at` (TIMESTAMP)

### 2. `users`
Registered contributors, editors, administrators, and public readers.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `name` (VARCHAR(100)): Full name
* `username` (VARCHAR(50), UNIQUE): Unique username
* `email` (VARCHAR(150), UNIQUE): Email address
* `password_hash` (VARCHAR(255)): Bcrypt cryptographic hash
* `bio` (TEXT): Academic / biographical background
* `avatar` (VARCHAR(255)): Relative path to avatar asset
* `status` (ENUM: `active`, `inactive`, `suspended`)
* `must_change_password` (TINYINT(1)): Enforces password update on first login
* `last_login_at` (DATETIME): Timestamp of last authenticated session
* `created_at`, `updated_at` (TIMESTAMP)

### 3. `user_roles`
Composite relationship table linking users to roles.
* `user_id` (INT UNSIGNED, FK -> `users.id` ON DELETE CASCADE)
* `role_id` (INT UNSIGNED, FK -> `roles.id` ON DELETE CASCADE)
* `assigned_at` (TIMESTAMP)
* *Primary Key*: `(user_id, role_id)`

### 4. `categories`
Thematic taxonomy pillars.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `name` (VARCHAR(100), UNIQUE): e.g. Media Literacy, Critical Thinking
* `slug` (VARCHAR(120), UNIQUE): URL-friendly slug
* `description` (TEXT): Pedagogical scope
* `icon` (VARCHAR(50)): FontAwesome icon reference
* `color_hex` (VARCHAR(20)): Primary accent color
* `is_featured` (TINYINT(1)): Display priority on homepage
* `display_order` (INT): Sorting weight
* `created_at` (TIMESTAMP)

### 5. `articles`
Core editorial monographs, investigative insights, and civic toolkits.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `author_id` (INT UNSIGNED, FK -> `users.id` ON DELETE RESTRICT)
* `title` (VARCHAR(255))
* `slug` (VARCHAR(255), UNIQUE)
* `subtitle` (VARCHAR(255)): Thesis statement / secondary title
* `excerpt` (TEXT): Summary for catalogs and meta tags
* `content` (MEDIUMTEXT): Sanitized rich editorial body
* `cover_image` (VARCHAR(255)): Asset path to cover visual
* `reading_time` (INT UNSIGNED): Calculated reading time in minutes
* `references_sources` (TEXT, NULL): Citations, academic references, and source attribution
* `tags` (VARCHAR(255), NULL): Comma-separated search tags
* `status` (ENUM: `draft`, `pending_review`, `revision_requested`, `approved`, `published`, `archived`)
* `views_count` (INT UNSIGNED): Monitored reader engagement
* `is_featured` (TINYINT(1)): Spotlight priority
* `published_at` (DATETIME): Timestamp of release
* `created_at`, `updated_at` (TIMESTAMP)

### 6. `article_categories`
Composite relationship table linking articles to taxonomy categories.
* `article_id` (INT UNSIGNED, FK -> `articles.id` ON DELETE CASCADE)
* `category_id` (INT UNSIGNED, FK -> `categories.id` ON DELETE CASCADE)
* *Primary Key*: `(article_id, category_id)`

### 7. `comments`
Public discussion contributions on published monographs.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `article_id` (INT UNSIGNED, FK -> `articles.id` ON DELETE CASCADE)
* `user_id` (INT UNSIGNED, FK -> `users.id` ON DELETE SET NULL): Optional user link
* `author_name` (VARCHAR(100))
* `author_email` (VARCHAR(150))
* `content` (TEXT)
* `status` (ENUM: `approved`, `pending`, `rejected`, `spam`)
* `created_at` (TIMESTAMP)

### 8. `bookmarks`
Saved reading list entries for authenticated readers.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `user_id` (INT UNSIGNED, FK -> `users.id` ON DELETE CASCADE)
* `article_id` (INT UNSIGNED, FK -> `articles.id` ON DELETE CASCADE)
* `created_at` (TIMESTAMP)
* *Unique Constraint*: `(user_id, article_id)`

### 9. `announcements`
Organizational dispatches, student research calls, and civic campaigns.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `title` (VARCHAR(255))
* `slug` (VARCHAR(255), UNIQUE)
* `summary` (TEXT)
* `content` (TEXT)
* `badge_type` (ENUM: `announcement`, `campaign`, `activity`, `initiative`, `publication`)
* `is_published` (TINYINT(1))
* `published_at` (DATETIME)
* `created_at` (TIMESTAMP)

### 10. `newsletter_subscribers`
Fortnightly dispatch subscribers.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `email` (VARCHAR(150), UNIQUE)
* `status` (ENUM: `subscribed`, `unsubscribed`)
* `token` (VARCHAR(64)): Cryptographic unsubscribe verification key
* `subscribed_at` (TIMESTAMP)

### 11. `media`
Internal asset library for uploaded images.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `uploader_id` (INT UNSIGNED, FK -> `users.id` ON DELETE SET NULL)
* `filename` (VARCHAR(255)): Sanitized unique filesystem name
* `original_name` (VARCHAR(255)): User filename on upload
* `file_path` (VARCHAR(255)): Relative web path
* `mime_type` (VARCHAR(100)): Server-verified MIME type
* `file_size` (INT UNSIGNED): File size in bytes
* `created_at` (TIMESTAMP)

### 12. `editorial_reviews`
Multi-tier peer review history for manuscripts.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `article_id` (INT UNSIGNED, FK -> `articles.id` ON DELETE CASCADE)
* `reviewer_id` (INT UNSIGNED, FK -> `users.id` ON DELETE RESTRICT)
* `action` (ENUM: `submitted`, `approved`, `rejected`, `revision_requested`, `published`)
* `feedback` (TEXT): Constructive guidance for the author
* `created_at` (TIMESTAMP)

### 13. `activity_logs`
Defensive audit trail recording administrative and authentication events.
* `id` (INT UNSIGNED, PK, AUTO_INCREMENT)
* `user_id` (INT UNSIGNED, FK -> `users.id` ON DELETE SET NULL)
* `action` (VARCHAR(100)): Event identifier (`user_login`, `article_published`, etc.)
* `entity_type` (VARCHAR(50)): Associated table entity
* `entity_id` (INT UNSIGNED): Associated record ID
* `ip_address` (VARCHAR(45)): Client IP address
* `user_agent` (TEXT): Client browser signature
* `details` (TEXT): Audit explanation
* `created_at` (TIMESTAMP)

### 14. `website_settings`
Dynamic key-value settings table.
* `setting_key` (VARCHAR(100), PK)
* `setting_value` (TEXT)
* `category` (VARCHAR(50))
* `description` (VARCHAR(255))
* `updated_at` (TIMESTAMP)

---

## Baseline Seed Data Summary
In accordance with the strict zero-fake-content policy:
* **Roles (4)**: `administrator`, `editor`, `contributor`, `reader`.
* **Taxonomy Categories (8)**:
  1. *Critical Thinking* (`#1557A6`)
  2. *Media Literacy* (`#2799E8`)
  3. *Digital Citizenship* (`#0B2252`)
  4. *Informed Discourse* (`#0D9488`)
  5. *Youth Civic Engagement* (`#D97706`)
  6. *Open Information* (`#4F46E5`)
  7. *Ethical Technology* (`#475569`)
  8. *Community Research* (`#2563EB`)
* **Website Settings (12)**: Official organization name, motto, email, headquarters address, copyright, social URLs, and default meta tags.
* **Users / Articles / Comments / Announcements / Subscribers**: **0** records initially inserted.
