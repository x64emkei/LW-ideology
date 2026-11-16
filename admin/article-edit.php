<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['administrator', 'super_admin', 'admin', 'editor', 'contributor', 'writer']);

$db = Database::getConnection();
$user = current_user();
$articleId = (int)($_GET['id'] ?? 0);

if ($articleId <= 0) {
    header('Location: ' . url('admin/articles.php'));
    exit;
}

// Fetch article
$stmt = $db->prepare("
    SELECT a.*, u.name AS author_name, u.email AS author_email
    FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.id = ?
    LIMIT 1
");
$stmt->execute([$articleId]);
$article = $stmt->fetch();

if (!$article) {
    flash('error', 'Article not found.');
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

// RBAC: Contributors can only edit their own draft or revision_requested articles
$isStaff = user_has_any_role(['administrator', 'super_admin', 'admin', 'editor']);
$isAuthor = ((int)$article['author_id'] === (int)$user['id']);

if (!$isStaff && !$isAuthor) {
    flash('error', 'Unauthorized: You are only permitted to edit your own authored manuscripts.');
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

if (!$isStaff && !in_array($article['status'], ['draft', 'revision_requested'], true)) {
    flash('warning', 'Notice: This manuscript is currently in the editorial review pipeline and cannot be altered until reviewed.');
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

// Fetch categories
$allCategories = $db->query("SELECT * FROM categories ORDER BY display_order ASC, name ASC")->fetchAll();

// Fetch assigned category IDs
$assignedCatStmt = $db->prepare("SELECT category_id FROM article_categories WHERE article_id = ?");
$assignedCatStmt->execute([$articleId]);
$assignedCatIds = $assignedCatStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch previous editorial reviews
$reviewStmt = $db->prepare("
    SELECT er.*, u.name AS reviewer_name
    FROM editorial_reviews er
    JOIN users u ON er.reviewer_id = u.id
    WHERE er.article_id = ?
    ORDER BY er.created_at DESC
");
$reviewStmt->execute([$articleId]);
$reviewHistory = $reviewStmt->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $submitAction = $_POST['submit_action'] ?? 'update';

    // Handle Delete Draft Action
    if ($submitAction === 'delete_draft') {
        if ($article['status'] === 'draft' || $isStaff) {
            $del = $db->prepare("DELETE FROM articles WHERE id = ?");
            $del->execute([$articleId]);
            log_activity((int)$user['id'], 'article_deleted', 'article', $articleId, "Deleted draft #{$articleId}");
            flash('success', "Draft '{$article['title']}' was deleted.");
            header('Location: ' . ($isStaff ? url('admin/articles.php') : url('pages/dashboard.php')));
            exit;
        } else {
            flash('error', 'Only drafts may be deleted.');
        }
    }

    $title = Security::sanitize($_POST['title'] ?? '');
    $subtitle = Security::sanitize($_POST['subtitle'] ?? '');
    $excerpt = Security::sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $referencesSources = Security::sanitize($_POST['references_sources'] ?? '');
    $tags = Security::sanitize($_POST['tags'] ?? '');
    $readingTime = max(1, (int)($_POST['reading_time'] ?? 5));
    $coverImage = trim($_POST['cover_image'] ?? $article['cover_image']);
    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
    $feedback = Security::sanitize($_POST['feedback'] ?? '');

    // Determine status based on action & role
    if ($submitAction === 'submit_review') {
        $newStatus = 'pending_review';
    } elseif ($submitAction === 'save_draft') {
        $newStatus = 'draft';
    } elseif ($isStaff) {
        $newStatus = $_POST['status'] ?? $article['status'];
    } else {
        $newStatus = $article['status'];
    }

    // Role check: Contributors cannot directly publish or approve
    if (!$isStaff && in_array($newStatus, ['published', 'approved'], true)) {
        $newStatus = 'pending_review';
    }

    // Handle file upload if provided
    if (!empty($_FILES['cover_upload']['name']) && $_FILES['cover_upload']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_upload'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        $mime = mime_content_type($file['tmp_name']);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];

        if (in_array($ext, $allowedExts, true) && in_array($mime, $allowedMimes, true) && $file['size'] <= MAX_UPLOAD_SIZE) {
            $newFilename = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = UPLOAD_DIR . '/' . $newFilename;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $coverImage = 'assets/uploads/' . $newFilename;
                $db->prepare("INSERT INTO media (uploader_id, filename, original_name, file_path, mime_type, file_size, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                   ->execute([$user['id'], $newFilename, $file['name'], $coverImage, $mime, $file['size']]);
            }
        }
    }

    if (empty($title) || empty($excerpt) || empty($content)) {
        $error = 'Please fill in Title, Summary/Excerpt, and Article Content.';
    } else {
        $sanitizedContent = Security::sanitizeArticleHtml($content);

        try {
            $db->beginTransaction();

            $publishedAt = $article['published_at'];
            if ($newStatus === 'published' && empty($publishedAt)) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            $stmt = $db->prepare("
                UPDATE articles
                SET title = ?, subtitle = ?, excerpt = ?, content = ?, references_sources = ?, tags = ?,
                    cover_image = ?, reading_time = ?, status = ?, published_at = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $title,
                $subtitle,
                $excerpt,
                $sanitizedContent,
                $referencesSources,
                $tags,
                $coverImage,
                $readingTime,
                $newStatus,
                $publishedAt,
                $articleId
            ]);

            // Update categories
            $db->prepare("DELETE FROM article_categories WHERE article_id = ?")->execute([$articleId]);
            if (!empty($categoryIds)) {
                $linkStmt = $db->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
                foreach ($categoryIds as $cId) {
                    $linkStmt->execute([$articleId, $cId]);
                }
            }

            // Record editorial review actions
            if ($newStatus !== $article['status'] || !empty($feedback)) {
                $reviewAction = match($newStatus) {
                    'published' => 'published',
                    'approved' => 'approved',
                    'revision_requested' => 'revision_requested',
                    'pending_review' => 'submitted',
                    default => 'submitted'
                };

                $reviewNote = !empty($feedback) ? $feedback : "Status updated from {$article['status']} to {$newStatus}";
                $db->prepare("
                    INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ")->execute([$articleId, $user['id'], $reviewAction, $reviewNote]);
            }

            $db->commit();

            log_activity((int)$user['id'], 'article_updated', 'article', $articleId, "Article #{$articleId} updated (Status: {$newStatus})");
            flash('success', "Article '{$title}' updated successfully.");

            if ($isStaff) {
                header('Location: ' . url('admin/articles.php'));
            } else {
                header('Location: ' . url('pages/dashboard.php'));
            }
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Article update error: " . $e->getMessage());
            $error = 'Database error during article update: ' . $e->getMessage();
        }
    }
}

$adminPageTitle = 'Edit Manuscript — #' . $articleId;
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header Breadcrumb & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Manuscript Editor</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                <?= match($article['status']) {
                    'published' => 'bg-emerald-100 text-emerald-800',
                    'pending_review' => 'bg-amber-100 text-amber-800',
                    'revision_requested' => 'bg-rose-100 text-rose-800',
                    default => 'bg-slate-200 text-slate-700'
                } ?>">
                <?= e(str_replace('_', ' ', $article['status'])) ?>
            </span>
        </div>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight mt-0.5">Edit Article</h1>
        <p class="text-xs text-slate-500">Authored by <strong><?= e($article['author_name']) ?></strong> • Created <?= e(format_date($article['created_at'])) ?></p>
    </div>

    <div class="flex items-center gap-2">
        <a href="<?= url('pages/article-preview.php?id=' . (int)$article['id']) ?>" 
           class="px-3.5 py-2 bg-surface hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-eye text-xs"></i>
            <span>Preview</span>
        </a>

        <?php if ($article['status'] === 'published'): ?>
            <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>" 
               target="_blank"
               class="px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                <span>Live URL</span>
            </a>
        <?php endif; ?>

        <a href="<?= $isStaff ? url('admin/articles.php') : url('pages/dashboard.php') ?>" 
           class="text-xs font-bold text-slate-600 hover:text-navy-900 transition flex items-center gap-1">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Back</span>
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation shrink-0"></i>
        <span><?= e($error) ?></span>
    </div>
<?php endif; ?>

<!-- Responsive Two-Column Layout (Main Editor ~66% / Settings Sidebar ~34%) -->
<form action="<?= url('admin/article-edit.php?id=' . (int)$article['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
    <?= Security::csrfField() ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Main Content Area (Desktop ~66% / 8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-6">
            
            <!-- Article Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Article Title *</label>
                <input type="text" name="title" required 
                       value="<?= e($_POST['title'] ?? $article['title']) ?>"
                       class="w-full text-base sm:text-lg font-bold px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <!-- Subtitle / Thesis -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subtitle / Thesis Statement</label>
                <input type="text" name="subtitle" 
                       value="<?= e($_POST['subtitle'] ?? $article['subtitle']) ?>"
                       class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <!-- Summary / Excerpt -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Summary / Excerpt *</label>
                <textarea name="excerpt" rows="3" required 
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition leading-relaxed"><?= e($_POST['excerpt'] ?? $article['excerpt']) ?></textarea>
            </div>

            <!-- Rich Content Editor with Complete Toolbar -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Article Content * (Rich Text &amp; Semantic HTML)
                    </label>
                    <span class="text-[11px] text-slate-400">Headings, paragraphs, blockquotes, lists, links, citations</span>
                </div>
                
                <!-- Formatting Toolbar -->
                <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100 rounded-t-xl border-t border-x border-slate-200 text-xs select-none">
                    <button type="button" onclick="formatInsert('<h2>', '</h2>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold text-navy-900" title="Heading 2">H2</button>
                    <button type="button" onclick="formatInsert('<h3>', '</h3>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold text-navy-900" title="Heading 3">H3</button>
                    <button type="button" onclick="formatInsert('<p>', '</p>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50" title="Paragraph">P</button>
                    <button type="button" onclick="formatInsert('<strong>', '</strong>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
                    <button type="button" onclick="formatInsert('<em>', '</em>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
                    <button type="button" onclick="formatInsert('<blockquote>\n  <p>', '</p>\n</blockquote>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50" title="Blockquote"><i class="fa-solid fa-quote-left"></i></button>
                    <button type="button" onclick="formatInsert('<ul>\n  <li>', '</li>\n  <li>Second item</li>\n</ul>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50" title="Unordered List"><i class="fa-solid fa-list-ul"></i></button>
                    <button type="button" onclick="formatInsert('<ol>\n  <li>', '</li>\n  <li>Second step</li>\n</ol>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50" title="Ordered List"><i class="fa-solid fa-list-ol"></i></button>
                    <button type="button" onclick="insertLink()" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50" title="Hyperlink"><i class="fa-solid fa-link"></i></button>
                    <button type="button" onclick="insertImage()" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50" title="Insert Image"><i class="fa-solid fa-image"></i></button>
                    <button type="button" onclick="formatInsert('<cite class=\"text-xs text-slate-500 block mt-2\">— ', '</cite>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-mono text-[11px]" title="Cite Source">Cite</button>
                </div>

                <textarea name="content" id="articleContent" rows="18" required 
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-b-xl focus:outline-none focus:border-lwblue focus:bg-white font-mono leading-relaxed transition"><?= e($_POST['content'] ?? $article['content']) ?></textarea>
            </div>

            <!-- References and Sources Field -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    References &amp; Cited Academic Sources
                </label>
                <textarea name="references_sources" rows="3" 
                          placeholder="List references, primary citations, or peer-reviewed monographs..."
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition leading-relaxed font-mono"><?= e($_POST['references_sources'] ?? ($article['references_sources'] ?? '')) ?></textarea>
            </div>

            <!-- Optional Tags Field -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keywords / Optional Tags (Comma separated)
                </label>
                <input type="text" name="tags" 
                       value="<?= e($_POST['tags'] ?? ($article['tags'] ?? '')) ?>"
                       placeholder="e.g. Media Literacy, AI Ethics, Algorithmic Bias"
                       class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <!-- Review History -->
            <?php if (!empty($reviewHistory)): ?>
                <div class="pt-6 border-t border-slate-200 space-y-3">
                    <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-lwblue"></i>
                        <span>Editorial Review History</span>
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($reviewHistory as $rev): ?>
                            <div class="p-3 bg-surface rounded-xl border border-slate-200 text-xs space-y-1">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span class="font-bold text-navy-900"><?= e($rev['reviewer_name']) ?> (Action: <?= e($rev['action']) ?>)</span>
                                    <span><?= e(format_date($rev['created_at'])) ?></span>
                                </div>
                                <?php if (!empty($rev['feedback'])): ?>
                                    <p class="text-slate-700"><?= e($rev['feedback']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar Options Area (Desktop ~34% / 4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Publishing Actions Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Actions &amp; Workflow</span>
                    <i class="fa-solid fa-sliders text-lwblue"></i>
                </h3>

                <!-- Staff Role / Governance Status Control -->
                <?php if ($isStaff): ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Set Publication Status</label>
                        <select name="status" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-lwblue">
                            <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft (Saved locally)</option>
                            <option value="pending_review" <?= $article['status'] === 'pending_review' ? 'selected' : '' ?>>Pending Review (In queue)</option>
                            <option value="revision_requested" <?= $article['status'] === 'revision_requested' ? 'selected' : '' ?>>Revision Requested</option>
                            <option value="approved" <?= $article['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published Live</option>
                            <option value="archived" <?= $article['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Editorial Feedback / Review Note</label>
                        <textarea name="feedback" rows="2" placeholder="Notes for author, revision requirements, or approval reasoning..."
                                  class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
                    </div>
                <?php endif; ?>

                <div class="space-y-2.5 pt-2">
                    <button type="submit" name="submit_action" value="update" 
                            class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Update Article</span>
                    </button>

                    <?php if (in_array($article['status'], ['draft', 'revision_requested'], true)): ?>
                        <button type="submit" name="submit_action" value="submit_review" 
                                class="w-full py-3 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Submit for Editorial Review</span>
                        </button>
                    <?php endif; ?>

                    <?php if ($article['status'] === 'draft' || $isStaff): ?>
                        <button type="submit" name="submit_action" value="delete_draft" 
                                onclick="return confirm('Are you sure you want to permanently delete this draft?');"
                                class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-trash text-xs"></i>
                            <span>Delete Draft</span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estimated Reading Time</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="reading_time" value="<?= (int)$article['reading_time'] ?>" min="1" max="120" 
                               class="w-24 text-xs p-2.5 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue font-bold">
                        <span class="text-xs text-slate-400">minutes</span>
                    </div>
                </div>
            </div>

            <!-- Categories Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Assign Topics</span>
                    <i class="fa-solid fa-hashtag text-slate-400"></i>
                </h3>
                <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1 text-xs">
                    <?php foreach ($allCategories as $cat): ?>
                        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-surface cursor-pointer transition">
                            <input type="checkbox" name="category_ids[]" value="<?= (int)$cat['id'] ?>" 
                                   <?= in_array((int)$cat['id'], $assignedCatIds, true) ? 'checked' : '' ?>
                                   class="rounded text-lwblue focus:ring-lwblue">
                            <span class="text-slate-800 font-semibold"><?= e($cat['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cover Image Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Cover Image</span>
                    <i class="fa-solid fa-image text-slate-400"></i>
                </h3>

                <?php if (!empty($article['cover_image'])): ?>
                    <div class="aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-900">
                        <img src="<?= asset($article['cover_image']) ?>" alt="Cover" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Replace Image (JPG, PNG, WEBP, SVG &lt; 5MB)</label>
                    <input type="file" name="cover_upload" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-surface file:text-navy-900 hover:file:bg-slate-200 cursor-pointer">
                </div>
            </div>

        </div>

    </div>
</form>

<script>
function formatInsert(startTag, endTag) {
    const textarea = document.getElementById('articleContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);
    const replacement = startTag + (selected || 'Sample text') + endTag;
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + startTag.length, start + replacement.length - endTag.length);
}

function insertLink() {
    const url = prompt('Enter hyperlink URL (https://...):', 'https://');
    if (url) {
        formatInsert('<a href="' + encodeURI(url) + '" target="_blank" rel="noopener noreferrer">', '</a>');
    }
}

function insertImage() {
    const imgUrl = prompt('Enter Image URL:', 'assets/images/article-medialiteracy.svg');
    const altText = prompt('Enter Alt Description:', 'Illustration');
    if (imgUrl) {
        const textarea = document.getElementById('articleContent');
        const imgTag = '\n<figure class="my-6">\n  <img src="' + imgUrl + '" alt="' + (altText || '') + '" class="rounded-xl shadow-md">\n  <figcaption class="text-xs text-slate-400 mt-2 text-center">' + (altText || '') + '</figcaption>\n</figure>\n';
        const start = textarea.selectionStart;
        textarea.value = textarea.value.substring(0, start) + imgTag + textarea.value.substring(start);
        textarea.focus();
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
