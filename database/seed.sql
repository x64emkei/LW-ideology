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
-- elena.vance: editor123!
-- marcus.reyes: writer123!
-- sophia.delacruz: writer123!
-- juan.reader: reader123!
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `name`, `username`, `email`, `password_hash`, `bio`, `avatar`, `status`, `must_change_password`, `created_at`) VALUES
(1, 'LagunaWISE Lead Administrator', 'admin', 'admin@lagunawise.org', '$2y$10$X/P1q6.o4s.y9bMjBOaNE.4VD9VMgS0fGY1Xet/QWLVprhl2hHvm6', 'Chief System Administrator for LagunaWISE Ideology Division digital infrastructure and governance.', 'assets/images/avatar-admin.svg', 'active', 1, '2026-10-01 08:00:00'),
(2, 'Elena Vance', 'elena.vance', 'elena.vance@lagunawise.org', '$2y$10$62hdYV45/1CFqP6f88y9vOWmAfwBZMbIaEb3rMx4LM4ImeMslfBs6', 'Senior Editorial Director focusing on media literacy, information integrity, and youth digital advocacy.', 'assets/images/avatar-editor.svg', 'active', 0, '2026-10-05 09:30:00'),
(3, 'Marcus Reyes', 'marcus.reyes', 'marcus.reyes@lagunawise.org', '$2y$10$CcluiIWwtuSQ0nyCBHsju.JbWpI4GkahXAdY0xlwX0j5N2YX4yBpu', 'Research Fellow and social analyst writing on civic engagement, online disinformation, and algorithmic bias.', 'assets/images/avatar-writer1.svg', 'active', 0, '2026-10-10 11:15:00'),
(4, 'Sophia Dela Cruz', 'sophia.delacruz', 'sophia.delacruz@lagunawise.org', '$2y$10$CcluiIWwtuSQ0nyCBHsju.JbWpI4GkahXAdY0xlwX0j5N2YX4yBpu', 'Educator and digital citizenship advocate examining public discourse in contemporary Philippine spaces.', 'assets/images/avatar-writer2.svg', 'active', 0, '2026-10-12 14:00:00'),
(5, 'Juan Dela Cruz', 'juan.reader', 'juan.delacruz@gmail.com', '$2y$10$9sbvXOKNs/7f.RdiFrfnXuYl7vwVAuX.ny0o0cXSiE2iBjI5B8xDu', 'Passionate collegiate student, researcher, and avid reader committed to media literacy and social civic action.', 'assets/images/avatar-reader.svg', 'active', 0, '2026-10-15 16:45:00');

-- ------------------------------------------------------------------------------
-- 3. Seed User Roles
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `user_roles`;
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1), -- Super Admin
(2, 3), -- Editor
(3, 4), -- Writer
(4, 4), -- Writer
(5, 5); -- Reader

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
-- 5. Seed Articles (At least 6 comprehensive editorial pieces)
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `articles`;
INSERT INTO `articles` (`id`, `author_id`, `title`, `slug`, `subtitle`, `excerpt`, `content`, `cover_image`, `reading_time`, `status`, `views_count`, `is_featured`, `published_at`, `created_at`) VALUES
(1, 3, 'Understanding Misinformation in the Digital Age: Architecture of Viral Deception', 'understanding-misinformation-in-the-digital-age', 'How algorithmic distribution, emotive headlines, and cognitive confirmation bias undermine democratic discourse.', 'The modern information ecosystem rewards velocity over veracity. To protect democratic deliberation, citizens must comprehend how disinformation campaigns weaponize algorithmic incentives and human cognitive shortcuts.', '<p class="lead">In an era where digital communication moves at the speed of fiber optics, the greatest challenge facing democratic citizenship is no longer the scarcity of information, but the sheer velocity and precision with which deceptive narratives travel.</p>
<h2>The Structural Incentive for Viral Deception</h2>
<p>Modern social platforms are engineered primarily around engagement maximization. Algorithms measure user dwell time, click-through rates, and emotional resonance. Empirical studies consistently demonstrate that content eliciting intense emotional states—specifically moral outrage, fear, and tribal indignation—is disseminated significantly faster and more broadly than sober, fact-based reporting.</p>
<blockquote>"The danger of disinformation is not simply that people believe a specific lie, but that constant exposure to coordinated falsehoods corrodes public confidence in the very possibility of shared truth."</blockquote>
<h2>Cognitive Vulnerabilities and the Confirmation Trap</h2>
<p>Disinformation rarely operates in a vacuum; it targets established human psychological mechanisms. Among these, confirmation bias remains the most prevalent. When individuals encounter assertions that reinforce their pre-existing grievances or political affiliations, their critical faculties relax. Conversely, counter-evidence is subjected to hyper-skepticism.</p>
<ul>
<li><strong>Confirmation Bias:</strong> Prioritizing data that flatters prior convictions while ignoring verifiable contradictions.</li>
<li><strong>Illusory Truth Effect:</strong> Repeated assertions begin to feel inherently factual due to increased cognitive fluency.</li>
<li><strong>Echo Chambers:</strong> Algorithmic sorting insulates users from constructive contradiction, solidifying radicalized perspectives.</li>
</ul>
<h2>Building Community Resilience and Verification Habits</h2>
<p>Countering this tide requires more than passive fact-checking services; it demands systemic media literacy. Citizens must adopt lateral reading techniques—investigating the source, consulting independent corroboration, and recognizing sponsored emotional appeals before pressing the share button.</p>
<p>As the LagunaWISE Ideology Division champions, a healthy democratic society begins when every citizen treats information consumption as a profound civic responsibility.</p>', 'assets/images/article-misinformation.svg', 7, 'published', 1420, 1, '2026-10-18 10:00:00', '2026-10-16 09:00:00'),

(2, 2, 'Why Media Literacy Matters in Modern Society: The First Line of Intellectual Defense', 'why-media-literacy-matters-in-modern-society', 'Empowering youth and community members with the analytical tools to evaluate, critique, and verify media sources.', 'Media literacy is no longer merely an optional academic skill—it is an indispensable civic survival skill for navigating today\'s saturated digital information landscape.', '<p class="lead">Throughout the twentieth century, literacy was defined primarily as the capacity to read and write printed prose. In the twenty-first century, literacy encompasses the critical ability to analyze, interrogate, and decode multimedia messages across decentralized platforms.</p>
<h2>Deconstructing Media Construction</h2>
<p>Every piece of media content—from a 15-second viral video to an investigative documentary—is an intentional construction. It reflects specific editorial selections, funding sources, cultural assumptions, and ideological lenses. An informed citizen does not consume media passively; they query its architecture:</p>
<ol>
<li>Who authored this message, and what institutional interests do they represent?</li>
<li>Which creative techniques are employed to capture my emotional attention?</li>
<li>How might different social demographics interpret this presentation differently?</li>
<li>Which crucial viewpoints, historical contexts, or dissenting data points have been omitted?</li>
</ol>
<blockquote>"An educated electorate requires more than access to broadband; it requires the intellectual habits of healthy skepticism, source attribution, and patient verification."</blockquote>
<h2>The Role of Educational Institutions in Laguna and Beyond</h2>
<p>In grassroots communities across Laguna, our research reveals that young learners are extraordinarily adept at digital consumption, yet often vulnerable to sophisticated content fabrication. Equipping educators with modular media literacy frameworks transforms classrooms into incubators for rigorous, evidence-based inquiry.</p>
<p>Media literacy transforms the reader from a passive consumer into an active, discerning participant in the democratic process.</p>', 'assets/images/article-medialiteracy.svg', 6, 'published', 980, 1, '2026-10-22 14:30:00', '2026-10-20 11:20:00'),

(3, 4, 'Digital Citizenship and Responsible Online Participation', 'digital-citizenship-and-responsible-online-participation', 'Moving beyond screen time rules toward an ethic of digital empathy, intellectual charity, and civic accountability.', 'The internet is not a consequence-free virtual sandbox; it is the modern public square where our words shape community safety, public trust, and democratic culture.', '<p class="lead">When we log onto digital platforms, we are not merely interacting with silicon and fiber optics; we are participating in an evolving civic collective. The norms we establish today will govern the quality of public discourse for generations to come.</p>
<h2>From Anonymity to Civic Responsibility</h2>
<p>The perceived anonymity of digital communication has frequently bred toxicity, performative conflict, and the dehumanization of intellectual opponents. Responsible digital citizenship calls for the deliberate recovery of intellectual humility and conversational charity.</p>
<p>Practicing ethical digital engagement includes:</p>
<ul>
<li><strong>Proportionality:</strong> Responding to ideas rather than attacking personal identities.</li>
<li><strong>Attribution:</strong> Crediting original journalists, researchers, and creators faithfully.</li>
<li><strong>Verification Before Amplification:</strong> Refusing to repost sensational rumors or unverified accusations.</li>
<li><strong>Data Sovereignty:</strong> Safeguarding personal privacy and respecting the confidentiality of others.</li>
</ul>
<blockquote>"Digital citizenship means recognizing that behind every username and profile avatar is a human being deserving of reasoned dialogue and fundamental dignity."</blockquote>
<p>By cultivating proactive digital stewardship in our local communities, we foster an online environment characterized by mutual respect, intellectual exploration, and genuine civic progress.</p>', 'assets/images/article-digitalcitizenship.svg', 5, 'published', 740, 0, '2026-10-26 16:15:00', '2026-10-24 13:40:00'),

(4, 3, 'Critical Thinking in an Age of Information Overload', 'critical-thinking-in-an-age-of-information-overload', 'Cultivating intellectual patience and rigorous mental models when data streams threaten cognitive clarity.', 'When every minute brings hundreds of competing alerts, true intelligence is measured by the ability to filter noise, test foundational assumptions, and deliberate deeply.', '<p class="lead">We are drowning in information while starving for wisdom. The sheer volume of notifications and fragmented commentary fragments human attention and undermines contemplative reasoning.</p>
<h2>The Fallacy of Speed in Deliberation</h2>
<p>Social media celebrates instantaneous reactions. Breaking news alerts demand rapid opinions before factual ground truths can be established. Yet critical thinking is inherently a slow, deliberate discipline that requires pausing to test premises.</p>
<h2>Essential Intellectual Habits for the Modern Thinker</h2>
<p>Developing robust critical thinking involves several disciplined mental habits:</p>
<ol>
<li><strong>Steel-Manning Opposing Views:</strong> Articulating an intellectual opponent\'s perspective in its strongest possible form before offering a critique.</li>
<li><strong>Identifying Logical Fallacies:</strong> Spotting ad hominem attacks, false dichotomies, and correlation-causation conflations in public discourse.</li>
<li><strong>Epistemic Humility:</strong> Acknowledging the limits of one\'s own expertise and remaining open to empirical correction.</li>
</ol>
<blockquote>"The mark of an educated mind is the ability to entertain a thought without accepting it, and to interrogate one\'s own cherished assumptions with rigorous honesty."</blockquote>
<p>At the LagunaWISE Ideology Division, we encourage students and community advocates to value precision and depth over hurried certainty.</p>', 'assets/images/article-criticalthinking.svg', 6, 'published', 1150, 0, '2026-10-30 09:45:00', '2026-10-28 10:10:00'),

(5, 4, 'Understanding Social Issues Through Multiple Perspectives', 'understanding-social-issues-through-multiple-perspectives', 'Transcending binary thinking to unpack systemic complexity, historical nuance, and economic interdependencies.', 'Complex societal challenges—from urban housing to educational equity—cannot be solved with simplistic political sloganeering or binary polarization.', '<p class="lead">Public debate in the modern era is frequently reduced to simplistic binaries: heroes versus villains, tradition versus progress, state intervention versus market fundamentalism. Yet real-world societal problems are characterized by intricate systemic trade-offs.</p>
<h2>The Trap of Binary Simplification</h2>
<p>Binary frameworks offer comforting cognitive simplicity, but they consistently obscure root causes. Consider urban development in developing provinces like Laguna. Striking a sustainable balance between industrial growth, agricultural preservation, flood resilience, and affordable housing requires multidisciplinary analysis.</p>
<h2>Framework for Multi-Perspective Inquiry</h2>
<ul>
<li><strong>Historical Context:</strong> How did past policy decisions, colonial legal legacies, and demographic shifts shape the present circumstance?</li>
<li><strong>Economic Incentives:</strong> Who benefits financially from the status quo, and who bears the externalized environmental or social costs?</li>
<li><strong>Human Experience:</strong> What does lived experience reveal that statistical aggregations frequently hide?</li>
</ul>
<blockquote>"To understand deeply is to accept that most serious social challenges involve genuine conflicts of legitimate values, requiring nuanced deliberation rather than dogmatic pronouncements."</blockquote>
<p>By fostering forums where diverse community voices can deliberate respectfully, we lay the groundwork for sustainable, participatory public governance.</p>', 'assets/images/article-socialissues.svg', 8, 'published', 620, 0, '2026-11-04 11:20:00', '2026-11-01 15:30:00'),

(6, 3, 'Artificial Intelligence and Its Influence on Society: Autonomy, Labor, and Truth', 'artificial-intelligence-and-its-influence-on-society', 'Navigating generative media, synthetic disinformation, algorithmic justice, and the future of human intellectual agency.', 'As generative AI systems mature, questions of copyright, authentic human voice, synthetic video deception, and cognitive deskilling demand urgent civic attention.', '<p class="lead">Artificial Intelligence has transitioned from speculative science fiction into the core infrastructure of daily communication, educational assessment, and economic production.</p>
<h2>The Dual Edge of Generative Technologies</h2>
<p>Large language models and multimodal diffusion systems present extraordinary opportunities for personalized learning and creative exploration. However, their democratization also drastically lowers the cost of producing plausible, tailored disinformation at industrial scale.</p>
<h2>Key Societal Fronts for Civic Oversight</h2>
<ol>
<li><strong>Epistemic Security and Deepfakes:</strong> The proliferation of hyper-realistic synthetic audio and video threatens to undermine evidentiary standards in public reporting and legal systems.</li>
<li><strong>Algorithmic Discrimination:</strong> Automated decision systems trained on historical data sets frequently replicate and amplify existing systemic inequalities.</li>
<li><strong>Cognitive Deskilling:</strong> Over-reliance on automated synthesis risks weakening fundamental human skills in synthesis, writing, and logical argumentation.</li>
</ol>
<blockquote>"Technology is neither inherently good nor bad; nor is it neutral. It reflects the values, blind spots, and economic motivations of the societies that construct and deploy it."</blockquote>
<p>The Ideology Division invites researchers, technologists, and community advocates to participate in establishing ethical guardrails that preserve human dignity in an increasingly automated world.</p>', 'assets/images/article-ai-society.svg', 9, 'published', 1890, 1, '2026-11-10 13:00:00', '2026-11-08 17:00:00'),

(7, 3, 'Philosophical Inquiries into Digital Identity and Moral Agency', 'philosophical-inquiries-into-digital-identity-and-moral-agency', 'Exploring the metaphysical and ethical questions of existing across physical and virtual spaces.', 'Draft manuscript examining how online personas affect moral accountability and genuine human connection.', '<p class="lead">A preliminary exploration into digital existence...</p><p>This manuscript is currently undergoing editorial review and internal refinement by the Ideology Division working group.</p>', 'assets/images/article-philosophy.svg', 4, 'pending_review', 0, 0, NULL, '2026-11-14 09:00:00'),

(8, 4, 'Cultivating Community Dialogue Circles in Rural Laguna', 'cultivating-community-dialogue-circles-in-rural-laguna', 'Grassroots methodologies for holding productive town-hall discussions across generational divides.', 'Early draft detailing grassroots methodologies for intergenerational civil discourse workshops.', '<p class="lead">Draft methodology notes...</p><p>Documenting initial workshop results from Barangay Barandal and neighboring communities.</p>', 'assets/images/article-dialogue.svg', 4, 'draft', 0, 0, NULL, '2026-11-15 10:00:00');

-- ------------------------------------------------------------------------------
-- 6. Seed Article Categories
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `article_categories`;
INSERT INTO `article_categories` (`article_id`, `category_id`) VALUES
(1, 1), (1, 2), -- Misinformation: Media Literacy, Misinformation
(2, 1), (2, 8), -- Media Literacy: Media Literacy, Education
(3, 4), (3, 3), -- Digital Citizenship: Digital Citizenship, Social Issues
(4, 5), (4, 1), -- Critical Thinking: Critical Thinking, Media Literacy
(5, 3), (5, 7), -- Social Issues: Social Issues, Philosophy
(6, 6), (6, 2), -- AI and Society: Tech & Society, Misinformation
(7, 7), (7, 6), -- Philosophical Inquiries: Philosophy, Tech & Society
(8, 8), (8, 3); -- Community Dialogue: Education, Social Issues

-- ------------------------------------------------------------------------------
-- 7. Seed Comments
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `comments`;
INSERT INTO `comments` (`id`, `article_id`, `user_id`, `author_name`, `author_email`, `content`, `status`, `created_at`) VALUES
(1, 1, 5, 'Juan Dela Cruz', 'juan.delacruz@gmail.com', 'This analysis hits the nail on the head regarding algorithmic incentives. We often blame individuals for sharing fake news without addressing the platform architecture designed to harvest rage.', 'approved', '2026-10-19 11:24:00'),
(2, 1, NULL, 'Prof. Maria Santos', 'maria.santos@edu.ph', 'Excellent emphasis on lateral reading. I am incorporating these exact verification steps into my undergraduate research syllabus this semester.', 'approved', '2026-10-20 15:40:00'),
(3, 2, 5, 'Juan Dela Cruz', 'juan.delacruz@gmail.com', 'The 4 questions for media deconstruction should be taught to students as early as high school. Inspiring work by the LagunaWISE team!', 'approved', '2026-10-23 09:12:00'),
(4, 6, 5, 'Juan Dela Cruz', 'juan.delacruz@gmail.com', 'The distinction between cognitive deskilling and practical AI adoption is very sharp. We must not surrender our capacity for original thought.', 'approved', '2026-11-11 16:30:00');

-- ------------------------------------------------------------------------------
-- 8. Seed Bookmarks
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `bookmarks`;
INSERT INTO `bookmarks` (`id`, `user_id`, `article_id`, `created_at`) VALUES
(1, 5, 1, '2026-10-19 12:00:00'),
(2, 5, 2, '2026-10-23 10:15:00'),
(3, 5, 6, '2026-11-11 17:00:00');

-- ------------------------------------------------------------------------------
-- 9. Seed Announcements
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `announcements`;
INSERT INTO `announcements` (`id`, `title`, `slug`, `summary`, `content`, `badge_type`, `is_published`, `published_at`, `created_at`) VALUES
(1, 'LagunaWISE Youth Media Literacy Workshop Series: Calamba City Edition', 'lagunawise-youth-media-literacy-workshop-series', 'An interactive 4-week hybrid seminar empowering student leaders with verification skills and critical thinking toolkits.', 'The Ideology Division of LagunaWISE is thrilled to launch our comprehensive Youth Media Literacy Workshop Series in Calamba City. Over four weeks, student participants will engage in live source-verification simulations, deconstruct algorithmic filter bubbles, and learn how to run community fact-checking desks in their respective educational institutions.', 'campaign', 1, '2026-10-15 08:30:00', '2026-10-14 10:00:00'),

(2, 'Call for Student Research Papers: Contemporary Civic Discourse and Digital Realities', 'call-for-student-research-papers-2026', 'Inviting collegiate researchers across Laguna and CALABARZON to submit interdisciplinary manuscripts on digital citizenship.', 'We invite collegiate researchers, sociologists, and aspiring essayists to submit original papers for our upcoming Ideology Division Annual Monograph. Topics of interest include algorithmic polarization, grassroots community organizing, educational access, and the ethical implementation of artificial intelligence.', 'initiative', 1, '2026-10-25 10:00:00', '2026-10-24 14:00:00'),

(3, 'Community Fact-Checking Toolkit Released for Local Civic Leaders', 'community-fact-checking-toolkit-released', 'A practical, downloadable guide featuring lateral reading checklists and free digital forensics tools.', 'To support grassroots community leaders, educators, and campus journalists, our editorial team has compiled a practical toolkit detailing reverse image searching, geolocation verification, domain registry tracking, and transparent attribution practices.', 'publication', 1, '2026-11-05 09:00:00', '2026-11-03 16:30:00');

-- ------------------------------------------------------------------------------
-- 10. Seed Newsletter Subscribers
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `newsletter_subscribers`;
INSERT INTO `newsletter_subscribers` (`id`, `email`, `status`, `token`, `subscribed_at`) VALUES
(1, 'juan.delacruz@gmail.com', 'subscribed', 'tok_7f9a2b8e3c1d4a5b6c7d8e9f0a1b2c3d', '2026-10-15 16:50:00'),
(2, 'maria.santos@edu.ph', 'subscribed', 'tok_4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a', '2026-10-20 15:45:00'),
(3, 'researcher.laguna@outlook.com', 'subscribed', 'tok_1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d', '2026-11-02 11:20:00');

-- ------------------------------------------------------------------------------
-- 11. Seed Editorial Reviews
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `editorial_reviews`;
INSERT INTO `editorial_reviews` (`id`, `article_id`, `reviewer_id`, `action`, `feedback`, `created_at`) VALUES
(1, 1, 2, 'approved', 'Exceptional depth of analysis on algorithmic amplification. Approved for immediate feature publication.', '2026-10-17 14:00:00'),
(2, 2, 2, 'published', 'Crisp pedagogical framing. Formatted and published with featured banner.', '2026-10-22 14:00:00'),
(3, 7, 2, 'submitted', 'Manuscript received from Marcus Reyes. Awaiting secondary review on philosophical ethics.', '2026-11-14 11:00:00');

-- ------------------------------------------------------------------------------
-- 12. Seed Activity Logs
-- ------------------------------------------------------------------------------
TRUNCATE TABLE `activity_logs`;
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, 1, 'system_init', 'database', 1, '127.0.0.1', 'CLI Seed Process', 'Initial system database schema migration and demonstration seeding completed successfully.', '2026-10-01 08:00:00'),
(2, 2, 'article_publish', 'article', 1, '127.0.0.1', 'Mozilla/5.0 Antigravity Browser', 'Published article: Understanding Misinformation in the Digital Age', '2026-10-18 10:00:00'),
(3, 2, 'article_publish', 'article', 2, '127.0.0.1', 'Mozilla/5.0 Antigravity Browser', 'Published article: Why Media Literacy Matters in Modern Society', '2026-10-22 14:30:00');

-- ------------------------------------------------------------------------------
-- 13. Seed Website Settings
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
