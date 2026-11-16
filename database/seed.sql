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

SET FOREIGN_KEY_CHECKS = 1;
