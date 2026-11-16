-- ==============================================================================
-- LAGUNAWISE — IDEOLOGY DIVISION INITIAL SYSTEM CONFIGURATION
-- Target Database: lagunawise_ideology
-- Strict No-Fake-Content Policy Compliant:
-- No fake users, no fake articles, no fake announcements, no fake comments.
-- ==============================================================================

USE `lagunawise_ideology`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. System Roles
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `roles`;
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`) VALUES
(1, 'administrator', 'Administrator', 'Full system control, role assignments, editorial governance, categories, announcements, and global site settings.'),
(2, 'editor', 'Editor', 'Peer review queue, manuscript edits, revision requests, and publishing approval.'),
(3, 'contributor', 'Contributor', 'Article draft drafting, revision submissions, and author profile curation.'),
(4, 'reader', 'Reader', 'Public discourse engagement, commentary, reading lists, and article bookmarks.');

-- ------------------------------------------------------------------------------
-- 2. Article Categories (Organizational Taxonomy)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `categories`;
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color_hex`, `is_featured`, `display_order`) VALUES
(1, 'Media Literacy', 'media-literacy', 'Critical evaluation of media sources, verifying claims, understanding framing, and discerning factual reporting.', 'newspaper', '#1557A6', 1, 1),
(2, 'Misinformation', 'misinformation', 'Investigating online disinformation ecosystems, fact-checking methodologies, and cognitive biases.', 'shield-alert', '#D9381E', 1, 2),
(3, 'Social Issues', 'social-issues', 'Nuanced, evidence-grounded perspectives on community welfare, equity, public policy, and collective progress.', 'users', '#0B2252', 1, 3),
(4, 'Digital Citizenship', 'digital-citizenship', 'Cultivating ethical online engagement, empathy, privacy hygiene, and constructive civic dialogue.', 'globe', '#2799E8', 1, 4),
(5, 'Critical Thinking', 'critical-thinking', 'Analytical frameworks, identifying logical fallacies, epistemology, and disciplined rational inquiry.', 'compass', '#6366F1', 1, 5),
(6, 'Technology and Society', 'technology-and-society', 'Examining algorithmic mediation, surveillance capitalism, artificial intelligence, and sociotechnical futures.', 'cpu', '#0D9488', 1, 6),
(7, 'Philosophy', 'philosophy', 'Ethical theory, democratic governance, intellectual history, and moral philosophy applied to modern dilemmas.', 'book-open', '#7C3AED', 0, 7),
(8, 'Education and Awareness', 'education-and-awareness', 'Educational pedagogies, youth civic literacy toolkits, and academic community outreach initiatives.', 'graduation-cap', '#EA580C', 0, 8);

-- ------------------------------------------------------------------------------
-- 3. Website Settings (Non-Personal Configuration)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `website_settings`;
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `category`, `description`) VALUES
('site_name', 'LagunaWISE — Ideology Division', 'general', 'The official publication and division title.'),
('division_title', 'Ideology Division', 'general', 'Organizational division subtitle.'),
('division_motto', 'Understanding Ideas. Challenging Perspectives. Empowering Minds.', 'general', 'Editorial platform motto and hero tagline.'),
('division_statement', 'An intellectual and educational division of LagunaWISE dedicated to fostering critical thinking, media literacy, informed discussions, and responsible digital citizenship.', 'general', 'Official divisional mission statement.'),
('contact_email', 'lagunawise.ph@gmail.com', 'contact', 'Primary partnership and correspondence email.'),
('organization_location', 'Barangay Barandal, Calamba City, Laguna, Philippines', 'contact', 'Physical headquarters and operational registry.'),
('social_facebook', 'https://facebook.com/lagunawise', 'social', 'Official Facebook organization page.'),
('social_instagram', 'https://instagram.com/lagunawise', 'social', 'Official Instagram profile.'),
('social_twitter', 'https://twitter.com/lagunawise', 'social', 'Official X / Twitter profile.'),
('social_linkedin', 'https://linkedin.com/company/lagunawise', 'social', 'Official LinkedIn page.'),
('footer_copyright', '© 2026 LagunaWISE Ideology Division. All rights reserved. Intellectual and educational nonpartisan publication.', 'footer', 'Standard legal and copyright notice.'),
('newsletter_enabled', '1', 'features', 'Toggle for public newsletter subscriptions.');

-- ------------------------------------------------------------------------------
-- 4. Clean State for Production Tables (No Fake Accounts or Fabricated Content)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `user_roles`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `articles`;
TRUNCATE TABLE `article_categories`;
TRUNCATE TABLE `comments`;
TRUNCATE TABLE `bookmarks`;
TRUNCATE TABLE `announcements`;
TRUNCATE TABLE `newsletter_subscribers`;
TRUNCATE TABLE `media`;
TRUNCATE TABLE `editorial_reviews`;
TRUNCATE TABLE `activity_logs`;


-- 4. Editorial Board Administrator User
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password_hash`, `bio`, `avatar`, `status`, `created_at`, `updated_at`) VALUES
(23, 'LagunaWISE Editorial Board', 'editorial.board', 'ideology@lagunawise.org', '$2y$10$f.iuYQLG3CG6q3eMHeg.I./GesVvpfCly45WHMmVY1dNpbZfX2veu', 'The nonpartisan scholarly and editorial governing council of the LagunaWISE Ideology Division.', 'assets/images/avatar-admin.svg', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (23, 1) ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`);

-- 5. Featured Monograph: HTTP Error 500
INSERT INTO `articles` (`id`, `author_id`, `title`, `slug`, `subtitle`, `excerpt`, `content`, `references_sources`, `tags`, `cover_image`, `reading_time`, `status`, `views_count`, `is_featured`, `published_at`, `created_at`, `updated_at`) VALUES
(17, 23, 'HTTP Error 500: Understanding Server-Side Failures, Digital Fragility, and Systemic Resilience', 'http-error-500-server-failures-and-systemic-resilience', 'An analytical investigation into why server-side execution collapses, what internal errors reveal about modern web architecture, and how distributed systems maintain integrity.', 'Unlike client-side 400-level errors, an HTTP 500 Internal Server Error represents an unexpected server-side collapse. This monograph deconstructs the architectural anatomy of 500 errors?from script syntax and misconfigured directives to database deadlocks?and outlines practical frameworks for diagnosing and remediating them in resource-constrained hosting environments.', '<p class=\"lead text-lg font-serif text-slate-700 leading-relaxed mb-6\">\nWhen an ordinary internet user encounters an <strong>HTTP Error 500 (Internal Server Error)</strong>, they are confronted with the web?s most enigmatic digital wall. Unlike client-side HTTP status codes such as <code>404 Not Found</code> or <code>403 Forbidden</code>?which transparently assign agency or fault to the requester?s actions?the <code>500 Internal Server Error</code> is an admission of server-side helplessness. It announces that an unhandled condition arose within the hosting infrastructure that prevented the server from fulfilling the request.\n</p>\n\n<h2 class=\"text-xl font-bold text-navy-900 mt-8 mb-4\">1. The Taxonomy of HTTP Error Codes: 4xx vs. 5xx</h2>\n<p class=\"mb-4\">\nTo understand why the 500 error holds such a unique place in computational diagnostics, one must look at RFC 9110 (the HTTP Semantics Standard). Status codes are partitioned into functional classes:\n</p>\n<ul class=\"list-disc pl-6 space-y-2 mb-6 text-slate-700\">\n    <li><strong>4xx Client Errors:</strong> The client?s request contains invalid syntax, lacks authorization, or targets an address that does not exist. The application behaves correctly by rejecting the transaction.</li>\n    <li><strong>5xx Server Errors:</strong> The client made a syntactically valid HTTP request, but the server encountered an unrecoverable failure while attempting to construct or deliver the response.</li>\n</ul>\n\n<div class=\"bg-blue-50 border-l-4 border-lwblue p-4 rounded-r-xl my-6\">\n    <p class=\"text-xs font-bold uppercase tracking-wider text-lwblue\">Key Architectural Distinction</p>\n    <p class=\"text-sm text-slate-700 mt-1\">\n        A 500 error is a generic \"catch-all\" status. Web servers emit it whenever an underlying program or subsystem crashes without providing a more specific 5xx status code (such as <code>502 Bad Gateway</code> or <code>504 Gateway Timeout</code>).\n    </p>\n</div>\n\n<h2 class=\"text-xl font-bold text-navy-900 mt-8 mb-4\">2. Anatomy of Common 500 Triggers in Modern Web Stacks</h2>\n<p class=\"mb-4\">\nIn modern PHP and Apache web application environments, an HTTP Error 500 almost invariably originates from one of four architectural layers:\n</p>\n\n<h3 class=\"text-lg font-semibold text-slate-800 mt-6 mb-2\">A. Web Server Configuration Overrides (.htaccess)</h3>\n<p class=\"mb-4\">\nOn Apache-powered hosts, <code>.htaccess</code> files allow directory-level overrides. However, if an administrator uses a directive that the server?s master configuration has forbidden via <code>AllowOverride</code>, Apache instantly aborts with a 500 error before PHP ever executes. Common examples include:\n</p>\n<ul class=\"list-disc pl-6 space-y-1 mb-4 text-slate-700 text-sm\">\n    <li><code>Options -Indexes</code> or <code>Options +FollowSymLinks</code> when the host disallows the <code>Options</code> keyword.</li>\n    <li>Malformed Regular Expressions inside <code>RewriteRule</code> directives.</li>\n    <li>Attempting to use <code>php_flag</code> or <code>php_value</code> in environments running PHP through FastCGI / PHP-FPM rather than <code>mod_php</code>.</li>\n</ul>\n\n<h3 class=\"text-lg font-semibold text-slate-800 mt-6 mb-2\">B. Uncaught PHP 8 Fatal Errors and TypeErrors</h3>\n<p class=\"mb-4\">\nIn modern PHP 8+, many programming mistakes that previously issued benign warnings now throw fatal <code>TypeError</code> or <code>Error</code> exceptions. If an application wraps logic with <code>catch (Exception )</code> instead of the broader <code>catch (Throwable )</code>, any uncaught error propagates to the top of the execution stack, terminating the process and forcing the web server to return status 500.\n</p>\n\n<h3 class=\"text-lg font-semibold text-slate-800 mt-6 mb-2\">C. Database Connection and Schema Failures</h3>\n<p class=\"mb-4\">\nWhen database servers fail to authenticate a connection, exhaust pool capacity, or reject queries due to foreign key violations without an application-level fallback, the unhandled PDOException instantly converts into a 500 response. This is particularly prevalent in shared hosting environments where MySQL hostnames differ from <code>localhost</code> (such as cluster-based hosts like <code>sqlXXX.infinityfree.com</code>).\n</p>\n\n<h3 class=\"text-lg font-semibold text-slate-800 mt-6 mb-2\">D. Missing Shared Extensions and File Permissions</h3>\n<p class=\"mb-4\">\nShared hosting clusters frequently disable non-essential extensions (e.g., <code>fileinfo</code> or <code>exec</code>). If code invokes functions like <code>mime_content_type()</code> without testing <code>function_exists()</code>, the execution aborts immediately with a Fatal Error.\n</p>\n\n<h2 class=\"text-xl font-bold text-navy-900 mt-8 mb-4\">3. Diagnostic Framework for Developers and Administrators</h2>\n<p class=\"mb-4\">\nSystematic remediation follows a three-phase triage:\n</p>\n\n<ol class=\"list-decimal pl-6 space-y-3 mb-6 text-slate-700\">\n    <li>\n        <strong>Rule Out Web Server Directives:</strong>\n        Temporarily rename <code>.htaccess</code> to <code>.htaccess.bak</code>. If the site immediately serves a plain <code>index.php</code>, the bug exists strictly within Apache configuration rules (e.g. <code>RewriteBase</code> or <code>Options</code>).\n    </li>\n    <li>\n        <strong>Inspect the Error Logs:</strong>\n        Examine the web server?s <code>error.log</code> or enable localized PHP error reporting during staging (<code>ini_set(\'display_errors\', \'1\'); error_reporting(E_ALL);</code>).\n    </li>\n    <li>\n        <strong>Implement Defensive Fallbacks:</strong>\n        Adopt the <code>Throwable</code> interface in try-catch blocks to catch both traditional <code>Exception</code> instances and modern PHP <code>Error</code> objects.\n    </li>\n</ol>\n\n<h2 class=\"text-xl font-bold text-navy-900 mt-8 mb-4\">4. Digital Resilience: The Civic Perspective</h2>\n<p class=\"mb-4\">\nIn the broader context of digital governance, public-interest platforms and civic educational portals must design for resilience. When civic discourse is mediated through web technology, persistent server breakdowns erode public trust, silence community dialogue, and impede information accessibility.\n</p>\n<p class=\"mb-4\">\nBy engineering graceful degradations, safe fallback handlers, and clear diagnostic telemetry, developers ensure that unexpected conditions do not result in opaque 500 errors, but rather provide constructive, transparent guidance that preserves system integrity.\n</p>', 'RFC 9110 HTTP Semantics, Apache HTTP Server Documentation 2.4, PHP Foundation Manual (Throwable Interface)', 'HTTP 500, Web Architecture, Server Errors, System Resilience, Technology', 'assets/images/article-ai-society.svg', 6, 'published', 12, 1, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`);

INSERT IGNORE INTO `article_categories` (`article_id`, `category_id`) VALUES (17, 6), (17, 5);

SET FOREIGN_KEY_CHECKS = 1;
