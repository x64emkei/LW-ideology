<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin', 'editor', 'writer']);

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
    header('Location: ' . url('admin/articles.php'));
    exit;
}

// RBAC: Writers can only edit their own draft/revision_requested articles
$isStaff = user_has_any_role(['super_admin', 'admin', 'editor']);
$isAuthor = ((int)$article['author_id'] === (int)$user['id']);

if (!$isStaff && !$isAuthor) {
    flash('error', 'Unauthorized: You are only permitted to edit your own authored drafts.');
    header('Location: ' . url('admin/articles.php'));
    exit;
}

// Fetch categories
$allCategories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

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

    $title = Security::sanitize($_POST['title'] ?? '');
    $subtitle = Security::sanitize($_POST['subtitle'] ?? '');
    $excerpt = Security::sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $readingTime = max(1, (int)($_POST['reading_time'] ?? 5));
    $coverImage = trim($_POST['cover_image'] ?? $article['cover_image']);
    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
    $newStatus = $_POST['status'] ?? $article['status'];
    $feedback = Security::sanitize($_POST['feedback'] ?? '');

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

    // Role check: Writers cannot directly set to 'published' or 'approved'
    if (!$isStaff && in_array($newStatus, ['published', 'approved'], true)) {
        $newStatus = 'pending_review';
    }

    if (empty($title) || empty($excerpt) || empty($content)) {
        $error = 'Please fill in Title, Excerpt, and Article Content.';
    } else {
        try {
            $db->beginTransaction();

            $publishedAt = $article['published_at'];
            if ($newStatus === 'published' && empty($publishedAt)) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            $update = $db->prepare("
                UPDATE articles
                SET title = ?, subtitle = ?, excerpt = ?, content = ?, cover_image = ?, reading_time = ?, status = ?, published_at = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $update->execute([
                $title,
                $subtitle,
                $excerpt,
                $content,
                $coverImage,
                $readingTime,
                $newStatus,
                $publishedAt,
                $articleId
            ]);

            // Sync categories
            $db->prepare("DELETE FROM article_categories WHERE article_id = ?")->execute([$articleId]);
            if (!empty($categoryIds)) {
                $linkStmt = $db->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
                foreach ($categoryIds as $cId) {
                    $linkStmt->execute([$articleId, $cId]);
                }
            }

            // Log review / feedback if status changed or feedback provided
            if (!empty($feedback) || $newStatus !== $article['status']) {
                $actionType = match($newStatus) {
                    'published' => 'published',
                    'approved' => 'approved',
                    'revision_requested' => 'revision_requested',
                    'pending_review' => 'submitted',
                    default => 'rejected'
                };
                $reviewIns = $db->prepare("INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at) VALUES (?, ?, ?, ?, NOW())");
                $reviewIns->execute([$articleId, $user['id'], $actionType, $feedback ?: "Status updated to {$newStatus}."]);
            }

            $db->commit();

            log_activity((int)$user['id'], 'article_updated', 'article', $articleId, "Article #{$articleId} updated. Status: {$newStatus}");
            flash('success', "Article '{$title}' updated successfully.");
            header('Location: ' . url('admin/articles.php'));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Article update error: " . $e->getMessage());
            $error = 'Error updating article: ' . $e->getMessage();
        }
    }
}

$adminPageTitle = 'Edit Monograph: ' . $article['title'];
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Editorial Revision</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Edit Manuscript</h1>
        <p class="text-xs text-slate-500 mt-0.5">Author: <strong><?= e($article['author_name']) ?></strong> • Created: <?= e(format_date($article['created_at'])) ?></p>
    </div>
    <div class="flex items-center gap-2">
        <?php if ($article['status'] === 'published'): ?>
            <a href="<?= url('pages/article.php?slug=' . urlencode($article['slug'])) ?>" target="_blank" class="px-3 py-2 bg-white border border-slate-200 hover:border-lwblue text-navy-900 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                <span>View Live</span>
            </a>
        <?php endif; ?>
        <a href="<?= url('admin/articles.php') ?>" class="text-xs font-bold text-slate-600 hover:text-navy-900">
            &larr; Back to Articles
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= e($error) ?></span>
    </div>
<?php endif; ?>

<form action="<?= url('admin/article-edit.php?id=' . (int)$articleId) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
    <?= Security::csrfField() ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Main Form (8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Article Title *</label>
                <input type="text" name="title" required value="<?= e($article['title']) ?>"
                       class="w-full text-base font-bold px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subtitle / Thesis Statement</label>
                <input type="text" name="subtitle" value="<?= e($article['subtitle']) ?>"
                       class="w-full text-xs px-4 py-2.5 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Summary / Excerpt *</label>
                <textarea name="excerpt" rows="3" required class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition"><?= e($article['excerpt']) ?></textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Main Article Body *</label>
                    <span class="text-[11px] text-slate-400">Semantic HTML formatting</span>
                </div>

                <!-- Formatting Toolbar -->
                <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100 rounded-t-xl border-t border-x border-slate-200 text-xs">
                    <button type="button" onclick="insertTag('<h2>', '</h2>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold">H2</button>
                    <button type="button" onclick="insertTag('<h3>', '</h3>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold">H3</button>
                    <button type="button" onclick="insertTag('<p>', '</p>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">P</button>
                    <button type="button" onclick="insertTag('<strong>', '</strong>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold">B</button>
                    <button type="button" onclick="insertTag('<em>', '</em>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 italic">I</button>
                    <button type="button" onclick="insertTag('<blockquote>', '</blockquote>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">Quote</button>
                    <button type="button" onclick="insertTag('<ul>\n  <li>', '</li>\n</ul>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">List</button>
                </div>

                <textarea name="content" id="articleContentInput" rows="18" required 
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-b-xl focus:outline-none focus:border-lwblue focus:bg-white font-mono leading-relaxed transition"><?= e($article['content']) ?></textarea>
            </div>

            <!-- Editorial Review History -->
            <?php if (!empty($reviewHistory)): ?>
                <div class="pt-4 border-t border-slate-200 space-y-3">
                    <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider">Editorial Review Timeline</h3>
                    <div class="space-y-2">
                        <?php foreach ($reviewHistory as $rev): ?>
                            <div class="p-3 bg-surface rounded-xl border border-slate-200 text-xs space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold text-slate-500">
                                    <span class="text-lwblue uppercase"><?= e($rev['action']) ?> by <?= e($rev['reviewer_name']) ?></span>
                                    <span><?= e(format_date($rev['created_at'])) ?></span>
                                </div>
                                <p class="text-slate-700"><?= e($rev['feedback']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Options (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Publishing Actions Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Editorial Workflow &amp; Status
                </h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-bold focus:outline-none focus:border-lwblue">
                        <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft (Internal)</option>
                        <option value="pending_review" <?= $article['status'] === 'pending_review' ? 'selected' : '' ?>>Pending Review (Submitted)</option>
                        <?php if ($isStaff): ?>
                            <option value="revision_requested" <?= $article['status'] === 'revision_requested' ? 'selected' : '' ?>>Request Revisions</option>
                            <option value="approved" <?= $article['status'] === 'approved' ? 'selected' : '' ?>>Approved (Ready to publish)</option>
                            <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published (Live)</option>
                            <option value="archived" <?= $article['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if ($isStaff): ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Editorial Feedback / Decision Notes</label>
                        <textarea name="feedback" rows="3" placeholder="Provide constructive feedback for the author or publication record..."
                                  class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reading Time (Minutes)</label>
                    <input type="number" name="reading_time" value="<?= (int)$article['reading_time'] ?>" min="1" max="60" 
                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Update Article</span>
                    </button>
                </div>
            </div>

            <!-- Categories Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Categories
                </h3>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-2 text-xs">
                    <?php foreach ($allCategories as $cat): ?>
                        <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="category_ids[]" value="<?= (int)$cat['id'] ?>" 
                                   <?= in_array((int)$cat['id'], $assignedCatIds, true) ? 'checked' : '' ?>
                                   class="rounded text-lwblue focus:ring-lwblue">
                            <span class="text-slate-800 font-medium"><?= e($cat['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cover Image Options Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Cover Image
                </h3>

                <?php if (!empty($article['cover_image'])): ?>
                    <div class="rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-900">
                        <img src="<?= asset($article['cover_image']) ?>" alt="Current Cover" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Preset Cover Asset</label>
                    <select name="cover_image" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-lwblue">
                        <option value="assets/images/article-medialiteracy.svg" <?= $article['cover_image'] === 'assets/images/article-medialiteracy.svg' ? 'selected' : '' ?>>Media Literacy Theme</option>
                        <option value="assets/images/article-misinformation.svg" <?= $article['cover_image'] === 'assets/images/article-misinformation.svg' ? 'selected' : '' ?>>Misinformation Alert Theme</option>
                        <option value="assets/images/article-digitalcitizenship.svg" <?= $article['cover_image'] === 'assets/images/article-digitalcitizenship.svg' ? 'selected' : '' ?>>Digital Citizenship Theme</option>
                        <option value="assets/images/article-criticalthinking.svg" <?= $article['cover_image'] === 'assets/images/article-criticalthinking.svg' ? 'selected' : '' ?>>Critical Thinking Theme</option>
                        <option value="assets/images/article-ai-society.svg" <?= $article['cover_image'] === 'assets/images/article-ai-society.svg' ? 'selected' : '' ?>>Technology &amp; AI Theme</option>
                        <option value="assets/images/article-socialissues.svg" <?= $article['cover_image'] === 'assets/images/article-socialissues.svg' ? 'selected' : '' ?>>Social Issues Theme</option>
                        <option value="assets/images/article-philosophy.svg" <?= $article['cover_image'] === 'assets/images/article-philosophy.svg' ? 'selected' : '' ?>>Philosophy Theme</option>
                        <option value="assets/images/article-dialogue.svg" <?= $article['cover_image'] === 'assets/images/article-dialogue.svg' ? 'selected' : '' ?>>Community Dialogue Theme</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Or Replace With File Upload</label>
                    <input type="file" name="cover_upload" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-lwblue hover:file:bg-blue-100">
                </div>
            </div>

        </div>

    </div>
</form>

<script>
function insertTag(openTag, closeTag) {
    const textarea = document.getElementById('articleContentInput');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    const replacement = openTag + selected + closeTag;
    textarea.value = text.substring(0, start) + replacement + text.substring(end);
    textarea.focus();
    textarea.selectionStart = start + openTag.length;
    textarea.selectionEnd = start + openTag.length + selected.length;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
