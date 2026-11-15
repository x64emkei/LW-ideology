<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'Articles & Insights — Editorial Directory';
$pageDescription = 'Browse research commentaries, monographs, and investigative insights on media literacy, digital citizenship, and contemporary social discourse.';

$db = Database::getConnection();

// Query Parameters
$search = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Base conditions
$where = ["a.status = 'published'"];
$params = [];

if (!empty($search)) {
    $where[] = "(a.title LIKE ? OR a.subtitle LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($categorySlug)) {
    $where[] = "EXISTS (SELECT 1 FROM article_categories ac JOIN categories c ON ac.category_id = c.id WHERE ac.article_id = a.id AND c.slug = ?)";
    $params[] = $categorySlug;
}

$whereSql = implode(' AND ', $where);

// Sorting
$orderBy = match($sort) {
    'oldest' => 'a.published_at ASC',
    'popular' => 'a.views_count DESC',
    'reading_time' => 'a.reading_time ASC',
    default => 'a.published_at DESC'
};

// Count total
$countStmt = $db->prepare("
    SELECT COUNT(*) 
    FROM articles a
    WHERE {$whereSql}
");
$countStmt->execute($params);
$totalArticles = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalArticles / $perPage));

// Fetch articles
$query = "
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
           (SELECT c.slug FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_slug
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE {$whereSql}
    ORDER BY {$orderBy}
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Fetch categories for filter dropdown/tabs
$categories = $db->query("
    SELECT c.*, COUNT(ac.article_id) AS total_count
    FROM categories c
    LEFT JOIN article_categories ac ON c.id = ac.category_id
    LEFT JOIN articles a ON ac.article_id = a.id AND a.status = 'published'
    GROUP BY c.id
    ORDER BY c.display_order ASC
")->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="bg-navy-900 text-white py-14 sm:py-20 border-b border-navy-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-4">
        <span class="text-xs font-bold tracking-widest text-lwblue-ice uppercase">Digital Archive</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
            Articles, Inquiries &amp; Insights
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto font-normal">
            Explore evidence-based perspectives, sociological evaluations, and media literacy toolkits.
        </p>
    </div>
</section>

<!-- Filter & Search Controls Bar -->
<section class="bg-white border-b border-slate-200 py-6 sticky top-20 z-30 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <form action="<?= url('pages/articles.php') ?>" method="GET" class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            
            <!-- Search input -->
            <div class="relative flex-1 max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="q" 
                       value="<?= e($search) ?>" 
                       placeholder="Search title, excerpt, keyword..." 
                       class="w-full text-xs pl-9 pr-4 py-2.5 bg-surface border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-lwblue focus:bg-white transition">
                <?php if (!empty($search)): ?>
                    <a href="<?= url('pages/articles.php' . (!empty($categorySlug) ? '?category=' . urlencode($categorySlug) : '')) ?>" 
                       class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 text-xs" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Category & Sort Selects -->
            <div class="flex flex-wrap items-center gap-3">
                <select name="category" 
                        onchange="this.form.submit()" 
                        class="text-xs py-2.5 px-3 bg-surface border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-lwblue cursor-pointer">
                    <option value="">All Categories (<?= $totalArticles ?>)</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?> (<?= (int)$cat['total_count'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" 
                        onchange="this.form.submit()" 
                        class="text-xs py-2.5 px-3 bg-surface border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:border-lwblue cursor-pointer">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest First</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Sort: Oldest First</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Sort: Most Viewed</option>
                    <option value="reading_time" <?= $sort === 'reading_time' ? 'selected' : '' ?>>Sort: Quickest Read</option>
                </select>

                <button type="submit" class="px-4 py-2.5 bg-navy-900 hover:bg-navy-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
            </div>

        </form>

        <!-- Active Filter Tags -->
        <?php if (!empty($search) || !empty($categorySlug)): ?>
            <div class="flex flex-wrap items-center gap-2 pt-4 text-xs">
                <span class="text-slate-400">Active filters:</span>
                <?php if (!empty($search)): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-lwblue rounded-md border border-blue-200 font-medium">
                        Search: "<?= e($search) ?>"
                        <a href="<?= url('pages/articles.php' . (!empty($categorySlug) ? '?category=' . urlencode($categorySlug) : '')) ?>" class="hover:text-navy-900"><i class="fa-solid fa-xmark"></i></a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($categorySlug)): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-lwblue rounded-md border border-blue-200 font-medium">
                        Category: <?= e($categorySlug) ?>
                        <a href="<?= url('pages/articles.php' . (!empty($search) ? '?q=' . urlencode($search) : '')) ?>" class="hover:text-navy-900"><i class="fa-solid fa-xmark"></i></a>
                    </span>
                <?php endif; ?>
                <a href="<?= url('pages/articles.php') ?>" class="text-rose-600 hover:underline font-semibold ml-2">Reset All</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Articles Catalog Section -->
<section class="py-12 sm:py-16 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <?php if (empty($articles)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-3xl p-12 text-center max-w-lg mx-auto border border-slate-200 shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-lwblue flex items-center justify-center text-2xl mx-auto">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h3 class="text-lg font-bold text-navy-900">No Articles Found</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    We could not find any published monographs matching your current search parameters. Try expanding your search terms or resetting filters.
                </p>
                <div class="pt-2">
                    <a href="<?= url('pages/articles.php') ?>" class="inline-block px-5 py-2.5 bg-lwblue text-white text-xs font-bold rounded-xl hover:bg-lwblue-hover transition">
                        View All Articles
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($articles as $article): ?>
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
                                        <span><?= (int)$article['reading_time'] ?> min read</span>
                                    </span>
                                </div>

                                <h2 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition leading-snug line-clamp-2">
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>">
                                        <?= e($article['title']) ?>
                                    </a>
                                </h2>

                                <p class="text-xs text-editorial-muted line-clamp-3 leading-relaxed">
                                    <?= e($article['excerpt']) ?>
                                </p>
                            </div>

                            <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <img src="<?= asset($article['author_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                                         alt="<?= e($article['author_name']) ?>" 
                                         class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                    <span class="text-xs font-semibold text-slate-800"><?= e($article['author_name']) ?></span>
                                </div>

                                <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>" 
                                   class="text-xs font-bold text-lwblue group-hover:text-navy-900 flex items-center gap-1">
                                    <span>Read Article</span>
                                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                                </a>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center items-center gap-2 mt-12 pt-6 border-t border-slate-200">
                    <?php if ($page > 1): ?>
                        <a href="?p=<?= $page - 1 ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($categorySlug) ?>&sort=<?= urlencode($sort) ?>" 
                           class="px-3.5 py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($categorySlug) ?>&sort=<?= urlencode($sort) ?>" 
                           class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-lg border <?= $i === $page ? 'bg-lwblue border-lwblue text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?p=<?= $page + 1 ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($categorySlug) ?>&sort=<?= urlencode($sort) ?>" 
                           class="px-3.5 py-2 text-xs font-bold rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
