<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['administrator', 'super_admin', 'admin']);

$db = Database::getConnection();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = Security::sanitize($_POST['title'] ?? '');
        $summary = Security::sanitize($_POST['summary'] ?? '');
        $content = Security::sanitize($_POST['content'] ?? '');
        $badgeType = $_POST['badge_type'] ?? 'announcement';
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if (!empty($title) && !empty($summary) && !empty($content)) {
            $slug = slugify($title);
            $stmt = $db->prepare("
                INSERT INTO announcements (title, slug, summary, content, badge_type, is_published, published_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$title, $slug, $summary, $content, $badgeType, $isPublished]);
            log_activity((int)$user['id'], 'announcement_created', 'announcement', (int)$db->lastInsertId(), "Created announcement: {$title}");
            flash('success', "Announcement released successfully.");
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
            log_activity((int)$user['id'], 'announcement_deleted', 'announcement', $id, "Deleted announcement #{$id}");
            flash('success', "Announcement deleted.");
        }
    }

    header('Location: ' . url('admin/announcements.php'));
    exit;
}

// Fetch announcements
$announcements = $db->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

$adminPageTitle = 'News & Campaign Announcements';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Communications</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">News &amp; Campaigns</h1>
        <p class="text-xs text-slate-500 mt-0.5">Publish community dispatches, event calls, and literacy initiatives.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Create Announcement Form (4 cols) -->
    <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
            Publish New Update
        </h2>

        <form action="<?= url('admin/announcements.php') ?>" method="POST" class="space-y-4">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Title *</label>
                <input type="text" name="title" required placeholder="e.g. Media Literacy Workshop 2026"
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Classification Badge</label>
                <select name="badge_type" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-lwblue">
                    <option value="announcement">Announcement</option>
                    <option value="campaign">Advocacy Campaign</option>
                    <option value="activity">Community Activity</option>
                    <option value="initiative">Division Initiative</option>
                    <option value="publication">Monograph Release</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Summary / Lead *</label>
                <textarea name="summary" rows="2" required placeholder="A brief one-sentence headline summary..."
                          class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Full Announcement Details *</label>
                <textarea name="content" rows="4" required placeholder="Detailed information, workshop schedules, or paper submission guidelines..."
                          class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                <input type="checkbox" name="is_published" value="1" checked class="rounded text-lwblue focus:ring-lwblue">
                <span>Publish Immediately</span>
            </label>

            <button type="submit" class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                <i class="fa-solid fa-bullhorn text-xs"></i>
                <span>Release Announcement</span>
            </button>
        </form>
    </div>

    <!-- Announcement Table (8 cols) -->
    <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface text-slate-400 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200">
                        <th class="py-3.5 px-6">Announcement Title</th>
                        <th class="py-3.5 px-4">Badge</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Published</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($announcements)): ?>
                        <tr><td colspan="5" class="py-12 text-center text-slate-400">No announcements created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-4 px-6 max-w-sm">
                                    <p class="font-bold text-navy-900 line-clamp-1"><?= e($ann['title']) ?></p>
                                    <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5"><?= e($ann['summary']) ?></p>
                                </td>

                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-lwblue-ice text-lwblue">
                                        <?= e($ann['badge_type']) ?>
                                    </span>
                                </td>

                                <td class="py-4 px-4 font-bold text-[10px] uppercase">
                                    <?= $ann['is_published'] ? '<span class="text-emerald-600">Published</span>' : '<span class="text-slate-400">Draft</span>' ?>
                                </td>

                                <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                    <?= e(format_date($ann['published_at'])) ?>
                                </td>

                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    <form action="<?= url('admin/announcements.php') ?>" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
                                        <?= Security::csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$ann['id'] ?>">
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

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
