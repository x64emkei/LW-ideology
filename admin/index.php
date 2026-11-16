<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$adminPageTitle = 'Dashboard Overview';
$db = Database::getConnection();

// Metrics
$totalArticles = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$publishedArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
$pendingArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'pending_review'")->fetchColumn();
$draftArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")->fetchColumn();

$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalContributors = (int)$db->query("SELECT COUNT(DISTINCT user_id) FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.name IN ('contributor', 'writer', 'editor')")->fetchColumn();
$totalAnnouncements = (int)$db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
$totalComments = (int)$db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$newsletterSubs = (int)$db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed'")->fetchColumn();

// Recent Submissions Queue (Pending Review)
$pendingQueue = $db->query("
    SELECT a.*, u.name AS author_name, u.email AS author_email
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'pending_review'
    ORDER BY a.created_at ASC
    LIMIT 5
")->fetchAll();

// Recent Activity Logs
$recentLogs = $db->query("
    SELECT l.*, u.name AS user_name, u.username
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 8
")->fetchAll();

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Welcome Banner -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Editorial Headquarters</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Platform Overview</h1>
        <p class="text-xs text-slate-500 mt-0.5">Real-time metrics, review pipeline, and administrative auditing.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= url('admin/article-create.php') ?>" class="px-4 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
            <i class="fa-solid fa-pen-nib"></i>
            <span>Create Article</span>
        </a>
    </div>
</div>

<!-- Metrics Statistics Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Articles</p>
            <p class="text-2xl font-extrabold text-navy-900 mt-1"><?= $totalArticles ?></p>
            <span class="text-[10px] text-emerald-600 font-semibold"><?= $publishedArticles ?> published</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-lwblue flex items-center justify-center text-lg">
            <i class="fa-solid fa-newspaper"></i>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Review</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1"><?= $pendingArticles ?></p>
            <span class="text-[10px] text-slate-500 font-semibold"><?= $draftArticles ?> local drafts</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-extrabold text-navy-900 mt-1"><?= $totalUsers ?></p>
            <span class="text-[10px] text-lwblue font-semibold"><?= $totalContributors ?> contributors / editors</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Announcements</p>
            <p class="text-2xl font-extrabold text-navy-900 mt-1"><?= $totalAnnouncements ?></p>
            <span class="text-[10px] text-slate-500 font-semibold"><?= $newsletterSubs ?> subscribers</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
    </div>

</div>

<!-- Content Grid: Pending Review Queue & Activity Audit -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Pending Submissions Queue (7 cols) -->
    <div class="lg:col-span-7 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-base font-bold text-navy-900 flex items-center gap-2">
                <i class="fa-solid fa-inbox text-amber-500"></i>
                <span>Editorial Review Queue</span>
            </h2>
            <a href="<?= url('admin/submissions.php') ?>" class="text-xs font-bold text-lwblue hover:underline">
                View All Pending (<?= $pendingArticles ?>)
            </a>
        </div>

        <?php if (empty($pendingQueue)): ?>
            <div class="p-8 text-center text-xs text-slate-400 space-y-2">
                <i class="fa-solid fa-circle-check text-2xl text-emerald-400 mb-2"></i>
                <p class="font-bold text-slate-700 text-sm">No submissions are awaiting review.</p>
                <p>All submitted monographs have been reviewed and evaluated.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($pendingQueue as $pArt): ?>
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">
                                Awaiting Review
                            </span>
                            <h3 class="text-sm font-bold text-navy-900 leading-snug">
                                <a href="<?= url('admin/article-edit.php?id=' . (int)$pArt['id']) ?>" class="hover:text-lwblue">
                                    <?= e($pArt['title']) ?>
                                </a>
                            </h3>
                            <p class="text-[11px] text-slate-400">
                                Submitted by <strong><?= e($pArt['author_name']) ?></strong> • <?= e(format_date($pArt['created_at'])) ?>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="<?= url('admin/article-edit.php?id=' . (int)$pArt['id']) ?>" 
                               class="px-3 py-1.5 bg-navy-900 hover:bg-navy-800 text-white rounded-lg text-xs font-bold transition">
                                Review &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent System Activity Audit Log (5 cols) -->
    <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-base font-bold text-navy-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-lwblue"></i>
                <span>Recent System Audit</span>
            </h2>
            <span class="text-[11px] text-slate-400">Live logs</span>
        </div>

        <div class="space-y-3">
            <?php foreach ($recentLogs as $log): ?>
                <div class="p-3 bg-surface rounded-xl border border-slate-100 text-xs space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold">
                        <span class="text-navy-900 font-bold"><?= e($log['user_name'] ?? 'System Process') ?></span>
                        <span><?= e(date('M j, g:i a', strtotime($log['created_at']))) ?></span>
                    </div>
                    <p class="text-slate-700 font-medium">
                        <?= e($log['details'] ?? $log['action']) ?>
                    </p>
                    <span class="inline-block text-[9px] font-mono text-slate-400">IP: <?= e($log['ip_address'] ?? '127.0.0.1') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
