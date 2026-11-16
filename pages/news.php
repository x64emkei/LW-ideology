<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'News & Updates — LagunaWISE Ideology Division';
$pageDescription = 'Official announcements, educational campaigns, civic workshops, and institutional releases from the LagunaWISE Ideology Division in Calamba City, Laguna.';

$db = Database::getConnection();

$badge = trim($_GET['badge'] ?? '');
$params = [];
$where = "is_published = 1";

if (!empty($badge)) {
    $where .= " AND badge_type = ?";
    $params[] = $badge;
}

$stmt = $db->prepare("SELECT * FROM announcements WHERE {$where} ORDER BY published_at DESC");
$stmt->execute($params);
$newsItems = $stmt->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="bg-navy-900 text-white py-16 sm:py-20 border-b border-navy-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-4">
        <span class="text-xs font-bold tracking-widest text-lwblue-ice uppercase">Division Dispatches</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
            News, Campaigns &amp; Activities
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto font-normal">
            Stay informed on our community workshops in Laguna, calls for research papers, and division milestones.
        </p>
    </div>
</section>

<!-- Filter badges -->
<section class="bg-white border-b border-slate-200 py-4">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 flex flex-wrap items-center gap-2 text-xs">
        <span class="text-slate-400 font-semibold mr-2">Filter Category:</span>
        <a href="<?= url('pages/news.php') ?>" class="px-3 py-1.5 rounded-lg <?= empty($badge) ? 'bg-navy-900 text-white font-bold' : 'bg-surface hover:bg-slate-200 text-slate-700' ?>">All Updates</a>
        <a href="<?= url('pages/news.php?badge=announcement') ?>" class="px-3 py-1.5 rounded-lg <?= $badge === 'announcement' ? 'bg-navy-900 text-white font-bold' : 'bg-surface hover:bg-slate-200 text-slate-700' ?>">Announcements</a>
        <a href="<?= url('pages/news.php?badge=campaign') ?>" class="px-3 py-1.5 rounded-lg <?= $badge === 'campaign' ? 'bg-navy-900 text-white font-bold' : 'bg-surface hover:bg-slate-200 text-slate-700' ?>">Campaigns</a>
        <a href="<?= url('pages/news.php?badge=initiative') ?>" class="px-3 py-1.5 rounded-lg <?= $badge === 'initiative' ? 'bg-navy-900 text-white font-bold' : 'bg-surface hover:bg-slate-200 text-slate-700' ?>">Initiatives</a>
        <a href="<?= url('pages/news.php?badge=publication') ?>" class="px-3 py-1.5 rounded-lg <?= $badge === 'publication' ? 'bg-navy-900 text-white font-bold' : 'bg-surface hover:bg-slate-200 text-slate-700' ?>">Publications</a>
    </div>
</section>

<section class="py-14 bg-surface">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-8">
        <?php if (empty($newsItems)): ?>
            <div class="bg-white p-10 rounded-lg border border-slate-200 text-center space-y-2">
                <i class="fa-regular fa-bell text-2xl text-slate-400 mb-1"></i>
                <p class="text-sm font-bold text-navy-900">No announcements are currently available.</p>
                <p class="text-xs text-slate-500">Check back soon for new dispatches, community workshops, and research releases.</p>
            </div>
        <?php else: ?>
            <?php foreach ($newsItems as $item): ?>
                <article id="news-<?= (int)$item['id'] ?>" class="bg-white rounded-lg p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4 hover:border-lwblue transition">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-lwblue-ice text-lwblue">
                            <?= e($item['badge_type']) ?>
                        </span>
                        <span class="text-slate-400 font-medium">
                            <i class="fa-regular fa-calendar-check mr-1 text-lwblue"></i>
                            <?= e(format_date($item['published_at'])) ?>
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-navy-900 leading-snug">
                        <?= e($item['title']) ?>
                    </h2>

                    <p class="text-sm font-medium text-slate-700 leading-relaxed bg-surface p-4 rounded-xl border border-slate-100">
                        <?= e($item['summary']) ?>
                    </p>

                    <div class="editorial-prose text-sm text-slate-600 leading-relaxed space-y-3 pt-2">
                        <?= nl2br(e($item['content'])) ?>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>Issued by: LagunaWISE Communications &amp; Ideology Division</span>
                        <a href="mailto:<?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>" class="text-lwblue font-bold hover:underline">Inquire About This &rarr;</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
