<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin', 'editor']);

$db = Database::getConnection();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $action = $_POST['action'] ?? '';
    $commentId = (int)($_POST['comment_id'] ?? 0);

    if ($commentId > 0) {
        if ($action === 'approve') {
            $db->prepare("UPDATE comments SET status = 'approved' WHERE id = ?")->execute([$commentId]);
            log_activity((int)$user['id'], 'comment_approved', 'comment', $commentId, "Approved comment #{$commentId}");
            flash('success', "Comment approved.");
        } elseif ($action === 'spam') {
            $db->prepare("UPDATE comments SET status = 'spam' WHERE id = ?")->execute([$commentId]);
            log_activity((int)$user['id'], 'comment_spammed', 'comment', $commentId, "Marked comment #{$commentId} as spam");
            flash('warning', "Comment marked as spam.");
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM comments WHERE id = ?")->execute([$commentId]);
            log_activity((int)$user['id'], 'comment_deleted', 'comment', $commentId, "Deleted comment #{$commentId}");
            flash('success', "Comment deleted.");
        }
    }

    header('Location: ' . url('admin/comments.php'));
    exit;
}

// Fetch comments
$comments = $db->query("
    SELECT c.*, a.title AS article_title, a.slug AS article_slug
    FROM comments c
    JOIN articles a ON c.article_id = a.id
    ORDER BY c.created_at DESC
")->fetchAll();

$adminPageTitle = 'Discussion Moderation';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Community Deliberation</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Comments &amp; Inquiries</h1>
        <p class="text-xs text-slate-500 mt-0.5">Moderate public discourse to ensure adherence to nonpartisan, evidence-based standards.</p>
    </div>
</div>

<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-surface text-slate-400 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200">
                    <th class="py-3.5 px-6">Author &amp; Email</th>
                    <th class="py-3.5 px-4">Article</th>
                    <th class="py-3.5 px-6">Comment Content</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Submitted</th>
                    <th class="py-3.5 px-6 text-right">Moderation Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">No comments submitted yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comments as $comm): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-bold text-navy-900 whitespace-nowrap">
                                <p><?= e($comm['author_name']) ?></p>
                                <p class="text-[10px] text-slate-400 font-normal"><?= e($comm['author_email']) ?></p>
                            </td>

                            <td class="py-4 px-4 font-semibold text-lwblue max-w-xs truncate">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($comm['article_slug'])) ?>" target="_blank" class="hover:underline">
                                    <?= e($comm['article_title']) ?>
                                </a>
                            </td>

                            <td class="py-4 px-6 text-slate-600 max-w-md">
                                <p class="line-clamp-3 bg-surface p-2.5 rounded-xl border border-slate-100">
                                    <?= e($comm['content']) ?>
                                </p>
                            </td>

                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    <?= match($comm['status']) {
                                        'approved' => 'bg-emerald-100 text-emerald-800',
                                        'spam' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-amber-100 text-amber-800'
                                    } ?>">
                                    <?= e($comm['status']) ?>
                                </span>
                            </td>

                            <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                <?= e(format_date($comm['created_at'])) ?>
                            </td>

                            <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                                <?php if ($comm['status'] !== 'approved'): ?>
                                    <form action="<?= url('admin/comments.php') ?>" method="POST" class="inline">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="comment_id" value="<?= (int)$comm['id'] ?>">
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-800" title="Approve">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($comm['status'] !== 'spam'): ?>
                                    <form action="<?= url('admin/comments.php') ?>" method="POST" class="inline">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="action" value="spam">
                                        <input type="hidden" name="comment_id" value="<?= (int)$comm['id'] ?>">
                                        <button type="submit" class="p-1.5 text-amber-600 hover:text-amber-800" title="Mark as Spam">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="<?= url('admin/comments.php') ?>" method="POST" class="inline" onsubmit="return confirm('Delete this comment?')">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="comment_id" value="<?= (int)$comm['id'] ?>">
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
