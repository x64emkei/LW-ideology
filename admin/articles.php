<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin', 'editor']);

$db = Database::getConnection();

// Handle quick status changes / actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $action = $_POST['action'] ?? '';
    $articleId = (int)($_POST['article_id'] ?? 0);

    if ($articleId > 0) {
        $user = current_user();

        if ($action === 'delete') {
            // Only admin/super_admin can delete
            if (user_has_any_role(['super_admin', 'admin'])) {
                $del = $db->prepare("DELETE FROM articles WHERE id = ?");
                $del->execute([$articleId]);
                log_activity((int)$user['id'], 'article_deleted', 'article', $articleId, "Article #{$articleId} deleted.");
                flash('success', "Article #{$articleId} was successfully deleted.");
            } else {
                flash('error', 'Only administrators are authorized to permanently delete monographs.');
            }
        } elseif ($action === 'publish') {
            $pub = $db->prepare("UPDATE articles SET status = 'published', published_at = NOW() WHERE id = ?");
            $pub->execute([$articleId]);
            log_activity((int)$user['id'], 'article_published', 'article', $articleId, "Article #{$articleId} marked as published.");
            flash('success', "Article published successfully to the public website.");
        } elseif ($action === 'unpublish') {
            $unpub = $db->prepare("UPDATE articles SET status = 'draft' WHERE id = ?");
            $unpub->execute([$articleId]);
            log_activity((int)$user['id'], 'article_unpublished', 'article', $articleId, "Article #{$articleId} returned to draft.");
            flash('info', "Article unpublished and reverted to draft status.");
        } elseif ($action === 'archive') {
            $arch = $db->prepare("UPDATE articles SET status = 'archived' WHERE id = ?");
            $arch->execute([$articleId]);
            log_activity((int)$user['id'], 'article_archived', 'article', $articleId, "Article #{$articleId} archived.");
            flash('warning', "Article has been archived.");
        }
    }

    header('Location: ' . url('admin/articles.php'));
    exit;
}

// Filters
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = ['1=1'];
$params = [];

if (!empty($statusFilter)) {
    $where[] = 'a.status = ?';
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $where[] = '(a.title LIKE ? OR a.excerpt LIKE ? OR u.name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

$query = "
    SELECT a.*, u.name AS author_name,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE {$whereSql}
    ORDER BY a.created_at DESC
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$adminPageTitle = 'Articles & Editorial Management';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Editorial Publications</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Articles Management</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage, review, publish, and govern all editorial monographs.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= url('admin/article-create.php') ?>" class="px-4 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Create New Article</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between text-xs">
    
    <div class="flex flex-wrap items-center gap-1.5 font-semibold">
        <a href="<?= url('admin/articles.php') ?>" class="px-3 py-1.5 rounded-lg <?= empty($statusFilter) ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">All Articles</a>
        <a href="<?= url('admin/articles.php?status=published') ?>" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'published' ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">Published</a>
        <a href="<?= url('admin/articles.php?status=pending_review') ?>" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'pending_review' ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">Pending Review</a>
        <a href="<?= url('admin/articles.php?status=draft') ?>" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'draft' ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">Drafts</a>
        <a href="<?= url('admin/articles.php?status=archived') ?>" class="px-3 py-1.5 rounded-lg <?= $statusFilter === 'archived' ? 'bg-navy-900 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">Archived</a>
    </div>

    <form action="<?= url('admin/articles.php') ?>" method="GET" class="relative max-w-xs w-full">
        <?php if (!empty($statusFilter)): ?>
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
        <?php endif; ?>
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search title or author..."
               class="w-full pl-8 pr-3 py-2 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
    </form>

</div>

<!-- Table Card -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-surface text-slate-400 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200">
                    <th class="py-3.5 px-6">Title &amp; Excerpt</th>
                    <th class="py-3.5 px-4">Author</th>
                    <th class="py-3.5 px-4">Category</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Views</th>
                    <th class="py-3.5 px-4">Date</th>
                    <th class="py-3.5 px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">No articles found matching criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $art): ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 max-w-sm">
                                <a href="<?= url('admin/article-edit.php?id=' . (int)$art['id']) ?>" class="font-bold text-navy-900 hover:text-lwblue line-clamp-1 text-sm">
                                    <?= e($art['title']) ?>
                                </a>
                                <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5"><?= e($art['excerpt']) ?></p>
                            </td>

                            <td class="py-4 px-4 font-semibold text-slate-800">
                                <?= e($art['author_name']) ?>
                            </td>

                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    <?= e($art['category_name'] ?? 'General') ?>
                                </span>
                            </td>

                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    <?= match($art['status']) {
                                        'published' => 'bg-emerald-100 text-emerald-800',
                                        'pending_review' => 'bg-amber-100 text-amber-800',
                                        'revision_requested' => 'bg-rose-100 text-rose-800',
                                        'archived' => 'bg-slate-200 text-slate-700',
                                        default => 'bg-slate-100 text-slate-600'
                                    } ?>">
                                    <?= e(str_replace('_', ' ', $art['status'])) ?>
                                </span>
                            </td>

                            <td class="py-4 px-4 font-semibold text-slate-600">
                                <?= number_format((int)$art['views_count']) ?>
                            </td>

                            <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                <?= e(format_date($art['published_at'] ?? $art['created_at'])) ?>
                            </td>

                            <td class="py-4 px-6 text-right whitespace-nowrap space-x-2">
                                <?php if ($art['status'] === 'published'): ?>
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($art['slug'])) ?>" target="_blank" class="p-1.5 text-slate-400 hover:text-lwblue" title="View Public Article">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="<?= url('admin/article-edit.php?id=' . (int)$art['id']) ?>" class="p-1.5 text-slate-600 hover:text-navy-900 font-bold" title="Edit Article">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <?php if ($art['status'] !== 'published' && user_has_any_role(['super_admin', 'admin', 'editor'])): ?>
                                    <form action="<?= url('admin/articles.php') ?>" method="POST" class="inline">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="action" value="publish">
                                        <input type="hidden" name="article_id" value="<?= (int)$art['id'] ?>">
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-800" title="Publish Article" onclick="return confirm('Publish this article to the live website?')">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (user_has_any_role(['super_admin', 'admin'])): ?>
                                    <form action="<?= url('admin/articles.php') ?>" method="POST" class="inline" onsubmit="return confirm('Permanently delete this article? This action cannot be undone.')">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="article_id" value="<?= (int)$art['id'] ?>">
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete Article">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
