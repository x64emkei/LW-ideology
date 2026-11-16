-- ==============================================================================
-- LAGUNAWISE — IDEOLOGY DIVISION SEED DATA
-- Target Database: lagunawise_ideology
-- ==============================================================================

USE `lagunawise_ideology`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. Seed Roles
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `roles`;
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`) VALUES
(1, 'super_admin', 'Super Administrator', 'Full system control, role assignments, audit logs, and global site governance.'),
(2, 'admin', 'Administrator', 'Content governance, category moderation, writer management, and announcement releases.'),
(3, 'editor', 'Editor', 'Peer review queue, manuscript edits, revision requests, and publishing approval.'),
(4, 'writer', 'Writer / Contributor', 'Article draft drafting, revision submissions, and author profile curation.'),
(5, 'reader', 'Reader', 'Public discourse engagement, commentary, reading lists, and article bookmarks.');

-- ------------------------------------------------------------------------------
-- 2. Seed Users
-- Passwords:
-- admin: admin123!
-- marla.adriano: marla123!
-- michael.ordenes: michael123!
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password_hash`, `bio`, `avatar`, `status`, `must_change_password`, `created_at`) VALUES
(1, 'LagunaWISE Lead Administrator', 'admin', 'admin@lagunawise.org', '$2y$10$X/P1q6.o4s.y9bMjBOaNE.4VD9VMgS0fGY1Xet/QWLVprhl2hHvm6', 'Chief System Administrator for LagunaWISE Ideology Division digital infrastructure and governance.', 'assets/images/avatar-admin.svg', 'active', 1, '2026-10-01 08:00:00'),
(2, 'Marla Adriano', 'marla.adriano', 'marla.adriano@lagunawise.org', '$2y$10$WMRQdZVJlc7L2eL/XzCqlutdzsJuybBkwr6V3AU00sn9aDNvajLH2', 'Editorial Board Member and Division Administrator at LagunaWISE Ideology Division.', 'assets/images/avatar-editor.svg', 'active', 0, '2026-10-05 09:30:00'),
(3, 'Michael Ordenes', 'michael.ordenes', 'michael.ordenes@lagunawise.org', '$2y$10$Ox8m/IEIfeV2PYs7.h/VB./TXQmMGPIAOmGD3g1wfyYpYAQ2oMwpC', 'Editorial Board Member and Research Director at LagunaWISE Ideology Division.', 'assets/images/avatar-writer1.svg', 'active', 0, '2026-10-10 11:15:00');

-- ------------------------------------------------------------------------------
-- 3. Seed User Roles
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `user_roles`;
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1), -- Super Admin
(2, 2), -- Administrator (Marla Adriano)
(3, 2); -- Administrator (Michael Ordenes)

-- ------------------------------------------------------------------------------
-- 4. Seed Categories
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
-- 5. Articles (Cleaned — ready for real editorial submissions)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `article_categories`;
TRUNCATE TABLE `articles`;

-- ------------------------------------------------------------------------------
-- 6. Comments (Cleaned)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `comments`;

-- ------------------------------------------------------------------------------
-- 7. Bookmarks (Cleaned)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `bookmarks`;

-- ------------------------------------------------------------------------------
-- 8. Announcements
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `announcements`;
INSERT INTO `announcements` (`id`, `title`, `slug`, `summary`, `content`, `badge_type`, `is_published`, `published_at`, `created_at`) VALUES
(1, 'LagunaWISE Youth Media Literacy Workshop Series: Calamba City Edition', 'lagunawise-youth-media-literacy-workshop-series', 'An interactive 4-week hybrid seminar empowering student leaders with verification skills and critical thinking toolkits.', 'The Ideology Division of LagunaWISE is thrilled to launch our comprehensive Youth Media Literacy Workshop Series in Calamba City. Over four weeks, student participants will engage in live source-verification simulations, deconstruct algorithmic filter bubbles, and learn how to run community fact-checking desks in their respective educational institutions.', 'campaign', 1, '2026-10-15 08:30:00', '2026-10-14 10:00:00'),
(2, 'Call for Student Research Papers: Contemporary Civic Discourse and Digital Realities', 'call-for-student-research-papers-2026', 'Inviting collegiate researchers across Laguna and CALABARZON to submit interdisciplinary manuscripts on digital citizenship.', 'We invite collegiate researchers, sociologists, and aspiring essayists to submit original papers for our upcoming Ideology Division Annual Monograph. Topics of interest include algorithmic polarization, grassroots community organizing, educational access, and the ethical implementation of artificial intelligence.', 'initiative', 1, '2026-10-25 10:00:00', '2026-10-24 14:00:00'),
(3, 'Community Fact-Checking Toolkit Released for Local Civic Leaders', 'community-fact-checking-toolkit-released', 'A practical, downloadable guide featuring lateral reading checklists and free digital forensics tools.', 'To support grassroots community leaders, educators, and campus journalists, our editorial team has compiled a practical toolkit detailing reverse image searching, geolocation verification, domain registry tracking, and transparent attribution practices.', 'publication', 1, '2026-11-05 09:00:00', '2026-11-03 16:30:00');

-- ------------------------------------------------------------------------------
-- 9. Newsletter Subscribers
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `newsletter_subscribers`;

-- ------------------------------------------------------------------------------
-- 10. Editorial Reviews
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `editorial_reviews`;

-- ------------------------------------------------------------------------------
-- 11. Activity Logs
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `activity_logs`;
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, 1, 'system_init', 'database', 1, '127.0.0.1', 'CLI Seed Process', 'Initial system database schema migration and verified account creation completed.', '2026-10-01 08:00:00');

-- ------------------------------------------------------------------------------
-- 12. Website Settings
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

SET FOREIGN_KEY_CHECKS = 1;
