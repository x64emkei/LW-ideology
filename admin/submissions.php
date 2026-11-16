<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['administrator', 'super_admin', 'admin', 'editor']);

$db = Database::getConnection();
$user = current_user();

// Handle Review Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $action = $_POST['action'] ?? '';
    $articleId = (int)($_POST['article_id'] ?? 0);
    $feedback = Security::sanitize($_POST['feedback'] ?? '');

    if ($articleId > 0) {
        // Fetch article
        $artStmt = $db->prepare("SELECT * FROM articles WHERE id = ? LIMIT 1");
        $artStmt->execute([$articleId]);
        $art = $artStmt->fetch();

        if ($art) {
            if ($action === 'approve') {
                $db->prepare("UPDATE articles SET status = 'approved', updated_at = NOW() WHERE id = ?")->execute([$articleId]);
                $db->prepare("
                    INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at)
                    VALUES (?, ?, 'approved', ?, NOW())
                ")->execute([$articleId, $user['id'], !empty($feedback) ? $feedback : 'Submission approved by editorial board.']);
                log_activity((int)$user['id'], 'article_approved', 'article', $articleId, "Approved article #{$articleId}");
                flash('success', "Article '{$art['title']}' was approved.");
            } elseif ($action === 'request_revision') {
                $db->prepare("UPDATE articles SET status = 'revision_requested', updated_at = NOW() WHERE id = ?")->execute([$articleId]);
                $db->prepare("
                    INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at)
                    VALUES (?, ?, 'revision_requested', ?, NOW())
                ")->execute([$articleId, $user['id'], !empty($feedback) ? $feedback : 'Revision requested by editorial reviewer.']);
                log_activity((int)$user['id'], 'revision_requested', 'article', $articleId, "Revision requested for article #{$articleId}");
                flash('warning', "Revision requested for '{$art['title']}'. Author has been notified.");
            } elseif ($action === 'publish') {
                $db->prepare("UPDATE articles SET status = 'published', published_at = NOW(), updated_at = NOW() WHERE id = ?")->execute([$articleId]);
                $db->prepare("
                    INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at)
                    VALUES (?, ?, 'published', ?, NOW())
                ")->execute([$articleId, $user['id'], !empty($feedback) ? $feedback : 'Approved and published to live website.']);
                log_activity((int)$user['id'], 'article_published', 'article', $articleId, "Published article #{$articleId}");
                flash('success', "Article '{$art['title']}' is now published live on the website.");
            } elseif ($action === 'reject') {
                $db->prepare("UPDATE articles SET status = 'archived', updated_at = NOW() WHERE id = ?")->execute([$articleId]);
                $db->prepare("
                    INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at)
                    VALUES (?, ?, 'rejected', ?, NOW())
                ")->execute([$articleId, $user['id'], !empty($feedback) ? $feedback : 'Submission rejected and archived.']);
                log_activity((int)$user['id'], 'article_rejected', 'article', $articleId, "Rejected and archived article #{$articleId}");
                flash('info', "Article '{$art['title']}' was rejected and archived.");
            }
        }
    }

    header('Location: ' . url('admin/submissions.php'));
    exit;
}

$statusFilter = trim($_GET['status'] ?? 'pending_review');

$where = ['1=1'];
$params = [];

if ($statusFilter === 'all') {
    $where[] = "a.status IN ('pending_review', 'revision_requested', 'approved', 'published', 'archived')";
} elseif (!empty($statusFilter)) {
    $where[] = "a.status = ?";
    $params[] = $statusFilter;
}

$whereSql = implode(' AND ', $where);

$query = "
    SELECT a.*, u.name AS author_name, u.email AS author_email,
           (SELECT c.name FROM categories c JOIN article_categories ac ON c.id = ac.category_id WHERE ac.article_id = a.id LIMIT 1) AS category_name,
           (SELECT feedback FROM editorial_reviews er WHERE er.article_id = a.id ORDER BY er.created_at DESC LIMIT 1) AS latest_feedback
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE {$whereSql}
    ORDER BY a.created_at ASC
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Counts for tabs
$pendingCount = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'pending_review'")->fetchColumn();
$revisionCount = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'revision_requested'")->fetchColumn();
$approvedCount = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'approved'")->fetchColumn();

$adminPageTitle = 'Editorial Submissions Queue';
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Peer Review Pipeline</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Editorial Submissions</h1>
        <p class="text-xs text-slate-500 mt-0.5">Evaluate incoming author monographs, request revisions, and approve for publication.</p>
    </div>
</div>

<!-- Queue Navigation Tabs -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-2 text-xs">
    <a href="<?= url('admin/submissions.php?status=pending_review') ?>" 
       class="px-3.5 py-2 rounded-xl transition <?= $statusFilter === 'pending_review' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
        <span>Awaiting Review</span>
        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] <?= $statusFilter === 'pending_review' ? 'bg-amber-400 text-navy-900' : 'bg-slate-100 text-slate-600' ?>">
            <?= $pendingCount ?>
        </span>
    </a>

    <a href="<?= url('admin/submissions.php?status=revision_requested') ?>" 
       class="px-3.5 py-2 rounded-xl transition <?= $statusFilter === 'revision_requested' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
        <span>Revision Requested</span>
        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] <?= $statusFilter === 'revision_requested' ? 'bg-rose-400 text-white' : 'bg-slate-100 text-slate-600' ?>">
            <?= $revisionCount ?>
        </span>
    </a>

    <a href="<?= url('admin/submissions.php?status=approved') ?>" 
       class="px-3.5 py-2 rounded-xl transition <?= $statusFilter === 'approved' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
        <span>Approved for Release</span>
        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] <?= $statusFilter === 'approved' ? 'bg-emerald-400 text-navy-900' : 'bg-slate-100 text-slate-600' ?>">
            <?= $approvedCount ?>
        </span>
    </a>

    <a href="<?= url('admin/submissions.php?status=all') ?>" 
       class="px-3.5 py-2 rounded-xl transition <?= $statusFilter === 'all' ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
        <span>All Pipeline Submissions</span>
    </a>
</div>

<!-- Submissions Queue List -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <?php if (empty($submissions)): ?>
        <!-- Mandatory Empty State per Section 3.2 -->
        <div class="py-16 text-center max-w-md mx-auto space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mx-auto">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <h3 class="text-base font-bold text-navy-900">No submissions are awaiting review.</h3>
            <p class="text-xs text-editorial-muted leading-relaxed">
                The editorial review queue is currently clear. Submitted monographs from contributors will appear here for evaluation and governance.
            </p>
        </div>
    <?php else: ?>
        <div class="divide-y divide-slate-100">
            <?php foreach ($submissions as $sub): ?>
                <div class="p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6 hover:bg-slate-50 transition">
                    
                    <div class="space-y-2 max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                <?= match($sub['status']) {
                                    'published' => 'bg-emerald-100 text-emerald-800',
                                    'approved' => 'bg-emerald-100 text-emerald-800',
                                    'pending_review' => 'bg-amber-100 text-amber-800',
                                    'revision_requested' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-slate-100 text-slate-700'
                                } ?>">
                                <?= e(str_replace('_', ' ', $sub['status'])) ?>
                            </span>
                            <?php if (!empty($sub['category_name'])): ?>
                                <span class="text-slate-500 font-semibold">• <?= e($sub['category_name']) ?></span>
                            <?php endif; ?>
                            <span class="text-slate-400">• Submitted <?= e(format_date($sub['created_at'])) ?></span>
                        </div>

                        <h3 class="text-lg font-bold text-navy-900 leading-snug">
                            <a href="<?= url('pages/article-preview.php?id=' . (int)$sub['id']) ?>" class="hover:text-lwblue transition">
                                <?= e($sub['title']) ?>
                            </a>
                        </h3>

                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                            <?= e($sub['excerpt']) ?>
                        </p>

                        <div class="pt-1 flex items-center gap-2 text-xs text-slate-500">
                            <span>Author: <strong><?= e($sub['author_name']) ?></strong> (<?= e($sub['author_email']) ?>)</span>
                            <span>•</span>
                            <span><?= (int)$sub['reading_time'] ?> min read</span>
                        </div>

                        <?php if (!empty($sub['latest_feedback'])): ?>
                            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 mt-2">
                                <strong>Previous Note:</strong> <?= e($sub['latest_feedback']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Controls -->
                    <div class="flex flex-wrap items-center gap-2 self-start lg:self-auto shrink-0">
                        <a href="<?= url('pages/article-preview.php?id=' . (int)$sub['id']) ?>" 
                           class="px-3.5 py-2 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-xs"></i>
                            <span>Read Manuscript</span>
                        </a>

                        <a href="<?= url('admin/article-edit.php?id=' . (int)$sub['id']) ?>" 
                           class="px-3.5 py-2 bg-navy-900 hover:bg-navy-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                            <span>Review &amp; Edit</span>
                        </a>

                        <!-- Quick Approve Form -->
                        <form action="<?= url('admin/submissions.php') ?>" method="POST" class="inline">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="article_id" value="<?= (int)$sub['id'] ?>">
                            <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5" title="Approve Monograph">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span>Approve</span>
                            </button>
                        </form>

                        <!-- Quick Publish Form -->
                        <form action="<?= url('admin/submissions.php') ?>" method="POST" class="inline" onsubmit="return confirm('Publish this article live to the public website?');">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="action" value="publish">
                            <input type="hidden" name="article_id" value="<?= (int)$sub['id'] ?>">
                            <button type="submit" class="px-3.5 py-2 bg-lwblue hover:bg-lwblue-hover text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5" title="Publish to Live Website">
                                <i class="fa-solid fa-globe text-xs"></i>
                                <span>Publish</span>
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
