<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_login();
$user = current_user();
$db = Database::getConnection();

// Filter for contributor articles
$statusFilter = trim($_GET['status'] ?? 'all');

// Fetch user's articles metrics
$userId = (int)$user['id'];
$countsStmt = $db->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS drafts,
        SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'revision_requested' THEN 1 ELSE 0 END) AS revisions,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published
    FROM articles 
    WHERE author_id = ?
");
$countsStmt->execute([$userId]);
$metrics = $countsStmt->fetch() ?: [];

$totalCount = (int)($metrics['total'] ?? 0);
$draftsCount = (int)($metrics['drafts'] ?? 0);
$pendingCount = (int)($metrics['pending'] ?? 0);
$revisionsCount = (int)($metrics['revisions'] ?? 0);
$publishedCount = (int)($metrics['published'] ?? 0);

// Fetch contributor articles
$whereSql = "a.author_id = ?";
$params = [$userId];

if ($statusFilter !== 'all' && in_array($statusFilter, ['draft', 'pending_review', 'revision_requested', 'published'], true)) {
    $whereSql .= " AND a.status = ?";
    $params[] = $statusFilter;
}

$artQuery = "
    SELECT a.*,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
           (SELECT feedback FROM editorial_reviews er WHERE er.article_id = a.id ORDER BY er.created_at DESC LIMIT 1) AS latest_feedback
    FROM articles a
    WHERE {$whereSql}
    ORDER BY a.updated_at DESC
";
$artStmt = $db->prepare($artQuery);
$artStmt->execute($params);
$myArticles = $artStmt->fetchAll();

// Fetch Reader Bookmarks
$bmStmt = $db->prepare("
    SELECT a.*, b.created_at AS bookmarked_at,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name
    FROM bookmarks b
    JOIN articles a ON b.article_id = a.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$bmStmt->execute([$userId]);
$bookmarkedArticles = $bmStmt->fetchAll();

// Fetch Reader Comments
$commStmt = $db->prepare("
    SELECT c.*, a.title AS article_title, a.slug AS article_slug
    FROM comments c
    JOIN articles a ON c.article_id = a.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$commStmt->execute([$userId]);
$myComments = $commStmt->fetchAll();

// Newsletter Subscription status
$subStmt = $db->prepare("SELECT status FROM newsletter_subscribers WHERE email = ? LIMIT 1");
$subStmt->execute([$user['email']]);
$isSubscribed = ($subStmt->fetchColumn() === 'subscribed');

$pageTitle = 'Contributor Dashboard — ' . $user['name'];
$pageDescription = 'Manage your authored manuscripts, review revision requests, track publications, and organize bookmarks.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- User Profile Hero Section -->
<section class="bg-navy-900 text-white py-12 border-b border-navy-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6">
            
            <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                <img src="<?= asset($user['avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                     alt="<?= e($user['name']) ?>" 
                     class="w-20 h-20 rounded-full object-cover border-4 border-navy-700 shadow-xl shrink-0">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h1 class="text-2xl font-extrabold text-white tracking-tight"><?= e($user['name']) ?></h1>
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-lwblue text-white uppercase tracking-wider">
                            <?= e(ucfirst(str_replace('_', ' ', $user['primary_role']))) ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">@<?= e($user['username']) ?> • Contributor since <?= e(format_date($user['created_at'])) ?></p>
                    <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                        <?= e($user['bio'] ?? 'Registered contributor and active intellectual participant at the LagunaWISE Ideology Division.') ?>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="<?= url('admin/article-create.php') ?>" 
                   class="px-5 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition duration-150 shadow flex items-center gap-2">
                    <i class="fa-solid fa-pen-nib text-xs"></i>
                    <span>Create Article</span>
                </a>

                <?php if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor'])): ?>
                    <a href="<?= url('admin/index.php') ?>" 
                       class="px-4 py-2.5 bg-navy-800 hover:bg-navy-700 border border-navy-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-lwblue-light"></i>
                        <span>CMS Administration</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('admin/profile.php') ?>" 
                   class="px-4 py-2.5 bg-white text-navy-900 hover:bg-slate-100 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-gear text-xs"></i>
                    <span>Account Settings</span>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- Dashboard Main Two-Column Layout (66% Main / 34% Sidebar) -->
<section class="py-12 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Main Content Area (Desktop ~66% / 8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Status Metrics Cards (Genuine MySQL Counts) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    
                    <a href="<?= url('pages/dashboard.php?status=all') ?>" 
                       class="bg-white p-4 rounded-md border transition shadow-xs hover:border-lwblue <?= $statusFilter === 'all' ? 'border-lwblue ring-1 ring-lwblue' : 'border-slate-200' ?>">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">My Articles</span>
                        <p class="text-2xl font-extrabold text-navy-900 mt-1"><?= $totalCount ?></p>
                        <span class="text-[10px] text-slate-500 font-medium">All manuscripts</span>
                    </a>

                    <a href="<?= url('pages/dashboard.php?status=draft') ?>" 
                       class="bg-white p-4 rounded-md border transition shadow-xs hover:border-slate-400 <?= $statusFilter === 'draft' ? 'border-slate-500 ring-1 ring-slate-500' : 'border-slate-200' ?>">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Drafts</span>
                        <p class="text-2xl font-extrabold text-slate-700 mt-1"><?= $draftsCount ?></p>
                        <span class="text-[10px] text-slate-500 font-medium">Unsubmitted</span>
                    </a>

                    <a href="<?= url('pages/dashboard.php?status=pending_review') ?>" 
                       class="bg-white p-4 rounded-md border transition shadow-xs hover:border-amber-400 <?= $statusFilter === 'pending_review' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-slate-200' ?>">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">In Review</span>
                        <p class="text-2xl font-extrabold text-amber-600 mt-1"><?= $pendingCount ?></p>
                        <span class="text-[10px] text-amber-600 font-medium">Awaiting editor</span>
                    </a>

                    <a href="<?= url('pages/dashboard.php?status=published') ?>" 
                       class="bg-white p-4 rounded-md border transition shadow-xs hover:border-emerald-400 <?= $statusFilter === 'published' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-slate-200' ?>">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Published</span>
                        <p class="text-2xl font-extrabold text-emerald-600 mt-1"><?= $publishedCount ?></p>
                        <span class="text-[10px] text-emerald-600 font-medium">Live on site</span>
                    </a>

                </div>

                <!-- Articles Section & Filter Navigation -->
                <div class="bg-white p-6 sm:p-8 rounded-lg border border-slate-200 shadow-xs space-y-6">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div>
                            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Author Manuscripts</span>
                            <h2 class="text-xl font-bold text-navy-900 mt-0.5">
                                <?= match($statusFilter) {
                                    'draft' => "Draft Articles ({$draftsCount})",
                                    'pending_review' => "Pending Editorial Review ({$pendingCount})",
                                    'revision_requested' => "Revision Requested ({$revisionsCount})",
                                    'published' => "Published Articles ({$publishedCount})",
                                    default => "All Manuscripts ({$totalCount})"
                                } ?>
                            </h2>
                        </div>
                        <a href="<?= url('admin/article-create.php') ?>" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-lwblue text-white rounded-xl text-xs font-bold hover:bg-lwblue-hover transition self-start sm:self-auto">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Create Article</span>
                        </a>
                    </div>

                    <!-- Category Status Tabs -->
                    <div class="flex flex-wrap items-center gap-2 text-xs border-b border-slate-100 pb-3">
                        <a href="<?= url('pages/dashboard.php?status=all') ?>" 
                           class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'all' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
                            All (<?= $totalCount ?>)
                        </a>
                        <a href="<?= url('pages/dashboard.php?status=draft') ?>" 
                           class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'draft' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
                            Drafts (<?= $draftsCount ?>)
                        </a>
                        <a href="<?= url('pages/dashboard.php?status=pending_review') ?>" 
                           class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'pending_review' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
                            Pending Review (<?= $pendingCount ?>)
                        </a>
                        <a href="<?= url('pages/dashboard.php?status=revision_requested') ?>" 
                           class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'revision_requested' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
                            Revision Requested (<?= $revisionsCount ?>)
                        </a>
                        <a href="<?= url('pages/dashboard.php?status=published') ?>" 
                           class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'published' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
                            Published (<?= $publishedCount ?>)
                        </a>
                    </div>

                    <!-- Article Cards or Empty State -->
                    <?php if (empty($myArticles)): ?>
                        <div class="py-12 text-center max-w-md mx-auto space-y-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-lwblue flex items-center justify-center text-xl mx-auto">
                                <i class="fa-solid fa-file-circle-plus"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-base font-bold text-navy-900">
                                    <?= $totalCount === 0 ? "You haven't created any articles yet." : "No articles found under this status filter." ?>
                                </p>
                                <p class="text-xs text-editorial-muted leading-relaxed">
                                    <?= $totalCount === 0 ? "Begin your contribution to the LagunaWISE Ideology Division by drafting a new monograph or research essay." : "Select another status tab to inspect your other manuscripts." ?>
                                </p>
                            </div>
                            <div class="pt-2">
                                <a href="<?= url('admin/article-create.php') ?>" 
                                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-lwblue text-white rounded-xl text-xs font-bold hover:bg-lwblue-hover transition shadow">
                                    <i class="fa-solid fa-pen-nib text-xs"></i>
                                    <span>Create Your First Article</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($myArticles as $art): ?>
                                <div class="bg-surface p-5 sm:p-6 rounded-2xl border border-slate-200/90 shadow-xs space-y-4 hover:border-slate-300 transition">
                                    
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                                <?= match($art['status']) {
                                                    'published' => 'bg-emerald-100 text-emerald-800',
                                                    'pending_review' => 'bg-amber-100 text-amber-800',
                                                    'revision_requested' => 'bg-rose-100 text-rose-800',
                                                    default => 'bg-slate-200 text-slate-700'
                                                } ?>">
                                                <?= e(str_replace('_', ' ', $art['status'])) ?>
                                            </span>
                                            <?php if (!empty($art['category_name'])): ?>
                                                <span class="text-slate-400 font-medium">• <?= e($art['category_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-slate-400 text-[11px]">
                                            Updated <?= e(format_date($art['updated_at'] ?? $art['created_at'])) ?>
                                        </span>
                                    </div>

                                    <div>
                                        <h3 class="text-base font-bold text-navy-900 leading-snug">
                                            <a href="<?= url('pages/article-preview.php?id=' . (int)$art['id']) ?>" class="hover:text-lwblue transition">
                                                <?= e($art['title']) ?>
                                            </a>
                                        </h3>
                                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed mt-1.5">
                                            <?= e($art['excerpt']) ?>
                                        </p>
                                    </div>

                                    <!-- Revision Feedback Callout if applicable -->
                                    <?php if ($art['status'] === 'revision_requested' && !empty($art['latest_feedback'])): ?>
                                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 space-y-1">
                                            <p class="font-bold flex items-center gap-1.5 text-[11px]">
                                                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                                <span>Editor Revision Guidance:</span>
                                            </p>
                                            <p class="text-amber-800 leading-relaxed"><?= e($art['latest_feedback']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Action Buttons Bar -->
                                    <div class="pt-3 border-t border-slate-200/60 flex flex-wrap items-center justify-between gap-3 text-xs font-bold">
                                        
                                        <div class="flex items-center gap-3">
                                            <a href="<?= url('admin/article-edit.php?id=' . (int)$art['id']) ?>" 
                                               class="text-slate-700 hover:text-navy-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                                <span>Edit</span>
                                            </a>

                                            <a href="<?= url('pages/article-preview.php?id=' . (int)$art['id']) ?>" 
                                               class="text-lwblue hover:underline flex items-center gap-1.5">
                                                <i class="fa-solid fa-eye text-[11px]"></i>
                                                <span>Preview</span>
                                            </a>

                                            <?php if ($art['status'] === 'published'): ?>
                                                <a href="<?= url('pages/article.php?slug=' . urlencode($art['slug'])) ?>" 
                                                   class="text-emerald-700 hover:underline flex items-center gap-1.5">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                    <span>View Live</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <?php if (in_array($art['status'], ['draft', 'revision_requested'], true)): ?>
                                                <form action="<?= url('actions/article-action.php') ?>" method="POST" class="inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="submit_for_review">
                                                    <input type="hidden" name="article_id" value="<?= (int)$art['id'] ?>">
                                                    <input type="hidden" name="return_to" value="<?= url('pages/dashboard.php?status=' . urlencode($statusFilter)) ?>">
                                                    <button type="submit" 
                                                            class="px-3 py-1.5 bg-lwblue text-white rounded-lg hover:bg-lwblue-hover transition text-[11px] font-bold flex items-center gap-1">
                                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                                        <span>Submit for Review</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($art['status'] === 'draft'): ?>
                                                <form action="<?= url('actions/article-action.php') ?>" method="POST" class="inline" onsubmit="return confirm('Delete this draft permanently?')">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="delete_draft">
                                                    <input type="hidden" name="article_id" value="<?= (int)$art['id'] ?>">
                                                    <input type="hidden" name="return_to" value="<?= url('pages/dashboard.php?status=' . urlencode($statusFilter)) ?>">
                                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete Draft">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

            <!-- Supplementary Sidebar Area (Desktop ~34% / 4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Quick Contributor Guide Card -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-xs space-y-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-lwblue">Editorial Standards</span>
                    <h3 class="text-sm font-bold text-navy-900">Publishing Guidelines</h3>
                    <p class="text-xs text-editorial-muted leading-relaxed">
                        All submissions undergo review by Division Editors. Ensure your arguments are grounded in verifiable research and cite empirical sources.
                    </p>
                    <a href="<?= url('pages/about.php#editorial-charter') ?>" class="text-xs font-bold text-lwblue hover:underline block pt-1">
                        Read Editorial Charter &rarr;
                    </a>
                </div>

                <!-- Bookmarked Articles Card (Strict Empty State per Section 3.2) -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-regular fa-bookmark text-lwblue"></i>
                            <span>Personal Reading List (<?= count($bookmarkedArticles) ?>)</span>
                        </h3>
                    </div>

                    <?php if (empty($bookmarkedArticles)): ?>
                        <div class="py-6 text-center text-xs text-slate-500 space-y-2">
                            <p class="font-medium text-slate-700">You haven't bookmarked any articles yet.</p>
                            <p class="text-[11px] text-slate-400">When reading insights, click "Bookmark" to build your reading queue.</p>
                            <div class="pt-1">
                                <a href="<?= url('pages/articles.php') ?>" class="inline-block px-3 py-1.5 bg-surface text-lwblue font-bold rounded hover:bg-slate-100">
                                    Explore Articles
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($bookmarkedArticles as $bArt): ?>
                                <div class="p-3 bg-surface rounded-md border border-slate-100 text-xs flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="<?= url('pages/article.php?slug=' . urlencode($bArt['slug'])) ?>" class="font-bold text-navy-900 hover:text-lwblue line-clamp-1">
                                            <?= e($bArt['title']) ?>
                                        </a>
                                        <span class="text-[10px] text-slate-400 block"><?= e($bArt['category_name'] ?? 'Research') ?></span>
                                    </div>
                                    <button type="button" 
                                            class="bookmark-toggle-btn text-rose-500 hover:text-rose-700 shrink-0 p-1" 
                                            data-article-id="<?= (int)$bArt['id'] ?>"
                                            onclick="this.closest('.p-3').remove()">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Discussion & Comment History Card -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-regular fa-comments text-lwblue"></i>
                            <span>My Comments (<?= count($myComments) ?>)</span>
                        </h3>
                    </div>

                    <?php if (empty($myComments)): ?>
                        <div class="py-6 text-center text-xs text-slate-400">
                            <p>You haven't participated in any article discussions yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($myComments as $comm): ?>
                                <div class="p-3 bg-surface rounded-md border border-slate-100 text-xs space-y-1">
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($comm['article_slug'])) ?>" class="font-bold text-navy-900 hover:text-lwblue line-clamp-1 block">
                                        <?= e($comm['article_title']) ?>
                                    </a>
                                    <p class="text-slate-600 text-[11px] line-clamp-2">"<?= e($comm['content']) ?>"</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Newsletter Subscription Card -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider">Ideology Dispatch</h3>
                    <p class="text-xs text-slate-600">
                        Status: <strong class="<?= $isSubscribed ? 'text-emerald-600' : 'text-slate-500' ?>"><?= $isSubscribed ? 'Subscribed' : 'Not Subscribed' ?></strong>
                    </p>
                    <form action="<?= url('actions/newsletter-action.php') ?>" method="POST">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="email" value="<?= e($user['email']) ?>">
                        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold border transition <?= $isSubscribed ? 'border-slate-300 text-slate-700 hover:bg-slate-50' : 'bg-lwblue text-white hover:bg-lwblue-hover' ?>">
                            <?= $isSubscribed ? 'Update Subscription' : 'Subscribe with Account Email' ?>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
