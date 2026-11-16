# LagunaWISE Ideology Division — Comprehensive User Guide

Welcome to the **LagunaWISE Ideology Division** platform. This guide explains how to navigate, read, author, peer-review, and administer content across the website.

---

## 1. For Public Visitors & Readers

### 1.1 Exploring Articles & Research Topics
* **Homepage** (`index.php`):
  - Responsive 2-column hero spotlight highlighting the division's mission and inquiry pillars.
  - 3-column articles showcase featuring recent publications with genuine empty state when no articles are published (*"No articles have been published yet."*).
  - 3-column taxonomy topics exploring Critical Thinking, Media Literacy, and Civic Engagement.
  - 3-column news and announcements with clean empty state (*"No announcements are currently available."*).
* **Articles Directory** (`pages/articles.php`):
  - **Search**: Keyword search across titles, excerpts, and body text.
  - **Category Filtering**: Filter by specific research domains (Media Literacy, Misinformation, Critical Thinking, etc.).
  - **Sort**: Sort by *Newest First*, *Oldest First*, *Most Viewed*, or *Quickest Read*.
* **Reading an Article** (`pages/article.php`):
  - Two-column reading layout: ~66% comfortable reading prose paired with a ~34% contextual sidebar.
  - Formatted in Source Serif 4 typography.
  - View calculated reading time, publication date, category badges, references, and author credential card.
  - **Share**: Social sharing links (Facebook, X, LinkedIn) and 1-click URL copying.
  - **Bookmark**: Interactive AJAX bookmark toggle saves the article to your personal reading list (requires authenticated user).
  - **Academic Citation**: Copy pre-formatted citations in APA, MLA, or Chicago format.
* **Participating in Discussions**:
  - Scroll to the bottom of any published article to participate in moderated inquiry discussions.
  - Authenticated contributors and readers have their credentials linked automatically.

### 1.2 Subscribing to the Fortnightly Dispatch
Enter your email address in the newsletter subscription form located in the footer or homepage callout to receive research briefs and civic toolkits directly.

### 1.3 Creating an Account (`auth/register.php`)
1. Click **Join the Community** or **Sign In / Register** in the top navigation.
2. Complete the registration form:
   - Full Name
   - Username
   - Email Address
   - Password & Confirm Password (minimum 8 characters)
   - Agree to the Terms of Service and Privacy Policy checkbox
3. Upon registration:
   - If the user database is currently empty, the very first user automatically receives the **Administrator** role (`id: 1`) and is directed to the CMS.
   - All subsequent registrations automatically receive the **Contributor** role (`id: 3`) and are redirected to the **Contributor Dashboard** (`pages/dashboard.php`).

---

## 2. For Contributors & Writers

### 2.1 The Editorial Lifecycle
```
[Contributor drafts article] ──► [Save Draft] ──► [Preview Manuscript] ──► [Submit for Review]
                                                                                   │
                                                                                   ▼
                                                                           [Editor / Admin Reviews]
                                                                                   │
                                  ┌────────────────────────────────────────────────┴──────────────────┐
                                  ▼                                                                   ▼
                         [Revision Requested]                                                [Approved / Published]
                   (Author receives feedback notes)                                            (Live on Public Site)
```

### 2.2 The Contributor Dashboard (`pages/dashboard.php`)
The dashboard features a responsive 2-column layout (~66% manuscript management / ~34% author sidebar):
* **Real-Time MySQL Counters**: Total Articles, Drafts, In Review, Revision Requested, and Published Articles.
* **Filter Tabs**: Filter your manuscripts by *All*, *Drafts*, *In Review*, *Revision Requested*, and *Published*.
* **Editorial Feedback Banners**: If an editor has requested changes, the exact constructive feedback appears prominently on the article card with an **Edit Manuscript** button.
* **Article Actions**:
  - **Edit Manuscript**: Open the rich text editor.
  - **Preview**: View the manuscript formatted exactly as it will appear when live (`pages/article-preview.php`).
  - **Submit for Review**: Send a saved draft to the editorial queue.
  - **Delete Draft**: Delete drafts that are no longer needed.
* **Personal Bookmarks**: Fast access to saved articles. When empty, shows: *"You haven't bookmarked any articles yet."*

### 2.3 Drafting a New Article (`admin/article-create.php`)
1. Click **Draft New Article** from the dashboard or CMS navigation.
2. Fill out the manuscript fields:
   - **Article Title**: Clear, compelling title for the inquiry.
   - **Subtitle / Thesis**: Supporting summary or secondary title.
   - **Category**: Select the primary taxonomy topic.
   - **Cover Image**: Upload a file (under 5MB: JPG, PNG, WEBP, SVG) or select an official LagunaWISE SVG preset.
   - **Article Content**: Compose using the built-in formatting toolbar:
     - Heading 2 (`<h2>`) and Heading 3 (`<h3>`)
     - Paragraph (`<p>`)
     - Bold (`<strong>`) and Italic (`<em>`)
     - Blockquote (`<blockquote>`)
     - Ordered List (`<ol>`) and Unordered List (`<ul>`)
     - Hyperlink (`<a>`)
     - Image insertion (`<img>`)
     - Source Citation (`<cite>`)
   - **References & Sources**: List academic sources, interviews, government data, or reading references.
   - **Tags**: Comma-separated search tags (e.g., `misinformation, youth, digital literacy`).
3. Choose your action:
   - **Save Draft**: Saves work to MySQL as `draft` for further editing.
   - **Submit for Review**: Changes status to `pending_review` and places the manuscript in the editor queue.
   - **Preview Manuscript**: Opens a private author preview of the formatted article.

---

## 3. For Editors & Administrators

### 3.1 Managing the Editorial Submissions Queue (`admin/submissions.php`)
1. Log in with an Administrator or Editor account.
2. Navigate to **Review Queue** in the CMS sidebar or top menu.
3. Review manuscripts by status tabs:
   - **Pending Review**: Newly submitted manuscripts awaiting editorial review. When empty, displays: *"No submissions are awaiting review."*
   - **In Revision**: Articles currently returned to authors for revisions.
   - **Approved**: Approved manuscripts ready for publication scheduling.
   - **Archived**: Withdrawn or closed submissions.
4. For any manuscript, click:
   - **Preview**: Review the full rendered layout on `pages/article-preview.php`.
   - **Edit / Inspect**: Inspect the source and content on `admin/article-edit.php`.
   - **Review / Actions**: Opens the editorial review modal:
     - **Approve Manuscript**: Marks the article as approved.
     - **Request Revision**: Enter required editorial feedback notes and return the draft to the author.
     - **Publish Live**: Immediately publishes the article to the public website.
     - **Archive**: Archives the submission.

---

## 4. For System Administrators

### 4.1 Administrator Setup & Role Management
* **CLI Admin Provisioning**: You can create or elevate administrators at any time:
  ```powershell
  C:\xampp\php\php.exe cli/create-admin.php --name="System Admin" --username="admin" --email="admin@lagunawise.org" --password="YourSecurePassword123!"
  ```
* **User Governance (`admin/users.php`)**:
  - View all registered users, roles, email addresses, and join dates.
  - Promote or demote users across roles (`administrator`, `editor`, `contributor`, `reader`).
  - Toggle account status between `active` and `inactive`.
  - When no registered users exist, displays clean zero-count empty table state.

### 4.2 Category & Topic Management (`admin/categories.php`)
* Add new research categories with custom icons, color tokens, and pedagogical descriptions.
* Toggle the **Featured Topic** flag to showcase priority themes on the public homepage.

### 4.3 Media Asset Library (`admin/media.php`)
* Upload visual assets (JPG, PNG, WEBP, SVG) up to 5MB.
* Copy asset paths with one click to embed inside article bodies.
* Delete outdated or unused media. Uploaded directories are protected against arbitrary code execution via `.htaccess`.

### 4.4 Announcement Releases (`admin/announcements.php`)
* Publish division updates, student symposium calls, and workshop notifications.
* Assign badges: *Announcement*, *Advocacy Campaign*, *Community Activity*, *Division Initiative*, or *Monograph Release*.

### 4.5 Platform Settings (`admin/settings.php`)
* Update division contact email (`lagunawise.ph@gmail.com`), headquarters location (`Barangay Barandal, Calamba City, Laguna 4027`), social links, and footer legal text.
