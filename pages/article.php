<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . url('pages/articles.php'));
    exit;
}

$db = Database::getConnection();

// Fetch article details
$stmt = $db->prepare("
    SELECT a.*, u.name AS author_name, u.bio AS author_bio, u.avatar AS author_avatar
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.slug = ?
    LIMIT 1
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Article Not Found';
    require_once dirname(__DIR__) . '/includes/header.php';
    echo '<div class="max-w-xl mx-auto py-24 px-4 text-center space-y-4">
            <h1 class="text-3xl font-extrabold text-navy-900">Article Not Found</h1>
            <p class="text-xs text-editorial-muted">The monograph you are seeking may have been relocated or archived.</p>
            <a href="' . url('pages/articles.php') . '" class="inline-block px-5 py-2.5 bg-lwblue text-white rounded-xl text-xs font-bold">Return to Directory</a>
          </div>';
    require_once dirname(__DIR__) . '/includes/footer.php';
    exit;
}

// Access Control: If article is not published, only authorized editors, admins, or the author can view it
if ($article['status'] !== 'published') {
    $canView = is_logged_in() && (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor']) || (int)$article['author_id'] === (int)($_SESSION['user_id'] ?? 0));
    if (!$canView) {
        http_response_code(403);
        $pageTitle = 'Private Draft';
        require_once dirname(__DIR__) . '/includes/header.php';
        echo '<div class="max-w-xl mx-auto py-24 px-4 text-center space-y-4">
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold uppercase">Restricted Content</span>
                <h1 class="text-2xl font-extrabold text-navy-900">Manuscript Under Review</h1>
                <p class="text-xs text-editorial-muted">This article has not been published yet. Only authorized staff and the contributing author may inspect draft manuscripts.</p>
                <a href="' . url('pages/articles.php') . '" class="inline-block px-5 py-2.5 bg-lwblue text-white rounded-xl text-xs font-bold">Browse Published Articles</a>
              </div>';
        require_once dirname(__DIR__) . '/includes/footer.php';
        exit;
    }
}

// Increment view count for published articles (rate limited via session)
if ($article['status'] === 'published') {
    $viewKey = 'viewed_article_' . $article['id'];
    if (empty($_SESSION[$viewKey])) {
        $db->prepare("UPDATE articles SET views_count = views_count + 1 WHERE id = ?")->execute([$article['id']]);
        $_SESSION[$viewKey] = true;
        $article['views_count']++;
    }
}

// Fetch categories for this article
$catStmt = $db->prepare("
    SELECT c.*
    FROM categories c
    JOIN article_categories ac ON c.id = ac.category_id
    WHERE ac.article_id = ?
");
$catStmt->execute([$article['id']]);
$categories = $catStmt->fetchAll();

// Check if currently bookmarked by logged in user
$isBookmarked = false;
if (is_logged_in()) {
    $bmStmt = $db->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND article_id = ?");
    $bmStmt->execute([$_SESSION['user_id'], $article['id']]);
    $isBookmarked = (bool)$bmStmt->fetchColumn();
}

// Fetch approved comments
$commentStmt = $db->prepare("
    SELECT c.*, u.avatar AS user_avatar
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.article_id = ? AND c.status = 'approved'
    ORDER BY c.created_at ASC
");
$commentStmt->execute([$article['id']]);
$comments = $commentStmt->fetchAll();

// Fetch Related Articles
$firstCatId = $categories[0]['id'] ?? 1;
$relStmt = $db->prepare("
    SELECT a.*, u.name AS author_name, u.avatar AS author_avatar
    FROM articles a
    JOIN users u ON a.author_id = u.id
    JOIN article_categories ac ON a.id = ac.article_id
    WHERE ac.category_id = ? AND a.id != ? AND a.status = 'published'
    ORDER BY a.published_at DESC
    LIMIT 3
");
$relStmt->execute([$firstCatId, $article['id']]);
$relatedArticles = $relStmt->fetchAll();

// Page metadata for social & SEO
$pageTitle = $article['title'];
$pageDescription = $article['excerpt'];
$ogImage = asset($article['cover_image'] ?? 'assets/images/lagunawise-map-logo.png');
$canonicalUrl = url('pages/article.php?slug=' . urlencode($article['slug']));

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- JSON-LD Structured Data for Article SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": <?= json_encode($article['title']) ?>,
  "description": <?= json_encode($article['excerpt']) ?>,
  "image": <?= json_encode($ogImage) ?>,
  "datePublished": <?= json_encode($article['published_at'] ?? $article['created_at']) ?>,
  "dateModified": <?= json_encode($article['updated_at'] ?? $article['created_at']) ?>,
  "author": {
    "@type": "Person",
    "name": <?= json_encode($article['author_name']) ?>
  },
  "publisher": {
    "@type": "Organization",
    "name": "LagunaWISE Ideology Division",
    "logo": {
      "@type": "ImageObject",
      "url": <?= json_encode(asset('assets/images/lagunawise-map-logo.png')) ?>
    }
  }
}
</script>

<!-- Draft Status Alert Banner for Staff -->
<?php if ($article['status'] !== 'published'): ?>
    <div class="bg-amber-500 text-slate-900 font-bold px-4 py-2.5 text-xs text-center border-b border-amber-600">
        <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>
        EDITORIAL PREVIEW MODE: This article is currently <strong><?= strtoupper(e(str_replace('_', ' ', $article['status']))) ?></strong> and is not visible to the public.
        <?php if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor'])): ?>
            <a href="<?= url('admin/article-edit.php?id=' . (int)$article['id']) ?>" class="ml-2 underline hover:text-black">Edit in CMS &rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Article Header Section -->
<header class="bg-navy-900 text-white pt-12 pb-16 relative overflow-hidden border-b border-navy-800">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 space-y-6">
        
        <!-- Breadcrumb & Categories -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="<?= url() ?>" class="text-slate-400 hover:text-white transition">Home</a>
            <span class="text-slate-600">/</span>
            <a href="<?= url('pages/articles.php') ?>" class="text-slate-400 hover:text-white transition">Articles</a>
            <span class="text-slate-600">/</span>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= url('pages/articles.php?category=' . urlencode($cat['slug'])) ?>" 
                   class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-lwblue text-white hover:bg-lwblue-hover transition">
                    <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Title -->
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight font-heading">
            <?= e($article['title']) ?>
        </h1>

        <!-- Subtitle / Excerpt -->
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
                    <p class="text-[11px] text-slate-400">LagunaWISE Ideology Division Contributor</p>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar text-lwblue-light"></i>
                    <span><?= e(format_date($article['published_at'] ?? $article['created_at'])) ?></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-clock text-lwblue-light"></i>
                    <span><?= (int)$article['reading_time'] ?> min read</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-eye text-lwblue-light"></i>
                    <span><?= number_format((int)$article['views_count']) ?> views</span>
                </span>
            </div>
        </div>

    </div>
</header>

<!-- Article Responsive Two-Column Layout (Main Reading Column 66% / Sidebar Column 34%) -->
<article class="py-12 sm:py-16 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Main Content Area (Desktop ~66% / 8 cols) -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-10 rounded-lg border border-slate-200 shadow-xs space-y-8">
                
                <!-- Cover Image -->
                <?php if (!empty($article['cover_image'])): ?>
                    <div class="rounded-md overflow-hidden shadow-sm border border-slate-200 aspect-[16/9] bg-slate-900">
                        <img src="<?= asset($article['cover_image']) ?>" 
                             alt="<?= e($article['title']) ?>" 
                             class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <!-- Excerpt Lead Box -->
                <div class="p-5 bg-blue-50/70 border-l-4 border-lwblue rounded-r-xl text-sm font-medium text-slate-800 leading-relaxed">
                    <?= e($article['excerpt']) ?>
                </div>

                <!-- Editorial Body Prose (Comfortable Reading Width) -->
                <div class="editorial-prose text-base sm:text-lg text-slate-800 leading-relaxed max-w-prose">
                    <?= render_article_content($article['content']) ?>
                </div>

                <!-- References & Sources Section -->
                <?php if (!empty($article['references_sources'])): ?>
                    <div class="mt-12 pt-6 border-t border-slate-200 space-y-3">
                        <h3 class="text-sm font-bold text-navy-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-book-bookmark text-lwblue"></i>
                            <span>References &amp; Cited Academic Sources</span>
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

                <!-- Comments & Discussion Area inside Main Column -->
                <section class="mt-14 pt-8 border-t border-slate-200 space-y-8" id="comments">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-navy-900 flex items-center gap-2">
                            <i class="fa-regular fa-comments text-lwblue"></i>
                            <span>Discussion &amp; Inquiry (<?= count($comments) ?>)</span>
                        </h3>
                        <span class="text-xs text-slate-400">Moderated civic discourse</span>
                    </div>

                    <!-- Existing Comments -->
                    <?php if (empty($comments)): ?>
                        <div class="p-6 rounded-md bg-surface border border-slate-200 text-center text-xs text-slate-500">
                            <p>No comments posted yet. Be the first to offer an informed inquiry or constructive perspective.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($comments as $comment): ?>
                                <div class="bg-surface p-4 rounded-md border border-slate-200 text-xs space-y-2">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <div class="flex items-center gap-2 font-bold text-navy-900">
                                            <img src="<?= asset($comment['user_avatar'] ?? 'assets/images/default-avatar.svg') ?>" alt="User" class="w-6 h-6 rounded-full object-cover">
                                            <span><?= e($comment['author_name']) ?></span>
                                        </div>
                                        <span class="text-slate-400"><?= e(format_date($comment['created_at'])) ?></span>
                                    </div>
                                    <p class="text-slate-700 leading-relaxed pl-8">
                                        <?= nl2br(e($comment['content'])) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Comment Form -->
                    <div class="p-6 rounded-md bg-surface border border-slate-200 space-y-4">
                        <h4 class="text-sm font-bold text-navy-900">Participate in the Discussion</h4>
                        
                        <form action="<?= url('actions/comment-action.php') ?>" method="POST" class="space-y-4">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                            <input type="hidden" name="slug" value="<?= e($article['slug']) ?>">

                            <?php if (!is_logged_in()): ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Your Full Name *</label>
                                        <input type="text" name="author_name" required placeholder="e.g. Maria Clara" 
                                               class="w-full text-xs p-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Your Email Address *</label>
                                        <input type="email" name="author_email" required placeholder="name@domain.com" 
                                               class="w-full text-xs p-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="p-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 flex items-center gap-2">
                                    <i class="fa-solid fa-user-check text-lwblue"></i>
                                    <span>Commenting as <strong><?= e($currentUser['name']) ?></strong></span>
                                </div>
                            <?php endif; ?>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Your Perspective *</label>
                                <textarea name="content" rows="4" required placeholder="Share your insights or constructive argument adhering to our Editorial Charter..."
                                          class="w-full text-xs p-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue leading-relaxed"></textarea>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <span class="text-[11px] text-slate-400">Respectful, civil debates only.</span>
                                <button type="submit" class="px-5 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow">
                                    Submit Comment
                                </button>
                            </div>
                        </form>
                    </div>

                </section>

            </div>

            <!-- Secondary Sidebar Area (Desktop ~34% / 4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Reader Actions & Utility Card -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Reader Actions</span>
                        <i class="fa-solid fa-bookmark text-lwblue"></i>
                    </h3>

                    <div class="space-y-3">
                        <!-- Interactive Bookmark Button -->
                        <button type="button" 
                                class="bookmark-toggle-btn w-full py-2.5 px-4 rounded-md border border-slate-200 bg-surface hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center justify-center gap-2"
                                data-article-id="<?= (int)$article['id'] ?>">
                            <i class="<?= $isBookmarked ? 'fa-solid fa-bookmark text-amber-500' : 'fa-regular fa-bookmark' ?>"></i>
                            <span class="bookmark-label"><?= $isBookmarked ? 'Bookmarked in Reading List' : 'Bookmark Article' ?></span>
                        </button>

                        <!-- Social Share -->
                        <div class="pt-3 border-t border-slate-100">
                            <span class="block text-xs font-semibold text-slate-600 mb-2">Share this Insight:</span>
                            <div class="flex items-center gap-2">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="flex-1 py-1.5 rounded-md bg-surface hover:bg-blue-600 hover:text-white border border-slate-200 text-slate-700 text-center transition text-xs font-bold" 
                                   title="Facebook">
                                    <i class="fa-brands fa-facebook-f mr-1"></i> FB
                                </a>

                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($article['title']) ?>" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="flex-1 py-1.5 rounded-md bg-surface hover:bg-black hover:text-white border border-slate-200 text-slate-700 text-center transition text-xs font-bold" 
                                   title="X / Twitter">
                                    <i class="fa-brands fa-x-twitter mr-1"></i> X
                                </a>

                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonicalUrl) ?>" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="flex-1 py-1.5 rounded-md bg-surface hover:bg-blue-700 hover:text-white border border-slate-200 text-slate-700 text-center transition text-xs font-bold" 
                                   title="LinkedIn">
                                    <i class="fa-brands fa-linkedin-in mr-1"></i> In
                                </a>

                                <button type="button" 
                                        onclick="navigator.clipboard.writeText(window.location.href); alert('Article URL copied to clipboard!');"
                                        class="p-2 rounded-md bg-surface hover:bg-lwblue hover:text-white border border-slate-200 text-slate-700 transition" 
                                        title="Copy Link">
                                    <i class="fa-solid fa-link text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Author Profile Card -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm space-y-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-lwblue">Author Attribution</span>
                    
                    <div class="flex items-center gap-3.5">
                        <img src="<?= asset($article['author_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                             alt="<?= e($article['author_name']) ?>" 
                             class="w-14 h-14 rounded-full object-cover border-2 border-lwblue-light shadow-sm shrink-0">
                        <div>
                            <h4 class="text-sm font-bold text-navy-900"><?= e($article['author_name']) ?></h4>
                            <p class="text-[11px] text-slate-500">Contributing Fellow</p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-lwblue-ice text-lwblue">Verified Author</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        <?= e($article['author_bio'] ?? 'Research contributor and educational advocate at the LagunaWISE Ideology Division.') ?>
                    </p>
                </div>

                <!-- Academic Citation & Attribution Box -->
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm space-y-2 text-xs text-slate-600">
                    <h4 class="font-bold text-navy-900 flex items-center gap-2">
                        <i class="fa-solid fa-feather text-lwblue"></i>
                        <span>Citation &amp; Attribution</span>
                    </h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed bg-surface p-3 rounded-md border border-slate-100 font-mono">
                        LagunaWISE Ideology Division. (<?= date('Y', strtotime($article['published_at'] ?? 'now')) ?>). <em><?= e($article['title']) ?></em>. Retrieved from <?= e($canonicalUrl) ?>
                    </p>
                </div>

                <!-- Related Published Articles -->
                <?php if (!empty($relatedArticles)): ?>
                    <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm space-y-4">
                        <h4 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                            Related Insights
                        </h4>
                        <div class="space-y-3">
                            <?php foreach ($relatedArticles as $rel): ?>
                                <div class="space-y-1">
                                    <a href="<?= url('pages/article.php?slug=' . urlencode($rel['slug'])) ?>" class="text-xs font-bold text-navy-900 hover:text-lwblue line-clamp-2 leading-snug">
                                        <?= e($rel['title']) ?>
                                    </a>
                                    <p class="text-[10px] text-slate-400">By <?= e($rel['author_name']) ?> • <?= (int)$rel['reading_time'] ?> min</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
</article>

<!-- Top Reading Progress Indicator -->
<div id="readingProgressContainer" class="fixed top-0 left-0 right-0 h-1 z-50 bg-transparent pointer-events-none">
    <div id="readingProgressBar" class="h-full bg-gradient-to-r from-lwblue via-sky-400 to-lwblue-light w-0 transition-all duration-75"></div>
</div>

<!-- Unique Floating Mobile Reader Bar (Mobile Only) -->
<aside id="mobileReaderBar" 
       class="fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 lg:hidden bg-navy-950/95 backdrop-blur-md text-white border border-slate-700/80 rounded-full shadow-2xl px-4 py-2.5 flex items-center justify-between gap-3 text-xs"
       aria-label="Mobile Reader Controls">
    
    <!-- Left: Reading Progress & Remaining Time -->
    <div class="flex items-center gap-2 border-r border-slate-700/80 pr-3 shrink-0">
        <span id="mobileProgressPercent" class="font-mono text-[11px] font-bold text-sky-400">0%</span>
        <span class="text-[10px] text-slate-400 hidden xs:inline">• <?= (int)$article['reading_time'] ?>m read</span>
    </div>

    <!-- Center/Right: Core Reader Actions -->
    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Font Size Toggle (Comfortable 17px vs Large 20px) -->
        <button type="button" 
                id="toggleFontSizeBtn" 
                class="px-2 py-1 rounded-full bg-white/10 hover:bg-white/20 text-slate-200 text-xs font-bold transition flex items-center gap-0.5"
                title="Adjust Text Size">
            <span class="text-[9px]">A</span><span class="text-xs font-black">A+</span>
        </button>

        <!-- Quick Bookmark -->
        <button type="button" 
                class="bookmark-toggle-btn px-2.5 py-1.5 rounded-full bg-white/10 hover:bg-white/20 transition flex items-center gap-1.5 text-xs font-semibold <?= $isBookmarked ? 'text-amber-400' : 'text-slate-300' ?>"
                data-article-id="<?= (int)$article['id'] ?>"
                title="Bookmark article">
            <i class="<?= $isBookmarked ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark' ?> text-xs"></i>
            <span class="hidden sm:inline"><?= $isBookmarked ? 'Saved' : 'Save' ?></span>
        </button>

        <!-- Comments Jump -->
        <a href="#comments" 
           class="px-2.5 py-1.5 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center gap-1.5 text-xs font-semibold"
           title="Jump to Discussion">
            <i class="fa-regular fa-comments text-xs"></i>
            <span class="px-1.5 py-0.2 rounded-full bg-lwblue text-[10px] font-bold"><?= count($comments) ?></span>
        </a>

        <!-- Native Share Sheet -->
        <button type="button" 
                id="mobileShareBtn" 
                class="w-8 h-8 rounded-full bg-lwblue hover:bg-lwblue-hover text-white flex items-center justify-center transition shadow shrink-0"
                title="Share Article">
            <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i>
        </button>
    </div>
</aside>

<!-- Floating Toast Notification -->
<div id="editorialToast" class="fixed top-16 left-1/2 -translate-x-1/2 z-50 hidden bg-navy-900 text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-xl border border-navy-700 items-center gap-2 transition-all duration-300">
    <i class="fa-solid fa-check text-emerald-400 text-xs" id="toastIcon"></i>
    <span id="toastMsg">Action completed</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const articleId = <?= (int)$article['id'] ?>;
    const csrfToken = <?= json_encode(csrf_token()) ?>;
    const bookmarkUrl = <?= json_encode(url('actions/bookmark-action.php')) ?>;
    const prose = document.querySelector('.editorial-prose');

    // 1. Reading Progress & Floating Reader Bar Scroll Handler
    const pBar = document.getElementById('readingProgressBar');
    const pText = document.getElementById('mobileProgressPercent');
    const mobileBar = document.getElementById('mobileReaderBar');
    let lastScrollY = window.scrollY;

    window.addEventListener('scroll', function() {
        const currentY = window.scrollY;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const percent = maxScroll > 0 ? Math.min(100, Math.round((currentY / maxScroll) * 100)) : 0;

        if (pBar) pBar.style.width = percent + '%';
        if (pText) pText.textContent = percent + '%';

        if (mobileBar) {
            if (currentY > 180 && currentY > lastScrollY) {
                mobileBar.classList.add('hide-bar');
            } else {
                mobileBar.classList.remove('hide-bar');
            }
        }
        lastScrollY = currentY;
    }, { passive: true });

    // 2. Mobile Font Size Toggle (Persisted in localStorage)
    const fontBtn = document.getElementById('toggleFontSizeBtn');
    if (fontBtn && prose) {
        if (localStorage.getItem('editorial_large_font') === 'true') {
            prose.classList.add('text-size-lg');
            fontBtn.classList.add('bg-lwblue', 'text-white');
        }

        fontBtn.addEventListener('click', function() {
            prose.classList.toggle('text-size-lg');
            const isLarge = prose.classList.contains('text-size-lg');
            localStorage.setItem('editorial_large_font', isLarge ? 'true' : 'false');
            fontBtn.classList.toggle('bg-lwblue', isLarge);
            fontBtn.classList.toggle('text-white', isLarge);
            showToast(isLarge ? 'Reading Mode: Large font' : 'Reading Mode: Standard font', 'fa-font');
        });
    }

    // 3. Native Share / Clipboard Fallback
    const shareBtn = document.getElementById('mobileShareBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: <?= json_encode($article['title']) ?>,
                    text: <?= json_encode($article['excerpt']) ?>,
                    url: window.location.href
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href).then(function() {
                    showToast('Article link copied to clipboard!', 'fa-link');
                });
            }
        });
    }

    // 4. Interactive AJAX Bookmark Toggle (Syncs both desktop and mobile buttons)
    document.querySelectorAll('.bookmark-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const originalHtml = btn.innerHTML;
            btn.disabled = true;

            const formData = new FormData();
            formData.append('article_id', articleId);
            formData.append('csrf_token', csrfToken);

            fetch(bookmarkUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.status === 'unauthorized') {
                    showToast(data.message || 'Please log in to bookmark articles', 'fa-circle-exclamation');
                    setTimeout(() => {
                        window.location.href = <?= json_encode(url('auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ''))) ?>;
                    }, 1200);
                    return;
                }

                const isAdded = data.action === 'added';
                // Update all bookmark buttons on the page
                document.querySelectorAll('.bookmark-toggle-btn').forEach(b => {
                    const icon = b.querySelector('i');
                    const label = b.querySelector('.bookmark-label');
                    if (isAdded) {
                        if (icon) icon.className = 'fa-solid fa-bookmark text-amber-400';
                        if (label) label.textContent = 'Bookmarked in Reading List';
                        b.classList.add('text-amber-400');
                    } else {
                        if (icon) icon.className = 'fa-regular fa-bookmark';
                        if (label) label.textContent = 'Bookmark Article';
                        b.classList.remove('text-amber-400');
                    }
                });

                showToast(data.message || (isAdded ? 'Saved to Reading List' : 'Removed from Bookmarks'), isAdded ? 'fa-bookmark' : 'fa-check');
            })
            .catch(err => {
                btn.disabled = false;
                showToast('Network error updating bookmark', 'fa-circle-exclamation');
            });
        });
    });

    // 5. Toast Notification Helper
    function showToast(msg, iconClass) {
        const toast = document.getElementById('editorialToast');
        const icon = document.getElementById('toastIcon');
        const text = document.getElementById('toastMsg');
        if (!toast) return;

        text.textContent = msg;
        if (icon && iconClass) {
            icon.className = 'fa-solid ' + iconClass + ' text-emerald-400 text-xs';
        }

        toast.classList.remove('hidden', 'opacity-0');
        toast.classList.add('flex', 'opacity-100');

        clearTimeout(window._toastTimeout);
        window._toastTimeout = setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 2600);
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
