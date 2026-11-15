<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$query = trim($_GET['q'] ?? '');
$pageTitle = !empty($query) ? 'Search results for "' . $query . '"' : 'Search Directory';
$pageDescription = 'Search across all published articles, research monographs, topics, and news releases of the LagunaWISE Ideology Division.';

$db = Database::getConnection();

$matchedArticles = [];
$matchedTopics = [];
$matchedNews = [];

if (!empty($query)) {
    $term = "%{$query}%";

    // 1. Search Articles
    $artStmt = $db->prepare("
        SELECT a.*, u.name AS author_name,
               (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name
        FROM articles a
        JOIN users u ON a.author_id = u.id
        WHERE a.status = 'published' AND (a.title LIKE ? OR a.subtitle LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)
        ORDER BY a.published_at DESC
        LIMIT 10
    ");
    $artStmt->execute([$term, $term, $term, $term]);
    $matchedArticles = $artStmt->fetchAll();

    // 2. Search Categories / Topics
    $catStmt = $db->prepare("
        SELECT * FROM categories 
        WHERE name LIKE ? OR description LIKE ?
        LIMIT 6
    ");
    $catStmt->execute([$term, $term]);
    $matchedTopics = $catStmt->fetchAll();

    // 3. Search News / Announcements
    $newsStmt = $db->prepare("
        SELECT * FROM announcements
        WHERE is_published = 1 AND (title LIKE ? OR summary LIKE ? OR content LIKE ?)
        LIMIT 6
    ");
    $newsStmt->execute([$term, $term, $term]);
    $matchedNews = $newsStmt->fetchAll();
}

$totalResults = count($matchedArticles) + count($matchedTopics) + count($matchedNews);

function highlight_match(string $text, string $query): string {
    if (empty($query)) return e($text);
    $escaped = e($text);
    return preg_replace('/(' . preg_quote(e($query), '/') . ')/i', '<mark class="bg-amber-200 text-slate-900 rounded px-1">$1</mark>', $escaped);
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="bg-navy-900 text-white py-14 sm:py-18 border-b border-navy-800 relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-4">
        <span class="text-xs font-bold tracking-widest text-lwblue-ice uppercase">Platform Search</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
            Search Directory &amp; Archive
        </h1>
        
        <form action="<?= url('pages/search.php') ?>" method="GET" class="max-w-xl mx-auto pt-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-4 text-slate-400"></i>
                <input type="text" 
                       name="q" 
                       value="<?= e($query) ?>" 
                       placeholder="Search keywords, authors, topics..." 
                       class="w-full text-sm pl-11 pr-24 py-3.5 bg-navy-800 border border-navy-700 rounded-2xl text-white placeholder-slate-400 focus:outline-none focus:border-lwblue-light focus:ring-1 focus:ring-lwblue-light">
                <button type="submit" class="absolute right-2 top-2 px-5 py-2 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition">
                    Search
                </button>
            </div>
        </form>
    </div>
</section>

<section class="py-14 bg-surface">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-10">

        <?php if (!empty($query)): ?>
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 text-xs">
                <p class="text-slate-600 font-medium">
                    Found <strong><?= $totalResults ?></strong> result(s) for "<span class="text-navy-900 font-bold"><?= e($query) ?></span>"
                </p>
                <a href="<?= url('pages/search.php') ?>" class="text-rose-600 hover:underline">Clear Search</a>
            </div>

            <?php if ($totalResults === 0): ?>
                <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center space-y-4 max-w-md mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                        <i class="fa-solid fa-file-circle-question"></i>
                    </div>
                    <h3 class="text-lg font-bold text-navy-900">No Matching Results</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        No articles, topics, or announcements matched "<?= e($query) ?>". Try different terms or browse the article directory.
                    </p>
                    <a href="<?= url('pages/articles.php') ?>" class="inline-block px-5 py-2.5 bg-lwblue text-white rounded-xl text-xs font-bold">Browse All Articles</a>
                </div>
            <?php else: ?>

                <!-- 1. Matching Articles -->
                <?php if (!empty($matchedArticles)): ?>
                    <div class="space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">
                            Articles &amp; Monographs (<?= count($matchedArticles) ?>)
                        </h2>
                        <div class="space-y-4">
                            <?php foreach ($matchedArticles as $art): ?>
                                <article class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-2 group hover:border-lwblue transition">
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                        <?php if (!empty($art['category_name'])): ?>
                                            <span class="font-bold text-lwblue uppercase"><?= e($art['category_name']) ?></span>
                                            <span>•</span>
                                        <?php endif; ?>
                                        <span><?= e(format_date($art['published_at'])) ?></span>
                                        <span>•</span>
                                        <span>By <?= e($art['author_name']) ?></span>
                                    </div>
                                    <h3 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition">
                                        <a href="<?= url('pages/article.php?slug=' . urlencode($art['slug'])) ?>">
                                            <?= highlight_match($art['title'], $query) ?>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-editorial-muted line-clamp-2 leading-relaxed">
                                        <?= highlight_match($art['excerpt'], $query) ?>
                                    </p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. Matching Topics -->
                <?php if (!empty($matchedTopics)): ?>
                    <div class="space-y-4 pt-4 border-t border-slate-200">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">
                            Topics (<?= count($matchedTopics) ?>)
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($matchedTopics as $top): ?>
                                <a href="<?= url('pages/topic.php?slug=' . urlencode($top['slug'])) ?>" 
                                   class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-lwblue transition space-y-1">
                                    <h3 class="text-sm font-bold text-navy-900"><?= highlight_match($top['name'], $query) ?></h3>
                                    <p class="text-xs text-slate-500 line-clamp-2"><?= highlight_match($top['description'], $query) ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3. Matching News -->
                <?php if (!empty($matchedNews)): ?>
                    <div class="space-y-4 pt-4 border-t border-slate-200">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">
                            News &amp; Updates (<?= count($matchedNews) ?>)
                        </h2>
                        <div class="space-y-3">
                            <?php foreach ($matchedNews as $nw): ?>
                                <div class="bg-white p-5 rounded-2xl border border-slate-200 space-y-1">
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400 font-bold uppercase">
                                        <span class="text-lwblue"><?= e($nw['badge_type']) ?></span>
                                        <span>•</span>
                                        <span><?= e(format_date($nw['published_at'])) ?></span>
                                    </div>
                                    <h3 class="text-sm font-bold text-navy-900">
                                        <a href="<?= url('pages/news.php#news-' . (int)$nw['id']) ?>" class="hover:text-lwblue">
                                            <?= highlight_match($nw['title'], $query) ?>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-slate-500"><?= highlight_match($nw['summary'], $query) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        <?php else: ?>
            <div class="bg-white p-8 rounded-3xl border border-slate-200 text-center space-y-3">
                <p class="text-xs text-slate-600">Enter a query above to search articles, research monographs, topics, and division announcements.</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
