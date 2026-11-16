<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$pageTitle = 'LagunaWISE — Ideology Division | Editorial News & Public Discourse';
$pageDescription = 'The Ideology Division of LagunaWISE is an intellectual digital publication and news platform dedicated to critical thinking, media literacy, informed discourse, and digital citizenship.';

$db = Database::getConnection();

// Fetch Hero Featured / Headline Article
$heroStmt = $db->query("
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
           (SELECT c.slug FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_slug,
           (SELECT c.color_hex FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_color
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published' AND a.is_featured = 1
    ORDER BY a.published_at DESC
    LIMIT 1
");
$featuredArticle = $heroStmt->fetch();

// If no featured article specifically marked, get the newest published one for the main headline
if (!$featuredArticle) {
    $headlineStmt = $db->query("
        SELECT a.*, u.name AS author_name, u.avatar AS author_avatar,
               (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
               (SELECT c.slug FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_slug,
               (SELECT c.color_hex FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_color
        FROM articles a
        JOIN users u ON a.author_id = u.id
        WHERE a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT 1
    ");
    $featuredArticle = $headlineStmt->fetch();
}

// Fetch Latest Published Articles (for news columns and topic sections)
$articlesQuery = "
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
           (SELECT c.slug FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_slug,
           (SELECT c.color_hex FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_color
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published'
    ORDER BY a.published_at DESC
    LIMIT 12
";
$latestArticles = $db->query($articlesQuery)->fetchAll();

// Fetch Secondary Lead (second latest article if exists)
$secondaryArticle = null;
if (count($latestArticles) > 1) {
    $secondaryArticle = $latestArticles[1];
}

// Fetch Featured Categories / Topics with article counts
$topicsStmt = $db->query("
    SELECT c.*, COUNT(ac.article_id) AS article_count
    FROM categories c
    LEFT JOIN article_categories ac ON c.id = ac.category_id
    LEFT JOIN articles a ON ac.article_id = a.id AND a.status = 'published'
    WHERE c.is_featured = 1
    GROUP BY c.id
    ORDER BY c.display_order ASC
    LIMIT 8
");
$featuredTopics = $topicsStmt->fetchAll();

// Fetch Recent Announcements / News
$newsStmt = $db->query("
    SELECT *
    FROM announcements
    WHERE is_published = 1
    ORDER BY published_at DESC
    LIMIT 4
");
$latestNews = $newsStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. CNN-Style Breaking Dispatch / Alert Bar -->
<div class="bg-navy-950 border-b border-navy-800 text-white text-xs" style="background-color: #030D22;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-3 overflow-hidden">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-sm bg-red-600 text-white font-extrabold uppercase tracking-wider text-[10px] shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                THE LATEST
            </span>
            <p class="truncate text-slate-200 font-medium text-[11px] sm:text-xs">
                <?php if (!empty($latestNews)): ?>
                    <a href="<?= url('pages/news.php') ?>" class="hover:text-lwblue-light transition font-semibold text-white">
                        <?= e($latestNews[0]['title']) ?>
                    </a>
                <?php else: ?>
                    <span>LagunaWISE Ideology Division: Dedicated to Media Literacy, Critical Thinking, and Responsible Public Discourse.</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="flex items-center gap-4 text-[11px] text-slate-300 shrink-0 self-end sm:self-auto font-mono">
            <span><?= date('l, F j, Y') ?></span>
            <span class="hidden md:inline">•</span>
            <span class="hidden md:inline text-lwblue-ice">Laguna, PH</span>
        </div>
    </div>
</div>

<!-- 2. LagunaWISE Hero Header (Crisp Editorial Geometry with High Contrast) -->
<section class="relative bg-navy-900 text-white overflow-hidden border-b border-navy-800" style="background-color: #061638;">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-lwblue-light/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-lwblue/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 lg:py-12 relative z-10 animate-fade-in">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            
            <!-- Left Hero Identity -->
            <div class="space-y-4 max-w-2xl text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-navy-800 border border-navy-700 text-xs font-semibold tracking-wide text-lwblue-ice">
                    <span class="w-2 h-2 rounded-full bg-lwblue-light animate-ping"></span>
                    <span>INTELLECTUAL &amp; EDITORIAL DIVISION</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight font-heading">
                    Understanding Ideas. <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-lwblue-ice via-lwblue-light to-blue-400">Challenging Perspectives.</span> <br class="hidden sm:inline">
                    Empowering Minds.
                </h1>

                <p class="text-xs sm:text-sm text-slate-200 font-normal leading-relaxed">
                    The Ideology Division of LagunaWISE is an investigative digital publication and discourse portal examining misinformation, public governance, media literacy, and democratic participation.
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-1">
                    <a href="<?= url('pages/articles.php') ?>" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-md transition duration-150 shadow-sm">
                        <span>Read Latest Dispatches</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                    <a href="<?= url('auth/register.php') ?>" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-navy-800 hover:bg-navy-700 border border-navy-700 text-slate-100 hover:text-white text-xs font-semibold rounded-md transition duration-150">
                        <i class="fa-solid fa-pen-nib text-lwblue-light text-[11px]"></i>
                        <span>Submit a Manuscript</span>
                    </a>
                </div>
            </div>

            <!-- Right Hero Emblem & Quick Metric Badge (Reduced Roundedness) -->
            <div class="shrink-0 flex flex-col items-center justify-center bg-navy-800/80 border border-navy-700 rounded-lg p-5 text-center w-full sm:w-72 shadow-md">
                <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="LagunaWISE Emblem" class="w-16 h-16 object-contain drop-shadow mb-2.5">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-100">LagunaWISE Ideology</span>
                <span class="text-[11px] text-slate-300 mt-0.5">Editorial Board • Calamba City, Laguna</span>
                
                <div class="grid grid-cols-2 gap-3 w-full mt-4 pt-4 border-t border-navy-700/80 text-center">
                    <div>
                        <span class="block text-lg font-extrabold text-lwblue-light"><?= count($featuredTopics) ?></span>
                        <span class="text-[10px] text-slate-300 uppercase tracking-wider font-semibold">Pillars</span>
                    </div>
                    <div>
                        <span class="block text-lg font-extrabold text-emerald-400"><?= count($latestArticles) ?></span>
                        <span class="text-[10px] text-slate-300 uppercase tracking-wider font-semibold">Live Articles</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 3. CNN-STYLE MAIN NEWSPAGE & HEADLINE PREVIEW GRID -->
<section class="py-10 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <!-- Mobile Horizontal Topic Ribbon (Thumb-Friendly Swipe Carousel) -->
        <div class="lg:hidden mb-6 w-full max-w-full overflow-x-auto no-scrollbar scroll-smooth flex items-center gap-2 py-1">
            <a href="<?= url('pages/articles.php') ?>" 
               class="px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0 bg-navy-900 text-white shadow-xs">
                All Dispatches
            </a>
            <?php foreach ($featuredTopics as $top): ?>
                <a href="<?= url('pages/topic.php?slug=' . urlencode($top['slug'])) ?>" 
                   class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 bg-surface hover:bg-slate-200 text-slate-700 border border-slate-200 transition flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: <?= e($top['color_hex'] ?? '#1557A6') ?>;"></span>
                    <span><?= e($top['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- CNN-Style Section Header Bar -->
        <div class="border-b-2 border-navy-900 pb-2 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 bg-red-600 rounded-xs"></span>
                <h2 class="text-xl sm:text-2xl font-black uppercase tracking-tight text-navy-900 font-heading">
                    Top Stories &amp; Editorial Inquiries
                </h2>
            </div>
            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider hidden sm:inline">
                Live Editorial Wire
            </span>
        </div>

        <!-- 3-Column CNN Newsroom Layout: [Left Rail] [Center Lead Headline] [Right Secondary Spotlight] -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN: "News & Critical Views" (CNN Left Rail) -->
            <div class="lg:col-span-3 space-y-5 order-2 lg:order-1 border-t lg:border-t-0 lg:border-r border-slate-200 pt-6 lg:pt-0 lg:pr-6">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-navy-900 pb-2 border-b border-slate-300 flex items-center justify-between">
                        <span>News &amp; Views</span>
                        <i class="fa-solid fa-bolt text-red-600 text-[10px]"></i>
                    </h3>

                    <?php if (empty($latestArticles)): ?>
                        <div class="py-4 space-y-3 text-xs text-slate-700">
                            <p class="font-bold text-navy-900 flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-lwblue mt-1.5 shrink-0"></span>
                                <span>No articles have been published yet.</span>
                            </p>
                            <p class="text-[11px] text-slate-600 leading-relaxed pl-3.5">
                                Our editorial board is reviewing newly submitted research toolkits and monographs. Registered contributors can submit work immediately.
                            </p>
                            <div class="pt-2">
                                <a href="<?= url('auth/register.php') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-lwblue hover:underline">
                                    <span>Become a Contributor</span>
                                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <ul class="divide-y divide-slate-100 text-xs">
                            <?php foreach (array_slice($latestArticles, 0, 4) as $railArticle): ?>
                                <li class="py-3 group">
                                    <div class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 mt-1.5 shrink-0 group-hover:scale-125 transition-transform"></span>
                                        <div>
                                            <a href="<?= url('pages/article.php?slug=' . urlencode($railArticle['slug'])) ?>" 
                                               class="font-bold text-navy-900 group-hover:text-lwblue transition leading-snug line-clamp-2">
                                                <?= e($railArticle['title']) ?>
                                            </a>
                                            <span class="text-[10px] text-slate-500 mt-1 block">
                                                <?= e($railArticle['category_name'] ?? 'Editorial') ?> • <?= (int)$railArticle['reading_time'] ?> min read
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Division Mandate Box (Crisp Geometry) -->
                <div class="bg-surface p-4 rounded-md border border-slate-200">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-lwblue block mb-1">
                        Division Principle
                    </span>
                    <p class="text-xs text-slate-800 font-semibold leading-relaxed">
                        “Rigor before speed. Evidence before opinion. Public interest above all.”
                    </p>
                    <div class="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between text-[11px]">
                        <span class="text-slate-600">Editorial Charter</span>
                        <a href="<?= url('pages/about.php') ?>" class="text-lwblue font-bold hover:underline">Read &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- CENTER COLUMN: First Headline Preview (CNN Lead Story Style) -->
            <div class="lg:col-span-6 space-y-4 order-1 lg:order-2">
                <?php if ($featuredArticle): ?>
                    <!-- Big Bold Headline -->
                    <div class="space-y-1.5">
                        <span class="inline-block px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-black uppercase tracking-wider rounded-xs">
                            <?= e($featuredArticle['category_name'] ?? 'Headlining Monograph') ?>
                        </span>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-navy-900 leading-tight font-heading group hover:text-lwblue transition">
                            <a href="<?= url('pages/article.php?slug=' . urlencode($featuredArticle['slug'])) ?>">
                                <?= e($featuredArticle['title']) ?>
                            </a>
                        </h2>
                        <?php if (!empty($featuredArticle['subtitle'])): ?>
                            <p class="text-sm font-semibold text-slate-700 italic">
                                <?= e($featuredArticle['subtitle']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Lead Story Cover Image Preview -->
                    <div class="relative aspect-video rounded-md overflow-hidden shadow border border-slate-200 group bg-navy-950">
                        <img src="<?= asset($featuredArticle['cover_image'] ?? 'assets/images/article-misinformation.svg') ?>" 
                             alt="<?= e($featuredArticle['title']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-102 transition duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-3 left-3 right-3 text-white flex items-center justify-between text-xs">
                            <span class="font-medium bg-black/70 px-2 py-0.5 rounded-xs">
                                By <?= e($featuredArticle['author_name']) ?>
                            </span>
                            <span class="bg-black/70 px-2 py-0.5 rounded-xs font-mono text-[11px]">
                                <?= (int)$featuredArticle['reading_time'] ?> min read
                            </span>
                        </div>
                    </div>

                    <!-- Excerpt & Takeaways (CNN Bullet Preview Style) -->
                    <p class="text-sm text-slate-800 leading-relaxed font-normal">
                        <?= e($featuredArticle['excerpt']) ?>
                    </p>

                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-600 block">Key Inquiry Angles:</span>
                        <ul class="text-xs text-slate-700 space-y-1.5 pl-4 list-disc marker:text-lwblue">
                            <li>Deconstructing cognitive biases in algorithmic digital news ecosystems.</li>
                            <li>Evaluating verification frameworks for student researchers and civic observers.</li>
                            <li>Frameworks for ethical debate across contentious contemporary social issues.</li>
                        </ul>
                    </div>

                    <div class="pt-2">
                        <a href="<?= url('pages/article.php?slug=' . urlencode($featuredArticle['slug'])) ?>" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-navy-900 hover:bg-lwblue text-white text-xs font-bold rounded-md transition duration-150 shadow-sm">
                            <span>Read Full Investigation</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Empty State for Lead Story (Strict No-Fake-Content Compliance) -->
                    <div class="bg-surface border-2 border-dashed border-slate-300 rounded-lg p-8 text-center space-y-4">
                        <div class="w-14 h-14 rounded-md bg-lwblue-ice text-lwblue flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-xl font-black text-navy-900">No articles have been published yet.</h3>
                            <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">
                                Our editorial wire is awaiting newly submitted monographs. When articles are published through the review pipeline, the primary lead story and live headlines will preview here automatically.
                            </p>
                        </div>
                        <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                            <a href="<?= url('auth/register.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-md transition shadow">
                                <i class="fa-solid fa-pen-nib text-xs"></i>
                                <span>Register to Write &amp; Submit</span>
                            </a>
                            <a href="<?= url('pages/about.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 text-slate-800 hover:text-navy-900 text-xs font-bold rounded-md transition">
                                <span>Learn About Our Platform</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT COLUMN: Secondary Headline & Inquiries (CNN Right Rail) -->
            <div class="lg:col-span-3 space-y-5 order-3 border-t lg:border-t-0 lg:border-l border-slate-200 pt-6 lg:pt-0 lg:pl-6">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-navy-900 pb-2 border-b border-slate-300 flex items-center justify-between">
                        <span>Perspective Spotlight</span>
                        <i class="fa-solid fa-scale-balanced text-lwblue text-[11px]"></i>
                    </h3>

                    <?php if ($secondaryArticle): ?>
                        <div class="pt-3 space-y-3">
                            <div class="relative aspect-video rounded-md overflow-hidden bg-navy-950">
                                <img src="<?= asset($secondaryArticle['cover_image'] ?? 'assets/images/article-dialogue.svg') ?>" 
                                     alt="<?= e($secondaryArticle['title']) ?>" 
                                     class="w-full h-full object-cover">
                                <span class="absolute top-2 left-2 bg-navy-900/90 text-white text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-xs">
                                    Analysis
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-navy-900 hover:text-lwblue transition leading-snug">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($secondaryArticle['slug'])) ?>">
                                    <?= e($secondaryArticle['title']) ?>
                                </a>
                            </h4>
                            <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed">
                                <?= e($secondaryArticle['excerpt']) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- Editorial Agenda Card when no secondary article -->
                        <div class="pt-3 space-y-3">
                            <div class="relative aspect-video rounded-md overflow-hidden bg-navy-900 flex items-center justify-center p-3">
                                <img src="<?= asset('assets/images/lagunawise-banner.svg') ?>" alt="LagunaWISE" class="max-h-12 w-auto object-contain">
                            </div>
                            <h4 class="text-sm font-bold text-navy-900 leading-snug">
                                Institutional Research Call
                            </h4>
                            <p class="text-xs text-slate-700 leading-relaxed">
                                Inviting monographs and student inquiry papers on media literacy, digital sociology, and civic pedagogy.
                            </p>
                            <a href="<?= url('pages/contact.php') ?>" class="inline-block text-xs font-bold text-lwblue hover:underline">
                                Inquire with Editorial Board &rarr;
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fortnightly Dispatch Newsletter Widget -->
                <div class="bg-navy-900 text-white p-4 rounded-md shadow space-y-3" style="background-color: #061638;">
                    <div class="flex items-center gap-2 text-lwblue-ice text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        <span>Fortnightly Dispatch</span>
                    </div>
                    <h4 class="text-sm font-bold text-white leading-snug">
                        Intellectual Inquiries in Your Inbox
                    </h4>
                    <p class="text-[11px] text-slate-200 leading-relaxed">
                        Receive research briefs, media literacy analysis, and civic toolkits twice a month.
                    </p>
                    <form action="<?= url('actions/newsletter-action.php') ?>" method="POST" class="space-y-2">
                        <?= csrf_field() ?>
                        <input type="email" name="email" required placeholder="name@example.com" 
                               class="w-full px-3 py-2 bg-navy-850 border border-navy-700 rounded text-xs text-white placeholder-slate-400 focus:outline-hidden focus:border-lwblue-light">
                        <button type="submit" class="w-full py-2 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded transition shadow">
                            Subscribe to Dispatch
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 4. CNN-STYLE MULTI-TOPIC SECTIONS GRID (Desks with crisp cards) -->
<section class="py-12 bg-surface border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-10">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-2 border-b-2 border-navy-900">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-lwblue">Curated Editorial Desks</span>
                <h2 class="text-2xl font-black text-navy-900 uppercase tracking-tight font-heading mt-0.5">
                    Topics &amp; Inquiry Desks
                </h2>
            </div>
            <a href="<?= url('pages/topics.php') ?>" class="text-xs font-bold text-lwblue hover:text-navy-900 flex items-center gap-1">
                <span>View All Desks (<?= count($featuredTopics) ?>)</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Topic Rows: 3 Topics side by side in CNN column format (reduced roundedness) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Desk 1: Media Literacy & Misinformation -->
            <div class="bg-white rounded-md border border-slate-200 overflow-hidden shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Desk Header -->
                    <div class="px-4 py-2.5 bg-navy-900 text-white flex items-center justify-between border-b border-navy-800" style="background-color: #061638;">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-white">Media Literacy</h3>
                        </div>
                        <a href="<?= url('pages/topic.php?slug=media-literacy') ?>" class="text-[11px] text-slate-200 hover:text-white font-bold">
                            Desk &gt;
                        </a>
                    </div>
                    
                    <div class="p-4 space-y-3">
                        <div class="relative aspect-video rounded overflow-hidden bg-slate-900">
                            <img src="<?= asset('assets/images/article-medialiteracy.svg') ?>" alt="Media Literacy Desk" class="w-full h-full object-cover">
                            <span class="absolute bottom-2 left-2 bg-navy-900/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-xs">
                                Verification Toolkit
                            </span>
                        </div>

                        <h4 class="text-sm font-bold text-navy-900 hover:text-lwblue transition leading-snug">
                            <a href="<?= url('pages/topic.php?slug=media-literacy') ?>">
                                Deconstructing Information Disorder: Verification Tools for Citizens
                            </a>
                        </h4>

                        <ul class="text-xs text-slate-700 space-y-1.5 border-t border-slate-100 pt-3">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">•</span>
                                <span>Fact-checking protocols in hyper-connected digital feeds.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">•</span>
                                <span>Synthetic media, deepfakes, and forensic verification.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">•</span>
                                <span>Educational curricula for secondary and collegiate learners.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="p-4 pt-0">
                    <a href="<?= url('pages/topic.php?slug=media-literacy') ?>" 
                       class="w-full block py-2 text-center bg-surface hover:bg-slate-100 border border-slate-200 text-xs font-bold text-navy-900 rounded transition">
                        Explore Media Literacy Desk &rarr;
                    </a>
                </div>
            </div>

            <!-- Desk 2: Critical Thinking & Logic -->
            <div class="bg-white rounded-md border border-slate-200 overflow-hidden shadow-xs flex flex-col justify-between">
                <div>
                    <div class="px-4 py-2.5 bg-navy-900 text-white flex items-center justify-between border-b border-navy-800" style="background-color: #061638;">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-white">Critical Thinking</h3>
                        </div>
                        <a href="<?= url('pages/topic.php?slug=critical-thinking') ?>" class="text-[11px] text-slate-200 hover:text-white font-bold">
                            Desk &gt;
                        </a>
                    </div>
                    
                    <div class="p-4 space-y-3">
                        <div class="relative aspect-video rounded overflow-hidden bg-slate-900">
                            <img src="<?= asset('assets/images/article-criticalthinking.svg') ?>" alt="Critical Thinking Desk" class="w-full h-full object-cover">
                            <span class="absolute bottom-2 left-2 bg-navy-900/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-xs">
                                Philosophical Inquiries
                            </span>
                        </div>

                        <h4 class="text-sm font-bold text-navy-900 hover:text-lwblue transition leading-snug">
                            <a href="<?= url('pages/topic.php?slug=critical-thinking') ?>">
                                Cognitive Biases and the Anatomy of Sound Argumentation
                            </a>
                        </h4>

                        <ul class="text-xs text-slate-700 space-y-1.5 border-t border-slate-100 pt-3">
                            <li class="flex items-start gap-2">
                                <span class="text-teal-600 font-bold">•</span>
                                <span>Identifying common informal logical fallacies in public rhetoric.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-teal-600 font-bold">•</span>
                                <span>The psychology of confirmation bias and echo chambers.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-teal-600 font-bold">•</span>
                                <span>Dialectical inquiry methods for classroom and community discussion.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="p-4 pt-0">
                    <a href="<?= url('pages/topic.php?slug=critical-thinking') ?>" 
                       class="w-full block py-2 text-center bg-surface hover:bg-slate-100 border border-slate-200 text-xs font-bold text-navy-900 rounded transition">
                        Explore Critical Thinking Desk &rarr;
                    </a>
                </div>
            </div>

            <!-- Desk 3: Digital Citizenship & Civic Rights -->
            <div class="bg-white rounded-md border border-slate-200 overflow-hidden shadow-xs flex flex-col justify-between">
                <div>
                    <div class="px-4 py-2.5 bg-navy-900 text-white flex items-center justify-between border-b border-navy-800" style="background-color: #061638;">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-white">Digital Citizenship</h3>
                        </div>
                        <a href="<?= url('pages/topic.php?slug=digital-citizenship') ?>" class="text-[11px] text-slate-200 hover:text-white font-bold">
                            Desk &gt;
                        </a>
                    </div>
                    
                    <div class="p-4 space-y-3">
                        <div class="relative aspect-video rounded overflow-hidden bg-slate-900">
                            <img src="<?= asset('assets/images/article-digitalcitizenship.svg') ?>" alt="Digital Citizenship Desk" class="w-full h-full object-cover">
                            <span class="absolute bottom-2 left-2 bg-navy-900/90 text-white text-[9px] font-bold px-2 py-0.5 rounded-xs">
                                Civic Participation
                            </span>
                        </div>

                        <h4 class="text-sm font-bold text-navy-900 hover:text-lwblue transition leading-snug">
                            <a href="<?= url('pages/topic.php?slug=digital-citizenship') ?>">
                                Civic Agency in the Digital Age: Privacy, Ethics, and Community
                            </a>
                        </h4>

                        <ul class="text-xs text-slate-700 space-y-1.5 border-t border-slate-100 pt-3">
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 font-bold">•</span>
                                <span>Data privacy, digital surveillance, and youth rights.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 font-bold">•</span>
                                <span>Civic engagement beyond hashtag activism.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 font-bold">•</span>
                                <span>Building respectful civil dialogue in polarized environments.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="p-4 pt-0">
                    <a href="<?= url('pages/topic.php?slug=digital-citizenship') ?>" 
                       class="w-full block py-2 text-center bg-surface hover:bg-slate-100 border border-slate-200 text-xs font-bold text-navy-900 rounded transition">
                        Explore Digital Citizenship Desk &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- Second Row of Topics (Informed Discourse, Youth Civic Engagement, Ethical Technology) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2">
            <?php foreach (array_slice($featuredTopics, 3, 3) as $topic): ?>
                <a href="<?= url('pages/topic.php?slug=' . urlencode($topic['slug'])) ?>" 
                   class="bg-white p-4 rounded-md border border-slate-200 hover:border-lwblue hover:shadow-sm transition flex items-start gap-3.5 group">
                    <div class="w-9 h-9 rounded bg-lwblue-ice text-lwblue flex items-center justify-center shrink-0 text-sm group-hover:bg-lwblue group-hover:text-white transition-colors">
                        <i class="fa-solid <?= e($topic['icon'] ?: 'fa-hashtag') ?>"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-navy-900 group-hover:text-lwblue transition mb-0.5">
                            <?= e($topic['name']) ?>
                        </h4>
                        <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed">
                            <?= e($topic['description']) ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- 5. CNN-STYLE MULTIMEDIA & RESEARCH INQUIRY SHOWCASE (Solid Dark Navy Background with High Contrast) -->
<section class="py-12 bg-navy-950 text-white border-b border-navy-800" style="background-color: #030D22;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="border-b border-navy-800 pb-3 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 bg-lwblue-light rounded-xs"></span>
                <h2 class="text-lg sm:text-xl font-black uppercase tracking-tight text-white font-heading">
                    Educational Toolkits &amp; Research Briefs
                </h2>
            </div>
            <span class="text-xs font-bold text-lwblue-ice uppercase tracking-wider">
                Multimedia Inquiries
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <div class="bg-navy-900 border border-navy-800 rounded-md overflow-hidden group hover:border-lwblue-light/60 transition shadow-sm" style="background-color: #061638;">
                <div class="relative aspect-video bg-navy-950">
                    <img src="<?= asset('assets/images/article-medialiteracy.svg') ?>" alt="Toolkit 1" class="w-full h-full object-cover">
                    <span class="absolute bottom-2 left-2 bg-black/80 text-white text-[10px] font-mono px-2 py-0.5 rounded-xs flex items-center gap-1">
                        <i class="fa-solid fa-file-pdf text-[9px] text-red-400"></i> PDF Guide
                    </span>
                </div>
                <div class="p-3.5 space-y-1">
                    <h4 class="text-xs font-bold text-white group-hover:text-lwblue-light transition leading-snug">
                        The Media Literacy Verification Fieldbook
                    </h4>
                    <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                        Essential check heuristics for students examining social media sources and claims.
                    </p>
                </div>
            </div>

            <div class="bg-navy-900 border border-navy-800 rounded-md overflow-hidden group hover:border-lwblue-light/60 transition shadow-sm" style="background-color: #061638;">
                <div class="relative aspect-video bg-navy-950">
                    <img src="<?= asset('assets/images/article-criticalthinking.svg') ?>" alt="Toolkit 2" class="w-full h-full object-cover">
                    <span class="absolute bottom-2 left-2 bg-black/80 text-white text-[10px] font-mono px-2 py-0.5 rounded-xs flex items-center gap-1">
                        <i class="fa-solid fa-chalkboard-user text-[9px] text-teal-400"></i> Workshop
                    </span>
                </div>
                <div class="p-3.5 space-y-1">
                    <h4 class="text-xs font-bold text-white group-hover:text-lwblue-light transition leading-snug">
                        Dialectical Reasoning &amp; Logic Primer
                    </h4>
                    <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                        A structured breakdown of deductive, inductive, and abductive inquiry in debate.
                    </p>
                </div>
            </div>

            <div class="bg-navy-900 border border-navy-800 rounded-md overflow-hidden group hover:border-lwblue-light/60 transition shadow-sm" style="background-color: #061638;">
                <div class="relative aspect-video bg-navy-950">
                    <img src="<?= asset('assets/images/article-misinformation.svg') ?>" alt="Toolkit 3" class="w-full h-full object-cover">
                    <span class="absolute bottom-2 left-2 bg-black/80 text-white text-[10px] font-mono px-2 py-0.5 rounded-xs flex items-center gap-1">
                        <i class="fa-solid fa-magnifying-glass-chart text-[9px] text-blue-400"></i> Case Study
                    </span>
                </div>
                <div class="p-3.5 space-y-1">
                    <h4 class="text-xs font-bold text-white group-hover:text-lwblue-light transition leading-snug">
                        Algorithms, Outrage, and Echo Chambers
                    </h4>
                    <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                        Investigating how social media recommendation engines amplify polarized rhetoric.
                    </p>
                </div>
            </div>

            <div class="bg-navy-900 border border-navy-800 rounded-md overflow-hidden group hover:border-lwblue-light/60 transition shadow-sm" style="background-color: #061638;">
                <div class="relative aspect-video bg-navy-950">
                    <img src="<?= asset('assets/images/article-digitalcitizenship.svg') ?>" alt="Toolkit 4" class="w-full h-full object-cover">
                    <span class="absolute bottom-2 left-2 bg-black/80 text-white text-[10px] font-mono px-2 py-0.5 rounded-xs flex items-center gap-1">
                        <i class="fa-solid fa-users text-[9px] text-amber-400"></i> Civic Brief
                    </span>
                </div>
                <div class="p-3.5 space-y-1">
                    <h4 class="text-xs font-bold text-white group-hover:text-lwblue-light transition leading-snug">
                        Youth Digital Citizenship Framework
                    </h4>
                    <p class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                        Empowering community leaders with responsible digital engagement practices.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 6. EDITORIAL ARTICLES REPOSITORIES & LATEST ARCHIVES -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="flex items-center justify-between border-b-2 border-navy-900 pb-2 mb-6">
            <h2 class="text-xl font-black uppercase tracking-tight text-navy-900 font-heading">
                All Published Monographs &amp; Research
            </h2>
            <a href="<?= url('pages/articles.php') ?>" class="text-xs font-bold text-lwblue hover:underline">
                View Archive &rarr;
            </a>
        </div>

        <?php if (empty($latestArticles)): ?>
            <div class="bg-surface rounded-md border border-slate-200 p-8 text-center max-w-xl mx-auto space-y-3">
                <div class="w-12 h-12 rounded bg-blue-100 text-lwblue flex items-center justify-center text-xl mx-auto">
                    <i class="fa-regular fa-newspaper"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900">No articles have been published yet.</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Our contributing authors and editorial board are actively authoring monographs, investigative analyses, and media literacy toolkits.
                </p>
                <div class="pt-1">
                    <a href="<?= url('auth/register.php') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-lwblue text-white text-xs font-bold rounded-md hover:bg-lwblue-hover transition shadow">
                        <i class="fa-solid fa-pen-nib text-[10px]"></i>
                        <span>Register as Contributor</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($latestArticles as $article): ?>
                    <article class="bg-surface rounded-md border border-slate-200 p-4 flex flex-col justify-between hover:border-lwblue hover:shadow-sm transition group">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span class="font-bold text-lwblue uppercase tracking-wider text-[10px]"><?= e($article['category_name'] ?? 'Inquiry') ?></span>
                                <span><?= (int)$article['reading_time'] ?> min read</span>
                            </div>
                            <h3 class="text-sm font-bold text-navy-900 group-hover:text-lwblue transition leading-snug line-clamp-2">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>">
                                    <?= e($article['title']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed">
                                <?= e($article['excerpt']) ?>
                            </p>
                        </div>
                        <div class="pt-3 mt-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
                            <span>By <?= e($article['author_name']) ?></span>
                            <span class="text-lwblue font-bold group-hover:underline">Read &rarr;</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- 7. Division Charter Quotation Banner -->
<section class="py-14 bg-navy-900 text-white relative overflow-hidden border-t border-navy-800" style="background-color: #061638;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-3">
        <span class="inline-block px-2.5 py-1 rounded bg-navy-800 border border-navy-700 text-[10px] font-black tracking-widest text-lwblue-ice uppercase">
            LagunaWISE Ideology Division
        </span>
        <blockquote class="text-2xl sm:text-3xl font-black text-white font-heading">
            “Think Critically. Question Responsibly. Understand Deeply.”
        </blockquote>
        <p class="text-xs sm:text-sm text-slate-200 max-w-2xl mx-auto leading-relaxed">
            Addressing modern social challenges requires more than fast-paced headlines—it demands rigorous inquiry, media literacy, open dialogue across perspectives, and an enduring commitment to truth.
        </p>
        <div class="pt-2">
            <a href="<?= url('pages/about.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-navy-900 hover:bg-slate-100 font-bold text-xs uppercase tracking-wider rounded-md transition shadow">
                <span>Read Full Editorial Charter</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
