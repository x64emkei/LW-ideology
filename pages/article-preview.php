<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_login();
$user = current_user();
$db = Database::getConnection();

$articleId = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

if ($articleId > 0) {
    $stmt = $db->prepare("
        SELECT a.*, u.name AS author_name, u.bio AS author_bio, u.avatar AS author_avatar
        FROM articles a
        JOIN users u ON a.author_id = u.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmt->execute([$articleId]);
} elseif (!empty($slug)) {
    $stmt = $db->prepare("
        SELECT a.*, u.name AS author_name, u.bio AS author_bio, u.avatar AS author_avatar
        FROM articles a
        JOIN users u ON a.author_id = u.id
        WHERE a.slug = ?
        LIMIT 1
    ");
    $stmt->execute([$slug]);
} else {
    flash('error', 'Article not specified.');
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article Not Found';
    require_once dirname(__DIR__) . '/includes/header.php';
    echo '<div class="max-w-xl mx-auto py-24 px-4 text-center space-y-4">
            <h1 class="text-3xl font-extrabold text-navy-900">Manuscript Not Found</h1>
            <p class="text-xs text-editorial-muted">The requested manuscript could not be found.</p>
            <a href="' . url('pages/dashboard.php') . '" class="inline-block px-5 py-2.5 bg-lwblue text-white rounded-xl text-xs font-bold">Return to Dashboard</a>
          </div>';
    require_once dirname(__DIR__) . '/includes/footer.php';
    exit;
}

// Access Control: Author or Staff only
$isAuthor = ((int)$article['author_id'] === (int)$user['id']);
$isStaff = user_has_any_role(['administrator', 'super_admin', 'admin', 'editor']);

if (!$isAuthor && !$isStaff) {
    http_response_code(403);
    $pageTitle = 'Access Restricted';
    require_once dirname(__DIR__) . '/includes/header.php';
    echo '<div class="max-w-xl mx-auto py-24 px-4 text-center space-y-4">
            <span class="inline-block px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold uppercase">Restricted</span>
            <h1 class="text-2xl font-extrabold text-navy-900">Private Manuscript Preview</h1>
            <p class="text-xs text-editorial-muted">You do not have permission to preview manuscripts authored by other contributors.</p>
            <a href="' . url('pages/dashboard.php') . '" class="inline-block px-5 py-2.5 bg-lwblue text-white rounded-xl text-xs font-bold">Return to Dashboard</a>
          </div>';
    require_once dirname(__DIR__) . '/includes/footer.php';
    exit;
}

// Fetch categories
$catStmt = $db->prepare("
    SELECT c.*
    FROM categories c
    JOIN article_categories ac ON c.id = ac.category_id
    WHERE ac.article_id = ?
");
$catStmt->execute([$article['id']]);
$categories = $catStmt->fetchAll();

// Fetch latest feedback if revision requested
$latestFeedback = null;
if ($article['status'] === 'revision_requested') {
    $fbStmt = $db->prepare("SELECT feedback FROM editorial_reviews WHERE article_id = ? ORDER BY created_at DESC LIMIT 1");
    $fbStmt->execute([$article['id']]);
    $latestFeedback = $fbStmt->fetchColumn();
}

$pageTitle = '[PREVIEW] ' . $article['title'];
$pageDescription = $article['excerpt'];
$canonicalUrl = url('pages/article.php?slug=' . urlencode($article['slug']));

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Preview Status Top Banner -->
<div class="bg-amber-500 text-navy-900 font-bold px-4 py-3 sticky top-20 z-30 shadow-md border-b border-amber-600">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-navy-900 text-white rounded uppercase text-[10px] tracking-wider font-extrabold">Private Preview</span>
            <span>Manuscript Status: <strong><?= strtoupper(e(str_replace('_', ' ', $article['status']))) ?></strong></span>
            <?php if ($article['status'] !== 'published'): ?>
                <span class="hidden md:inline text-amber-950 font-normal">• Not visible to public readers</span>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('admin/article-edit.php?id=' . (int)$article['id']) ?>" 
               class="px-3 py-1.5 bg-navy-900 hover:bg-navy-800 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>Edit Manuscript</span>
            </a>
            <?php if (in_array($article['status'], ['draft', 'revision_requested'], true)): ?>
                <form action="<?= url('actions/article-action.php') ?>" method="POST" class="inline">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="submit_for_review">
                    <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                    <input type="hidden" name="return_to" value="<?= url('pages/article-preview.php?id=' . (int)$article['id']) ?>">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane text-[11px]"></i>
                        <span>Submit for Review</span>
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= url('pages/dashboard.php') ?>" class="px-3 py-1.5 bg-white text-navy-900 hover:bg-slate-100 rounded-lg text-xs font-bold transition">
                &larr; Dashboard
            </a>
        </div>
    </div>
</div>

<?php if (!empty($latestFeedback)): ?>
    <div class="bg-amber-100 border-b border-amber-300 p-4">
        <div class="max-w-4xl mx-auto text-xs text-amber-900 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5 shrink-0 text-sm"></i>
            <div>
                <strong>Editor Revision Guidance:</strong> <?= e($latestFeedback) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Article Header Section -->
<header class="bg-navy-900 text-white pt-12 pb-16 relative overflow-hidden border-b border-navy-800">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 space-y-6">
        
        <!-- Breadcrumb & Categories -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="<?= url('pages/dashboard.php') ?>" class="text-slate-400 hover:text-white transition">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-400">Author Preview</span>
            <span class="text-slate-600">/</span>
            <?php if (empty($categories)): ?>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-300">Uncategorized</span>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-lwblue text-white">
                        <?= e($cat['name']) ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Title -->
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight font-heading">
            <?= e($article['title']) ?>
        </h1>

        <!-- Subtitle / Thesis -->
        <?php if (!empty($article['subtitle'])): ?>
            <p class="text-base sm:text-xl text-slate-300 font-normal leading-relaxed max-w-4xl">
                <?= e($article['subtitle']) ?>
            </p>
        <?php endif; ?>

        <!-- Author Meta & Metrics Bar -->
        <div class="pt-4 border-t border-navy-800 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-400">
            <div class="flex items-center gap-3">
                <img src="<?= asset($article['author_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                     alt="<?= e($article['author_name']) ?>" 
                     class="w-10 h-10 rounded-full object-cover border border-navy-700 shadow-sm">
                <div>
                    <p class="font-bold text-white text-sm"><?= e($article['author_name']) ?></p>
                    <p class="text-[11px] text-slate-400">Contributing Author</p>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar text-lwblue-light"></i>
                    <span>Drafted <?= e(format_date($article['created_at'])) ?></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-clock text-lwblue-light"></i>
                    <span><?= (int)$article['reading_time'] ?> min read</span>
                </span>
            </div>
        </div>

    </div>
</header>

<!-- Article Responsive Two-Column Layout (Main Content 66% / Sidebar 34%) -->
<div class="py-12 sm:py-16 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Main Content Column (Desktop ~66% / 8 cols) -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/90 shadow-sm space-y-8">
                
                <!-- Cover Image -->
                <?php if (!empty($article['cover_image'])): ?>
                    <div class="rounded-2xl overflow-hidden shadow-md border border-slate-200 aspect-[16/9] bg-slate-900">
                        <img src="<?= asset($article['cover_image']) ?>" 
                             alt="<?= e($article['title']) ?>" 
                             class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <!-- Excerpt Lead Box -->
                <div class="p-5 bg-blue-50/70 border-l-4 border-lwblue rounded-r-xl text-sm font-medium text-slate-800 leading-relaxed">
                    <?= e($article['excerpt']) ?>
                </div>

                <!-- Editorial Body Prose -->
                <div class="editorial-prose text-base sm:text-lg text-slate-800 leading-relaxed max-w-prose">
                    <?= render_article_content($article['content']) ?>
                </div>

                <!-- References & Sources Section -->
                <?php if (!empty($article['references_sources'])): ?>
                    <div class="mt-12 pt-6 border-t border-slate-200 space-y-3">
                        <h3 class="text-sm font-bold text-navy-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-book-bookmark text-lwblue"></i>
                            <span>References &amp; Cited Sources</span>
                        </h3>
                        <div class="p-4 bg-surface rounded-xl border border-slate-200 text-xs text-slate-700 whitespace-pre-line leading-relaxed font-mono">
                            <?= e($article['references_sources']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tags -->
                <?php if (!empty($article['tags'])): ?>
                    <div class="pt-4 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold"><i class="fa-solid fa-tags text-[11px] mr-1"></i>Tags:</span>
                        <?php foreach (explode(',', $article['tags']) as $t): ?>
                            <?php $t = trim($t); if (!empty($t)): ?>
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">#<?= e($t) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Secondary Sidebar Column (Desktop ~34% / 4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Publishing & Workflow Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                        Manuscript Controls
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Status</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                <?= match($article['status']) {
                                    'published' => 'bg-emerald-100 text-emerald-800',
                                    'pending_review' => 'bg-amber-100 text-amber-800',
                                    'revision_requested' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-slate-100 text-slate-700'
                                } ?>">
                                <?= e(str_replace('_', ' ', $article['status'])) ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Reading Time</span>
                            <span class="font-bold text-slate-800"><?= (int)$article['reading_time'] ?> min</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Created</span>
                            <span class="font-medium text-slate-800"><?= e(format_date($article['created_at'])) ?></span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <a href="<?= url('admin/article-edit.php?id=' . (int)$article['id']) ?>" 
                           class="w-full py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-pen text-xs"></i>
                            <span>Edit Manuscript</span>
                        </a>

                        <?php if (in_array($article['status'], ['draft', 'revision_requested'], true)): ?>
                            <form action="<?= url('actions/article-action.php') ?>" method="POST" class="w-full">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="action" value="submit_for_review">
                                <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                                <input type="hidden" name="return_to" value="<?= url('pages/dashboard.php') ?>">
                                <button type="submit" 
                                        class="w-full py-2.5 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Submit for Editorial Review</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Author Bio Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-lwblue">Author Attribution</span>
                    <div class="flex items-center gap-3">
                        <img src="<?= asset($article['author_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                             alt="<?= e($article['author_name']) ?>" 
                             class="w-12 h-12 rounded-full object-cover border border-slate-200">
                        <div>
                            <h4 class="text-sm font-bold text-navy-900"><?= e($article['author_name']) ?></h4>
                            <p class="text-[11px] text-slate-500">Contributing Writer</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed pt-1">
                        <?= e($article['author_bio'] ?? 'Contributor to the LagunaWISE Ideology Division research and editorial platform.') ?>
                    </p>
                </div>

                <!-- Citation Preview Box -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-2 text-xs text-slate-600">
                    <h4 class="font-bold text-navy-900 flex items-center gap-2">
                        <i class="fa-solid fa-quote-left text-lwblue"></i>
                        <span>Citation Preview</span>
                    </h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        LagunaWISE Ideology Division. (<?= date('Y') ?>). <em><?= e($article['title']) ?></em>. Retrieved from <?= e($canonicalUrl) ?>
                    </p>
                </div>

            </div>

        </div>

    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
