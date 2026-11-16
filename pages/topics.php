<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'Core Inquiry Topics — Research Pillars';
$pageDescription = 'Explore the core intellectual topics addressed by the LagunaWISE Ideology Division: from misinformation resilience to artificial intelligence.';

$db = Database::getConnection();

// Fetch all categories with article counts
$topics = $db->query("
    SELECT c.*, COUNT(ac.article_id) AS total_articles
    FROM categories c
    LEFT JOIN article_categories ac ON c.id = ac.category_id
    LEFT JOIN articles a ON ac.article_id = a.id AND a.status = 'published'
    GROUP BY c.id
    ORDER BY c.display_order ASC
")->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="bg-navy-900 text-white py-16 sm:py-20 border-b border-navy-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-4">
        <span class="text-xs font-bold tracking-widest text-lwblue-ice uppercase">Thematic Directory</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
            Core Inquiry Topics
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto font-normal">
            Organized subject domains where we conduct critical analysis, investigate digital realities, and formulate educational toolkits.
        </p>
    </div>
</section>

<section class="py-16 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($topics as $t): ?>
                <div class="bg-white rounded-lg p-6 sm:p-7 border border-slate-200 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="w-11 h-11 rounded-md bg-lwblue-ice text-lwblue flex items-center justify-center text-base font-bold group-hover:bg-lwblue group-hover:text-white transition">
                                <i class="fa-solid fa-<?= e($t['icon'] ?? 'bookmark') ?>"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-surface px-3 py-1 rounded-full border border-slate-200">
                                <?= (int)$t['total_articles'] ?> Published
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-navy-900 group-hover:text-lwblue transition">
                            <a href="<?= url('pages/topic.php?slug=' . urlencode($t['slug'])) ?>">
                                <?= e($t['name']) ?>
                            </a>
                        </h2>

                        <p class="text-xs text-editorial-muted leading-relaxed">
                            <?= e($t['description']) ?>
                        </p>
                    </div>

                    <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="<?= url('pages/topic.php?slug=' . urlencode($t['slug'])) ?>" class="text-xs font-bold text-lwblue group-hover:text-navy-900 flex items-center gap-1.5">
                            <span>Explore Topic Monograph</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
