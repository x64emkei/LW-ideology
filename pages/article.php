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
    $canView = is_logged_in() && (user_has_any_role(['super_admin', 'admin', 'editor']) || (int)$article['author_id'] === (int)($_SESSION['user_id'] ?? 0));
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
        EDITORIAL PREVIEW MODE: This article is currently <strong><?= strtoupper(e($article['status'])) ?></strong> and is not visible to the public.
        <?php if (user_has_any_role(['super_admin', 'admin', 'editor'])): ?>
            <a href="<?= url('admin/article-edit.php?id=' . (int)$article['id']) ?>" class="ml-2 underline hover:text-black">Edit in CMS &rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Article Header Section -->
<header class="bg-navy-900 text-white pt-12 pb-16 relative overflow-hidden border-b border-navy-800">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 space-y-6">
        
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
            <p class="text-base sm:text-xl text-slate-300 font-normal leading-relaxed">
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
                    <p class="text-[11px] text-slate-400">LagunaWISE Ideology Division Fellow</p>
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

<!-- Article Main Content & Reader Layout -->
<article class="py-12 sm:py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        <!-- Cover Image -->
        <?php if (!empty($article['cover_image'])): ?>
            <div class="rounded-3xl overflow-hidden shadow-lg border border-slate-200 mb-12 aspect-[16/9] bg-slate-900">
                <img src="<?= asset($article['cover_image']) ?>" 
                     alt="<?= e($article['title']) ?>" 
                     class="w-full h-full object-cover">
            </div>
        <?php endif; ?>

        <!-- Reading Utility Bar (Share, Bookmark, Font adjustment) -->
        <div class="flex flex-wrap items-center justify-between gap-4 pb-8 mb-10 border-b border-slate-200 text-xs">
            
            <!-- Bookmark Button -->
            <button type="button" 
                    class="bookmark-toggle-btn inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-surface hover:bg-slate-100 text-slate-700 font-semibold transition"
                    data-article-id="<?= (int)$article['id'] ?>">
                <i class="<?= $isBookmarked ? 'fa-solid fa-bookmark text-amber-500' : 'fa-regular fa-bookmark' ?>"></i>
                <span class="bookmark-label"><?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?></span>
            </button>

            <!-- Social Share Buttons -->
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-medium mr-1">Share:</span>
                
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" 
                   target="_blank" rel="noopener noreferrer"
                   class="w-8 h-8 rounded-full bg-slate-100 hover:bg-blue-600 hover:text-white flex items-center justify-center text-slate-600 transition" 
                   title="Share on Facebook">
                    <i class="fa-brands fa-facebook-f text-xs"></i>
                </a>

                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($article['title']) ?>" 
                   target="_blank" rel="noopener noreferrer"
                   class="w-8 h-8 rounded-full bg-slate-100 hover:bg-black hover:text-white flex items-center justify-center text-slate-600 transition" 
                   title="Share on X">
                    <i class="fa-brands fa-x-twitter text-xs"></i>
                </a>

                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonicalUrl) ?>" 
                   target="_blank" rel="noopener noreferrer"
                   class="w-8 h-8 rounded-full bg-slate-100 hover:bg-blue-700 hover:text-white flex items-center justify-center text-slate-600 transition" 
                   title="Share on LinkedIn">
                    <i class="fa-brands fa-linkedin-in text-xs"></i>
                </a>

                <button type="button" 
                        onclick="navigator.clipboard.writeText(window.location.href); alert('Article URL copied to clipboard!');"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-lwblue hover:text-white flex items-center justify-center text-slate-600 transition" 
                        title="Copy Link">
                    <i class="fa-solid fa-link text-xs"></i>
                </button>
            </div>

        </div>

        <!-- Editorial Body Prose -->
        <div class="editorial-prose text-lg text-slate-800 leading-relaxed space-y-6 max-w-none">
            <?= Security::sanitizeArticleHtml($article['content']) ?>
        </div>

        <!-- Citation & Institutional Footer Note -->
        <div class="mt-14 p-6 bg-surface rounded-2xl border border-slate-200 text-xs text-slate-600 space-y-2">
            <h4 class="font-bold text-navy-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-feather text-lwblue"></i>
                Academic Citation &amp; Attribution
            </h4>
            <p>
                LagunaWISE Ideology Division. (<?= date('Y', strtotime($article['published_at'] ?? 'now')) ?>). <em><?= e($article['title']) ?></em>. LagunaWISE Digital Publication, Calamba City, Laguna. Retrieved from <?= e($canonicalUrl) ?>
            </p>
        </div>

        <!-- Author Biography Card -->
        <div class="mt-10 p-6 bg-navy-900 text-white rounded-2xl flex flex-col sm:flex-row items-center sm:items-start gap-5">
            <img src="<?= asset($article['author_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                 alt="<?= e($article['author_name']) ?>" 
                 class="w-16 h-16 rounded-full object-cover border-2 border-lwblue-light shadow-md shrink-0">
            <div class="space-y-2 text-center sm:text-left">
                <span class="text-[10px] font-bold uppercase tracking-widest text-lwblue-light">About the Author</span>
                <h3 class="text-base font-bold text-white"><?= e($article['author_name']) ?></h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    <?= e($article['author_bio'] ?? 'Research contributor and educational advocate at the LagunaWISE Ideology Division.') ?>
                </p>
            </div>
        </div>

    </div>
</article>

<!-- Comments Section -->
<section class="py-16 bg-surface border-t border-slate-200" id="comments">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-10">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <h3 class="text-xl font-bold text-navy-900 flex items-center gap-2">
                <i class="fa-regular fa-comments text-lwblue"></i>
                <span>Discussion &amp; Inquiry (<?= count($comments) ?>)</span>
            </h3>
            <span class="text-xs text-slate-400">Moderated academic and civic debate</span>
        </div>

        <!-- Existing Comments List -->
        <?php if (empty($comments)): ?>
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                <p>No comments posted yet. Be the first to offer a thoughtful inquiry or perspective.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($comments as $comment): ?>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <img src="<?= asset($comment['user_avatar'] ?? 'assets/images/default-avatar.svg') ?>" 
                                     alt="<?= e($comment['author_name']) ?>" 
                                     class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                <span class="font-bold text-navy-900"><?= e($comment['author_name']) ?></span>
                            </div>
                            <span class="text-slate-400"><?= e(format_date($comment['created_at'])) ?></span>
                        </div>
                        <p class="text-xs text-slate-700 leading-relaxed">
                            <?= nl2br(e($comment['content'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Post a Comment Form -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-5">
            <h4 class="text-base font-bold text-navy-900">Add to the Conversation</h4>
            <p class="text-xs text-slate-500">
                Please maintain respectful, evidence-based dialogue adhering to our 
                <a href="<?= url('pages/about.php#editorial-charter') ?>" class="text-lwblue underline">Editorial Charter</a>.
            </p>

            <form action="<?= url('actions/comment-action.php') ?>" method="POST" class="space-y-4">
                <?= Security::csrfField() ?>
                <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">
                <input type="hidden" name="slug" value="<?= e($article['slug']) ?>">

                <?php if (!is_logged_in()): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Your Full Name *</label>
                            <input type="text" name="author_name" required placeholder="e.g. Maria Clara" 
                                   class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Your Email Address *</label>
                            <input type="email" name="author_email" required placeholder="name@domain.com" 
                                   class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-lwblue font-medium flex items-center gap-2">
                        <i class="fa-solid fa-user-check"></i>
                        <span>Posting as <strong><?= e($currentUser['name']) ?></strong> (<?= e($currentUser['email']) ?>)</span>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Your Thoughtful Comment *</label>
                    <textarea name="content" rows="4" required placeholder="Share your perspective, constructive counter-argument, or inquiry..."
                              class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-400">Moderated to uphold respectful civil discourse.</span>
                    <button type="submit" class="px-6 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow">
                        Submit Comment
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

<!-- Related Articles Section -->
<?php if (!empty($relatedArticles)): ?>
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-xl font-bold text-navy-900 mb-8">Related Inquiries &amp; Perspectives</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($relatedArticles as $rel): ?>
                    <article class="bg-surface rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between group hover:shadow-md transition">
                        <div class="aspect-video bg-slate-900 overflow-hidden">
                            <img src="<?= asset($rel['cover_image'] ?? 'assets/images/article-medialiteracy.svg') ?>" 
                                 alt="<?= e($rel['title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                            <h4 class="text-sm font-bold text-navy-900 group-hover:text-lwblue transition line-clamp-2">
                                <a href="<?= url('pages/article.php?slug=' . urlencode($rel['slug'])) ?>">
                                    <?= e($rel['title']) ?>
                                </a>
                            </h4>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-200">
                                <span><?= e(format_date($rel['published_at'])) ?></span>
                                <a href="<?= url('pages/article.php?slug=' . urlencode($rel['slug'])) ?>" class="font-bold text-lwblue">Read &rarr;</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
