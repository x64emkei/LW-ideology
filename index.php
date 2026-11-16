<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$pageTitle = 'LagunaWISE — Ideology Division | Critical Thinking & Media Literacy';
$pageDescription = 'The Ideology Division of LagunaWISE is a digital publication platform dedicated to fostering critical thinking, media literacy, informed public discourse, and digital citizenship.';

$db = Database::getConnection();

// Fetch Hero Featured Article
$heroStmt = $db->query("
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published' AND a.is_featured = 1
    ORDER BY a.published_at DESC
    LIMIT 1
");
$featuredArticle = $heroStmt->fetch();

// Fetch Latest Published Articles (excluding featured if exists)
$articlesQuery = "
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
           (SELECT c.slug FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_slug,
           (SELECT c.color_hex FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_color
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published'
    ORDER BY a.published_at DESC
    LIMIT 6
";
$latestArticles = $db->query($articlesQuery)->fetchAll();

// Fetch Featured Categories / Topics with article counts
$topicsStmt = $db->query("
    SELECT c.*, COUNT(ac.article_id) AS article_count
    FROM categories c
    LEFT JOIN article_categories ac ON c.id = ac.category_id
    LEFT JOIN articles a ON ac.article_id = a.id AND a.status = 'published'
    WHERE c.is_featured = 1
    GROUP BY c.id
    ORDER BY c.display_order ASC
    LIMIT 6
");
$featuredTopics = $topicsStmt->fetchAll();

// Fetch Recent Announcements / News
$newsStmt = $db->query("
    SELECT *
    FROM announcements
    WHERE is_published = 1
    ORDER BY published_at DESC
    LIMIT 3
");
$latestNews = $newsStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Editorial Hero Section -->
<section class="relative bg-navy-900 text-white overflow-hidden border-b border-navy-800">
    <!-- Subtle geometric background grid -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-lwblue-light/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-lwblue/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 lg:py-28 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-navy-800/90 border border-navy-700 text-xs font-semibold tracking-wide text-lwblue-ice">
                    <span class="w-2 h-2 rounded-full bg-lwblue-light animate-ping"></span>
                    <span>INTELLECTUAL &amp; EDUCATIONAL DIVISION</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                    Understanding Ideas.<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-lwblue-ice via-lwblue-light to-blue-400">Challenging Perspectives.</span><br>
                    Empowering Minds.
                </h1>

                <p class="text-base sm:text-lg text-slate-300 font-normal leading-relaxed max-w-2xl">
                    The Ideology Division of LagunaWISE is a dedicated platform for informed perspectives, critical conversations, and meaningful engagement with the ideas shaping our communities and contemporary society.
                </p>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="<?= url('pages/articles.php') ?>" 
                       class="inline-flex items-center gap-2.5 px-6 py-3.5 bg-lwblue hover:bg-lwblue-hover text-white text-sm font-bold rounded-xl transition duration-150 shadow-lg shadow-lwblue/25">
                        <span>Explore Articles</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <a href="<?= url('pages/about.php') ?>" 
                       class="inline-flex items-center gap-2 px-6 py-3.5 bg-navy-800/90 hover:bg-navy-700 border border-navy-700 text-slate-200 hover:text-white text-sm font-semibold rounded-xl transition duration-150">
                        <i class="fa-solid fa-compass text-lwblue-light text-xs"></i>
                        <span>Discover Our Advocacy</span>
                    </a>
                </div>

                <div class="pt-6 border-t border-navy-800/80 flex flex-wrap items-center gap-6 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check-double text-lwblue-light"></i>
                        <span>Nonpartisan Research</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-lwblue-light"></i>
                        <span>Media Literacy Toolkits</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-users text-lwblue-light"></i>
                        <span>Digital Citizenship</span>
                    </div>
                </div>
            </div>

            <!-- Right Hero Card: Spotlight Article / Official Emblem Banner -->
            <div class="lg:col-span-5">
                <?php if ($featuredArticle): ?>
                    <article class="bg-navy-800/80 border border-navy-700 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-sm group hover:border-lwblue-light/50 transition duration-300">
                        <div class="relative aspect-video overflow-hidden bg-navy-950">
                            <img src="<?= asset($featuredArticle['cover_image'] ?? 'assets/images/article-misinformation.svg') ?>" 
                                 alt="<?= e($featuredArticle['title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-3 left-3 bg-lwblue text-white text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded shadow">
                                Editorial Spotlight
                            </span>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <span><?= e(format_date($featuredArticle['published_at'])) ?></span>
                                <span>•</span>
                                <span><?= (int)$featuredArticle['reading_time'] ?> min read</span>
                            </div>
                            <h2 class="text-lg font-bold text-white group-hover:text-lwblue-light transition line-clamp-2">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($featuredArticle['slug'])) ?>">
                                    <?= e($featuredArticle['title']) ?>
                                </a>
                            </h2>
                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">
                                <?= e($featuredArticle['excerpt']) ?>
                            </p>
                            <div class="pt-3 flex items-center justify-between border-t border-navy-700/60">
                                <span class="text-xs text-slate-400">By <?= e($featuredArticle['author_name']) ?></span>
                                <a href="<?= url('pages/article.php?slug=' . urlencode($featuredArticle['slug'])) ?>" 
                                   class="text-xs font-semibold text-lwblue-light hover:text-white flex items-center gap-1">
                                    <span>Read Insight</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php else: ?>
                    <div class="bg-navy-800 border border-navy-700 p-8 rounded-2xl text-center space-y-4">
                        <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="LagunaWISE Emblem" class="w-24 h-24 mx-auto object-contain">
                        <h2 class="text-xl font-bold text-white">LagunaWISE Ideology Division</h2>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Empowering minds through media literacy, analytical reasoning, and informed community participation.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<!-- 2. Division Introduction: An Informed Society Begins with Critical Thinking -->
<section class="py-16 sm:py-20 bg-white border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="max-w-3xl mx-auto text-center space-y-4">
            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Our Intellectual Mission</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">
                An Informed Society Begins with Critical Thinking.
            </h2>
            <div class="w-16 h-1 bg-lwblue rounded-full mx-auto"></div>
            <p class="text-sm sm:text-base text-editorial-text leading-relaxed font-normal">
                The Ideology Division serves as the intellectual cornerstone of LagunaWISE. We believe that addressing modern social challenges requires more than fast-paced headlines—it demands rigorous inquiry, media literacy, open dialogue across perspectives, and an enduring commitment to truth.
            </p>
        </div>

        <!-- 4 Pillars Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
            <div class="bg-surface p-6 rounded-2xl border border-slate-200/80 hover:border-lwblue/50 hover:shadow-md transition duration-200 group">
                <div class="w-12 h-12 rounded-xl bg-lwblue-ice text-lwblue flex items-center justify-center text-lg mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Critical Thinking</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Cultivating logical reasoning, identifying deceptive fallacies, and learning how to evaluate arguments based on evidence rather than rhetoric.
                </p>
            </div>

            <div class="bg-surface p-6 rounded-2xl border border-slate-200/80 hover:border-lwblue/50 hover:shadow-md transition duration-200 group">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Media Literacy</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Equipping students, educators, and citizens with the verification toolkits needed to deconstruct viral disinformation and sensationalist framing.
                </p>
            </div>

            <div class="bg-surface p-6 rounded-2xl border border-slate-200/80 hover:border-lwblue/50 hover:shadow-md transition duration-200 group">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Digital Citizenship</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Championing responsible online engagement, empathy in digital debate, source attribution, and ethical community participation.
                </p>
            </div>

            <div class="bg-surface p-6 rounded-2xl border border-slate-200/80 hover:border-lwblue/50 hover:shadow-md transition duration-200 group">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Informed Discourse</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Fostering respectful, multi-perspective inquiry into social issues, public governance, and the sociotechnical future of our nation.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 3. Featured Articles & Insights Section -->
<section class="py-16 sm:py-20 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Digital Publication</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight mt-1">
                    Featured Articles &amp; Insights
                </h2>
                <p class="text-xs sm:text-sm text-editorial-muted mt-1">
                    Peer-reviewed editorial monographs, research commentaries, and analytical investigations.
                </p>
            </div>
            <a href="<?= url('pages/articles.php') ?>" class="inline-flex items-center gap-2 text-xs font-bold text-lwblue hover:text-navy-900 transition">
                <span>View All Articles (<?= count($latestArticles) ?>)</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        </div>

        <!-- Articles Grid -->
        <?php if (empty($latestArticles)): ?>
            <div class="bg-white rounded-3xl border border-slate-200/90 p-12 text-center max-w-xl mx-auto shadow-xs space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-lwblue flex items-center justify-center text-2xl mx-auto">
                    <i class="fa-regular fa-newspaper"></i>
                </div>
                <h3 class="text-lg font-bold text-navy-900">Forthcoming Publications Underway</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Our editorial team is currently preparing upcoming monographs, analytical investigations, and research commentaries. Check back soon or visit our Announcements for the latest updates.
                </p>
                <div class="pt-2">
                    <a href="<?= url('pages/news.php') ?>" class="inline-block px-5 py-2.5 bg-navy-900 text-white text-xs font-bold rounded-xl hover:bg-navy-800 transition">
                        View Announcements
                    </a>
                </div>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($latestArticles as $article): ?>
                <article class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col group">
                    <div class="relative aspect-video overflow-hidden bg-slate-900">
                        <img src="<?= asset($article['cover_image'] ?? 'assets/images/article-medialiteracy.svg') ?>" 
                             alt="<?= e($article['title']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php if (!empty($article['category_name'])): ?>
                            <span class="absolute top-3 left-3 bg-navy-900/90 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded shadow-sm">
                                <?= e($article['category_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-[11px] text-editorial-muted">
                                <span class="font-medium"><?= e(format_date($article['published_at'])) ?></span>
                                <span class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span><?= (int)$article['reading_time'] ?> min</span>
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition leading-snug line-clamp-2">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>">
                                    <?= e($article['title']) ?>
                                </a>
                            </h3>

                            <p class="text-xs text-editorial-muted line-clamp-3 leading-relaxed">
                                <?= e($article['excerpt']) ?>
                            </p>
                        </div>

                        <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <img src="<?= asset($article['author_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                                     alt="<?= e($article['author_name']) ?>" 
                                     class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                <span class="text-xs font-semibold text-slate-800"><?= e($article['author_name']) ?></span>
                            </div>

                            <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>" 
                               class="text-xs font-bold text-lwblue group-hover:text-navy-900 flex items-center gap-1">
                                <span>Read</span>
                                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mt-12">
            <a href="<?= url('pages/articles.php') ?>" 
               class="inline-flex items-center gap-2 px-8 py-3.5 bg-white border border-slate-300 hover:border-lwblue text-navy-900 hover:text-lwblue font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition">
                <span>Browse Full Editorial Archive</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        </div>
    </div>
</section>

<!-- 4. Featured Topics / Inquiry Pillars -->
<section class="py-16 sm:py-20 bg-white border-t border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Research Pillars</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">
                Explore Core Inquiry Topics
            </h2>
            <p class="text-xs sm:text-sm text-editorial-muted">
                Navigate curated collections examining the pressing intellectual and civic themes of our time.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredTopics as $topic): ?>
                <a href="<?= url('pages/topic.php?slug=' . urlencode($topic['slug'])) ?>" 
                   class="group bg-surface p-6 rounded-2xl border border-slate-200/80 hover:border-lwblue hover:shadow-lg transition duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-lwblue group-hover:bg-lwblue group-hover:text-white transition-colors">
                                <i class="fa-solid fa-hashtag text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-white px-2.5 py-1 rounded-full border border-slate-200">
                                <?= (int)$topic['article_count'] ?> articles
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition mb-2">
                            <?= e($topic['name']) ?>
                        </h3>
                        <p class="text-xs text-editorial-muted leading-relaxed line-clamp-2">
                            <?= e($topic['description']) ?>
                        </p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-200/60 flex items-center justify-between text-xs font-semibold text-lwblue">
                        <span>Explore Topic</span>
                        <i class="fa-solid fa-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. Editorial Statement / Advocacy Banner -->
<section class="py-20 bg-gradient-to-br from-navy-900 via-navy-800 to-navy-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[radial-gradient(#FFFFFF_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center relative z-10 space-y-6">
        <span class="inline-block px-3 py-1 rounded-full bg-lwblue/30 border border-lwblue-light/30 text-xs font-bold tracking-widest text-lwblue-ice uppercase">
            LagunaWISE Ideology Division Charter
        </span>
        <blockquote class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight font-heading">
            “Think Critically. Question Responsibly. Understand Deeply.”
        </blockquote>
        <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
            We are dedicated to building a culture of intellectual inquiry where ideas are evaluated with rigor, disagreements are handled with civility, and every community member is empowered with the skills of an independent digital citizen.
        </p>
        <div class="pt-4">
            <a href="<?= url('pages/about.php') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-navy-900 hover:bg-slate-100 font-bold text-xs uppercase tracking-wider rounded-xl transition shadow">
                <span>Read Our Full Charter &amp; Principles</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</section>

<!-- 6. News & Organizational Updates -->
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Division Initiatives</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight mt-1">
                    News &amp; Community Updates
                </h2>
                <p class="text-xs sm:text-sm text-editorial-muted mt-1">
                    Announcements, workshops, educational campaigns, and research initiatives from Calamba City.
                </p>
            </div>
            <a href="<?= url('pages/news.php') ?>" class="inline-flex items-center gap-2 text-xs font-bold text-lwblue hover:text-navy-900 transition">
                <span>All Updates</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($latestNews as $item): ?>
                <div class="bg-surface p-6 rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-sm flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-lwblue-ice text-lwblue">
                                <?= e($item['badge_type']) ?>
                            </span>
                            <span class="text-slate-400"><?= e(format_date($item['published_at'])) ?></span>
                        </div>
                        <h3 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition leading-snug">
                            <?= e($item['title']) ?>
                        </h3>
                        <p class="text-xs text-editorial-muted leading-relaxed line-clamp-3">
                            <?= e($item['summary']) ?>
                        </p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-200/60">
                        <a href="<?= url('pages/news.php#news-' . (int)$item['id']) ?>" class="text-xs font-bold text-lwblue hover:text-navy-900 flex items-center gap-1">
                            <span>Read Announcement</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 7. Prominent Newsletter Subscription Section -->
<section class="py-16 bg-navy-900 border-t border-navy-800 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="bg-gradient-to-r from-navy-800 to-navy-700/80 p-8 sm:p-12 rounded-3xl border border-navy-600/60 shadow-2xl relative overflow-hidden">
            <div class="max-w-2xl mx-auto text-center space-y-4">
                <span class="text-xs font-bold tracking-widest text-lwblue-light uppercase">Stay Informed</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Subscribe to the Ideology Division Dispatch
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal">
                    Join researchers, educators, and young leaders receiving our fortnightly dispatches on media literacy, civic toolkits, and critical thought.
                </p>

                <form action="<?= url('actions/newsletter-action.php') ?>" method="POST" class="pt-4 flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                    <?= Security::csrfField() ?>
                    <input type="email" 
                           name="email" 
                           required 
                           placeholder="Enter your academic or personal email" 
                           class="flex-1 text-xs px-4 py-3 bg-navy-900/90 border border-navy-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-lwblue-light focus:ring-1 focus:ring-lwblue-light">
                    <button type="submit" 
                            class="px-6 py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow duration-150">
                        Subscribe Now
                    </button>
                </form>

                <p class="text-[11px] text-slate-400">
                    We honor your digital privacy. Unsubscribe anytime with a single click.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
