# LagunaWISE Ideology Division — Comprehensive User Guide

Welcome to the **LagunaWISE Ideology Division** platform. This guide explains how to navigate, read, author, peer-review, and administer content across the website.

---

## 1. For Public Visitors & Readers

### 1.1 Exploring Articles & Research Topics
* **Homepage**: Features our intellectual hero statement, spotlight article, four core pillars, recent publications, and news dispatches.
* **Articles Directory** (`pages/articles.php`):
  - **Search**: Use the top search bar to filter by keywords across titles and body texts.
  - **Category Filtering**: Filter by specific research domains (Media Literacy, Misinformation, Critical Thinking, etc.).
  - **Sort**: Sort by *Newest First*, *Oldest First*, *Most Viewed*, or *Quickest Read*.
* **Reading an Article** (`pages/article.php`):
  - Read with refined typography formatted in Source Serif 4.
  - View the estimated reading time, publication date, and author credential card.
  - Click the **Share** buttons (Facebook, X, LinkedIn, or Copy Link) to share insights.
  - Click **Bookmark** to save the article to your personal reading list (requires reader login).
* **Participating in Discussions**:
  - Scroll to the bottom of any published article to join the moderated inquiry discussion.
  - Authenticated readers have their name and credentials linked automatically.

### 1.2 Subscribing to the Fortnightly Dispatch
Enter your email address in the newsletter form located in the footer or homepage callout to receive research briefs and civic toolkits directly.

### 1.3 Creating a Reader Account (`auth/register.php`)
1. Click **Join the Community** in the main navigation.
2. Provide your name, desired username, email address, and a secure password (minimum 8 characters).
3. Upon registration, you are automatically signed in and redirected to your **User Dashboard** (`pages/dashboard.php`).

---

## 2. For Writers & Research Fellows

### 2.1 The Editorial Lifecycle
```
[Writer drafts article] ──► [Save Draft] ──► [Submit for Review] ──► [Editor reviews]
                                                                          │
                        ┌─────────────────────────────────────────────────┴────────┐
                        ▼                                                          ▼
             [Revision Requested]                                              [Approved / Published]
```

### 2.2 Drafting a New Article (`admin/article-create.php`)
1. Log in with your contributor account (e.g., `marcus.reyes` or `sophia.delacruz`).
2. Navigate to **Create New Article** from the top user dropdown or CMS sidebar.
3. Complete the required fields:
   - **Title**: Strong, descriptive inquiry title.
   - **Subtitle**: Secondary thesis statement.
   - **Excerpt**: Concise 2-3 sentence summary for previews.
   - **Main Article Body**: Format with semantic subheadings (`<h2>`, `<h3>`), quotes (`<blockquote>`), and bullet points (`<ul>`).
   - **Cover Image**: Choose an official themed SVG preset or upload an image file (under 5MB).
   - **Assign Categories**: Select one or more relevant taxonomy topics.
4. Set status to:
   - **Draft**: Saves work locally without sending to editors.
   - **Submit for Review**: Sends the manuscript to the editorial queue.
5. Click **Save & Process Article**.

### 2.3 Responding to Editorial Feedback
When an editor reviews your manuscript, feedback notes appear on your **User Dashboard** (`pages/dashboard.php`). You can open the manuscript, make revisions, and re-submit for approval.

---

## 3. For Editors

### 3.1 Managing the Review Queue (`admin/index.php` & `admin/articles.php`)
1. Log in with an editor account (e.g., `elena.vance`).
2. Open the **CMS Dashboard** (`admin/index.php`). The *Editorial Review Queue* lists all pending manuscripts.
3. Click **Review &rarr;** on any submission to open the manuscript editor (`admin/article-edit.php`).
4. In the sidebar:
   - Set status to **Approved** or **Published**.
   - If changes are needed, set status to **Request Revisions** and write constructive guidance in the *Editorial Feedback* box.
5. Click **Update Article**.

---

## 4. For System Administrators & Super Admins

### 4.1 User Governance & Role Management (`admin/users.php`)
* View all registered readers, writers, editors, and administrators.
* **Role Promotion / Demotion**: Change a user's role instantaneously using the role selector dropdown.
* **Account Status**: Toggle user state between `active` and `inactive` to suspend accounts if necessary.

### 4.2 Category & Topic Management (`admin/categories.php`)
* Add new research categories with custom icons, color tokens, and descriptions.
* Toggle the **Featured Topic** flag to showcase priority themes on the public homepage.

### 4.3 Media Asset Library (`admin/media.php`)
* Upload visual assets (JPG, PNG, WEBP, SVG) up to 5MB.
* Copy asset paths with one click to embed inside article bodies.
* Delete outdated or unused media. Uploaded directories are protected against arbitrary code execution.

### 4.4 Announcement Releases (`admin/announcements.php`)
* Publish division updates, student symposium calls, and workshop notifications.
* Assign badges: *Announcement*, *Advocacy Campaign*, *Community Activity*, *Division Initiative*, or *Monograph Release*.

### 4.5 Platform Settings (`admin/settings.php`)
* Update division contact email (`lagunawise.ph@gmail.com`), headquarters location (`Barangay Barandal, Calamba City, Laguna`), social links, and footer legal text.
