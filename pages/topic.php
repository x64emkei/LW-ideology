<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . url('pages/topics.php'));
    exit;
}

$db = Database::getConnection();

// Fetch category details
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: ' . url('pages/topics.php'));
    exit;
}

// Fetch articles in this category
$artStmt = $db->prepare("
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar
    FROM articles a
    JOIN users u ON a.author_id = u.id
    JOIN article_categories ac ON a.id = ac.article_id
    WHERE ac.category_id = ? AND a.status = 'published'
    ORDER BY a.published_at DESC
");
$artStmt->execute([$category['id']]);
$articles = $artStmt->fetchAll();

$pageTitle = $category['name'] . ' — Topic Inquiry';
$pageDescription = $category['description'];

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="bg-navy-900 text-white py-16 sm:py-20 border-b border-navy-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-navy-800 border border-navy-700 text-xs font-semibold text-lwblue-ice uppercase tracking-wider">
            <i class="fa-solid fa-<?= e($category['icon'] ?? 'hashtag') ?>"></i>
            <span>Topic Focus</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white">
            <?= e($category['name']) ?>
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed">
            <?= e($category['description']) ?>
        </p>
    </div>
</section>

<section class="py-14 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="flex items-center justify-between pb-6 mb-8 border-b border-slate-200">
            <h2 class="text-lg font-bold text-navy-900">
                Published Monograph &amp; Articles (<?= count($articles) ?>)
            </h2>
            <a href="<?= url('pages/articles.php?category=' . urlencode($category['slug'])) ?>" class="text-xs font-bold text-lwblue hover:text-navy-900">
                Filter in Directory &rarr;
            </a>
        </div>

        <?php if (empty($articles)): ?>
            <div class="bg-white p-10 rounded-lg border border-slate-200 text-center max-w-md mx-auto space-y-3">
                <i class="fa-regular fa-newspaper text-2xl text-slate-400 mb-1"></i>
                <h3 class="text-base font-bold text-navy-900">No articles have been published yet.</h3>
                <p class="text-xs text-slate-500">There are currently no published monographs under this topic category.</p>
                <div class="pt-2">
                    <a href="<?= url('pages/articles.php') ?>" class="inline-block px-4 py-2 bg-lwblue text-white rounded-xl text-xs font-bold hover:bg-lwblue-hover transition">Browse All Articles</a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($articles as $article): ?>
                    <article class="bg-white rounded-md border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col justify-between group">
                        <div class="aspect-video bg-slate-900 overflow-hidden">
                            <img src="<?= asset($article['cover_image'] ?? 'assets/images/article-medialiteracy.svg') ?>" 
                                 alt="<?= e($article['title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[11px] text-slate-400 font-medium"><?= e(format_date($article['published_at'])) ?> • <?= (int)$article['reading_time'] ?> min</span>
                                <h3 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition mt-1 line-clamp-2">
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>">
                                        <?= e($article['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-editorial-muted line-clamp-2 mt-2 leading-relaxed">
                                    <?= e($article['excerpt']) ?>
                                </p>
                            </div>
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs text-slate-700 font-semibold"><?= e($article['author_name']) ?></span>
                                <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>" class="text-xs font-bold text-lwblue">Read &rarr;</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
