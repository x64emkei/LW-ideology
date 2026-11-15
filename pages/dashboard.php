<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_login();
$user = current_user();
$db = Database::getConnection();

// Fetch Reader's Bookmarked Articles
$bmStmt = $db->prepare("
    SELECT a.*, b.created_at AS bookmarked_at,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name
    FROM bookmarks b
    JOIN articles a ON b.article_id = a.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$bmStmt->execute([$user['id']]);
$bookmarkedArticles = $bmStmt->fetchAll();

// Fetch Reader's Submitted Comments
$commStmt = $db->prepare("
    SELECT c.*, a.title AS article_title, a.slug AS article_slug
    FROM comments c
    JOIN articles a ON c.article_id = a.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$commStmt->execute([$user['id']]);
$myComments = $commStmt->fetchAll();

// Check if subscribed to newsletter
$subStmt = $db->prepare("SELECT status FROM newsletter_subscribers WHERE email = ? LIMIT 1");
$subStmt->execute([$user['email']]);
$isSubscribed = ($subStmt->fetchColumn() === 'subscribed');

// If user is a writer/editor/admin, fetch their authored articles
$myArticles = [];
if (user_has_any_role(['super_admin', 'admin', 'editor', 'writer'])) {
    $artStmt = $db->prepare("
        SELECT a.*,
               (SELECT feedback FROM editorial_reviews er WHERE er.article_id = a.id ORDER BY er.created_at DESC LIMIT 1) AS latest_feedback
        FROM articles a
        WHERE a.author_id = ?
        ORDER BY a.created_at DESC
    ");
    $artStmt->execute([$user['id']]);
    $myArticles = $artStmt->fetchAll();
}

$pageTitle = 'User Dashboard — ' . $user['name'];
$pageDescription = 'Access your personal bookmarks, contribution history, and reading preferences.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="bg-navy-900 text-white py-12 border-b border-navy-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                <img src="<?= asset($user['avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                     alt="<?= e($user['name']) ?>" 
                     class="w-20 h-20 rounded-full object-cover border-4 border-navy-700 shadow-xl">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h1 class="text-2xl font-extrabold text-white"><?= e($user['name']) ?></h1>
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-lwblue text-white uppercase tracking-wider">
                            <?= e(str_replace('_', ' ', $user['primary_role'])) ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">@<?= e($user['username']) ?> • Member since <?= e(format_date($user['created_at'])) ?></p>
                    <p class="text-xs text-slate-300 max-w-lg mt-1"><?= e($user['bio'] ?? 'Community reader and critical thinker engaging with the LagunaWISE Ideology Division.') ?></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <?php if (user_has_any_role(['super_admin', 'admin', 'editor', 'writer'])): ?>
                    <a href="<?= url('admin/article-create.php') ?>" class="px-4 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
                        <i class="fa-solid fa-pen-nib"></i>
                        <span>Draft Article</span>
                    </a>
                <?php endif; ?>

                <?php if (user_has_any_role(['super_admin', 'admin', 'editor'])): ?>
                    <a href="<?= url('admin/index.php') ?>" class="px-4 py-2.5 bg-navy-800 hover:bg-navy-700 border border-navy-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-lwblue-light"></i>
                        <span>Staff CMS</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('admin/profile.php') ?>" class="px-4 py-2.5 bg-white text-navy-900 hover:bg-slate-100 text-xs font-bold rounded-xl transition">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-12">
        
        <!-- Writer Manuscripts Section (if applicable) -->
        <?php if (!empty($myArticles)): ?>
            <div class="space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Author Portal</span>
                        <h2 class="text-xl font-bold text-navy-900 mt-0.5">My Research Articles &amp; Drafts (<?= count($myArticles) ?>)</h2>
                    </div>
                    <a href="<?= url('admin/article-create.php') ?>" class="text-xs font-bold text-lwblue hover:underline">+ New Article</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($myArticles as $art): ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        <?= match($art['status']) {
                                            'published' => 'bg-emerald-100 text-emerald-800',
                                            'pending_review' => 'bg-amber-100 text-amber-800',
                                            'revision_requested' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-slate-100 text-slate-700'
                                        } ?>">
                                        <?= e(str_replace('_', ' ', $art['status'])) ?>
                                    </span>
                                    <span class="text-slate-400"><?= e(format_date($art['created_at'])) ?></span>
                                </div>
                                <h3 class="text-base font-bold text-navy-900 leading-snug">
                                    <a href="<?= url('admin/article-edit.php?id=' . (int)$art['id']) ?>" class="hover:text-lwblue">
                                        <?= e($art['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    <?= e($art['excerpt']) ?>
                                </p>
                                <?php if (!empty($art['latest_feedback'])): ?>
                                    <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-[11px] text-amber-900">
                                        <strong>Editor Feedback:</strong> <?= e($art['latest_feedback']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                                <a href="<?= url('admin/article-edit.php?id=' . (int)$art['id']) ?>" class="text-slate-700 hover:text-navy-900">
                                    <i class="fa-solid fa-pen text-[10px] mr-1"></i> Edit Manuscript
                                </a>
                                <?php if ($art['status'] === 'published'): ?>
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($art['slug'])) ?>" class="text-lwblue hover:underline">
                                        View Live &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reader Bookmarks Section -->
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Personal Reading List</span>
                    <h2 class="text-xl font-bold text-navy-900 mt-0.5">Bookmarked Articles (<?= count($bookmarkedArticles) ?>)</h2>
                </div>
            </div>

            <?php if (empty($bookmarkedArticles)): ?>
                <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-xs text-slate-500 max-w-md mx-auto space-y-3">
                    <i class="fa-regular fa-bookmark text-2xl text-slate-400"></i>
                    <p>You have not bookmarked any articles yet. When browsing insights, click "Bookmark" to build your reading queue.</p>
                    <a href="<?= url('pages/articles.php') ?>" class="inline-block px-4 py-2 bg-lwblue text-white rounded-xl font-bold">Explore Articles</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($bookmarkedArticles as $art): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs flex flex-col justify-between group">
                            <div class="p-6 space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span><?= e($art['category_name'] ?? 'Research') ?></span>
                                    <span>Bookmarked <?= e(format_date($art['bookmarked_at'])) ?></span>
                                </div>
                                <h3 class="text-base font-bold text-navy-900 group-hover:text-lwblue transition leading-snug">
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($art['slug'])) ?>">
                                        <?= e($art['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    <?= e($art['excerpt']) ?>
                                </p>
                            </div>
                            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($art['slug'])) ?>" class="text-lwblue hover:underline">
                                    Read Article &rarr;
                                </a>
                                <button type="button" 
                                        class="bookmark-toggle-btn text-rose-600 hover:text-rose-800" 
                                        data-article-id="<?= (int)$art['id'] ?>"
                                        onclick="this.closest('.group').remove()">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reader Comments History Section -->
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Civic Participation</span>
                    <h2 class="text-xl font-bold text-navy-900 mt-0.5">My Submitted Comments (<?= count($myComments) ?>)</h2>
                </div>
            </div>

            <?php if (empty($myComments)): ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                    <p>You haven't participated in any article discussions yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($myComments as $comm): ?>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 text-xs space-y-2">
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>On: <a href="<?= url('pages/article.php?slug=' . urlencode($comm['article_slug']) . '#comments') ?>" class="text-lwblue font-bold hover:underline"><?= e($comm['article_title']) ?></a></span>
                                <span><?= e(format_date($comm['created_at'])) ?></span>
                            </div>
                            <p class="text-slate-700 bg-surface p-3 rounded-xl border border-slate-100">
                                "<?= e($comm['content']) ?>"
                            </p>
                            <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                <?= e($comm['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Preferences Section -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-navy-900">Communication &amp; Newsletter Status</h3>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-800">Ideology Division Fortnightly Dispatch</p>
                    <p class="text-xs text-slate-500">Current status: <strong class="<?= $isSubscribed ? 'text-emerald-600' : 'text-slate-600' ?>"><?= $isSubscribed ? 'Active Subscriber' : 'Not Subscribed' ?></strong> (<?= e($user['email']) ?>)</p>
                </div>
                <form action="<?= url('actions/newsletter-action.php') ?>" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="email" value="<?= e($user['email']) ?>">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold border <?= $isSubscribed ? 'border-slate-300 text-slate-700 hover:bg-slate-50' : 'bg-lwblue text-white hover:bg-lwblue-hover' ?>">
                        <?= $isSubscribed ? 'Update / Confirm' : 'Subscribe Now' ?>
                    </button>
                </form>
            </div>
        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
