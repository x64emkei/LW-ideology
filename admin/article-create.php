<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin', 'editor', 'writer']);

$db = Database::getConnection();
$user = current_user();
$error = null;

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $title = Security::sanitize($_POST['title'] ?? '');
    $subtitle = Security::sanitize($_POST['subtitle'] ?? '');
    $excerpt = Security::sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $readingTime = max(1, (int)($_POST['reading_time'] ?? 5));
    $coverImage = trim($_POST['cover_image'] ?? 'assets/images/article-medialiteracy.svg');
    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
    $status = $_POST['status'] ?? 'draft';

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
                // Record in media table
                $db->prepare("INSERT INTO media (uploader_id, filename, original_name, file_path, mime_type, file_size, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                   ->execute([$user['id'], $newFilename, $file['name'], $coverImage, $mime, $file['size']]);
            }
        } else {
            $error = 'Invalid image upload. Please use JPG, PNG, WEBP, or SVG under 5MB.';
        }
    }

    // Role checks on status
    if ($status === 'published' && !user_has_any_role(['super_admin', 'admin', 'editor'])) {
        $status = 'pending_review'; // Writers cannot publish directly
    }

    if (empty($title) || empty($excerpt) || empty($content)) {
        $error = 'Please fill in Title, Excerpt, and Article Content.';
    } else {
        // Generate unique slug
        $baseSlug = slugify($title);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $check = $db->prepare("SELECT id FROM articles WHERE slug = ? LIMIT 1");
            $check->execute([$slug]);
            if (!$check->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        try {
            $db->beginTransaction();

            $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

            $stmt = $db->prepare("
                INSERT INTO articles (author_id, title, slug, subtitle, excerpt, content, cover_image, reading_time, status, published_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $user['id'],
                $title,
                $slug,
                $subtitle,
                $excerpt,
                $content,
                $coverImage,
                $readingTime,
                $status,
                $publishedAt
            ]);
            $articleId = (int)$db->lastInsertId();

            // Link categories
            if (!empty($categoryIds)) {
                $linkStmt = $db->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
                foreach ($categoryIds as $cId) {
                    $linkStmt->execute([$articleId, $cId]);
                }
            }

            // If submitted for review, log review entry
            if ($status === 'pending_review') {
                $db->prepare("INSERT INTO editorial_reviews (article_id, reviewer_id, action, feedback, created_at) VALUES (?, ?, 'submitted', 'Draft submitted by author for editorial peer review.', NOW())")
                   ->execute([$articleId, $user['id']]);
            }

            $db->commit();

            log_activity((int)$user['id'], 'article_created', 'article', $articleId, "Article '{$title}' created with status: {$status}");
            flash('success', "Article '{$title}' was successfully created!");
            header('Location: ' . url('admin/articles.php'));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Article create error: " . $e->getMessage());
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$adminPageTitle = 'Create New Editorial Monograph';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Manuscript Submission</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Compose Editorial Article</h1>
        <p class="text-xs text-slate-500 mt-0.5">Author a new research commentary, civic toolkit, or philosophical essay.</p>
    </div>
    <a href="<?= url('admin/articles.php') ?>" class="text-xs font-bold text-slate-600 hover:text-navy-900">
        &larr; Back to Articles
    </a>
</div>

<?php if ($error): ?>
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= e($error) ?></span>
    </div>
<?php endif; ?>

<form action="<?= url('admin/article-create.php') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
    <?= Security::csrfField() ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Main Form (8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Article Title *</label>
                <input type="text" name="title" required placeholder="e.g. Navigating Algorithmic Echo Chambers in Contemporary Society"
                       value="<?= e($_POST['title'] ?? '') ?>"
                       class="w-full text-base font-bold px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subtitle / Thesis Statement</label>
                <input type="text" name="subtitle" placeholder="A concise secondary heading outlining the core inquiry..."
                       value="<?= e($_POST['subtitle'] ?? '') ?>"
                       class="w-full text-xs px-4 py-2.5 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Summary / Excerpt * (Used in previews &amp; SEO)</label>
                <textarea name="excerpt" rows="3" required placeholder="A 2-3 sentence overview of the article findings and recommendations..."
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition"><?= e($_POST['excerpt'] ?? '') ?></textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Main Article Body * (Supports HTML &amp; Rich Formatting)</label>
                    <span class="text-[11px] text-slate-400">Headings, paragraphs, blockquotes, lists</span>
                </div>
                
                <!-- Quick formatting toolbar -->
                <div class="flex flex-wrap items-center gap-1.5 p-2 bg-slate-100 rounded-t-xl border-t border-x border-slate-200 text-xs">
                    <button type="button" onclick="insertTag('<h2>', '</h2>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold">H2</button>
                    <button type="button" onclick="insertTag('<h3>', '</h3>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold">H3</button>
                    <button type="button" onclick="insertTag('<p>', '</p>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">P</button>
                    <button type="button" onclick="insertTag('<strong>', '</strong>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold">B</button>
                    <button type="button" onclick="insertTag('<em>', '</em>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 italic">I</button>
                    <button type="button" onclick="insertTag('<blockquote>', '</blockquote>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">Quote</button>
                    <button type="button" onclick="insertTag('<ul>\n  <li>', '</li>\n</ul>')" class="px-2 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50">List</button>
                </div>

                <textarea name="content" id="articleContentInput" rows="16" required placeholder="Write your full editorial monograph here. Use semantic HTML paragraphs, blockquotes, and lists for optimal reading formatting..."
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-b-xl focus:outline-none focus:border-lwblue focus:bg-white font-mono leading-relaxed transition"><?= e($_POST['content'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Sidebar Options (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Publishing Actions Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Publication &amp; Workflow
                </h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-lwblue">
                        <option value="draft">Draft (Save locally)</option>
                        <option value="pending_review" selected>Submit for Review</option>
                        <?php if (user_has_any_role(['super_admin', 'admin', 'editor'])): ?>
                            <option value="published">Publish Immediately</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estimated Reading Time (Minutes)</label>
                    <input type="number" name="reading_time" value="6" min="1" max="60" 
                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Save &amp; Process Article</span>
                    </button>
                </div>
            </div>

            <!-- Categories Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Assign Categories
                </h3>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-2 text-xs">
                    <?php foreach ($categories as $cat): ?>
                        <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="category_ids[]" value="<?= (int)$cat['id'] ?>" class="rounded text-lwblue focus:ring-lwblue">
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

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Select Preset Cover Asset</label>
                    <select name="cover_image" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-lwblue">
                        <option value="assets/images/article-medialiteracy.svg">Media Literacy Theme</option>
                        <option value="assets/images/article-misinformation.svg">Misinformation Alert Theme</option>
                        <option value="assets/images/article-digitalcitizenship.svg">Digital Citizenship Theme</option>
                        <option value="assets/images/article-criticalthinking.svg">Critical Thinking Theme</option>
                        <option value="assets/images/article-ai-society.svg">Technology &amp; AI Theme</option>
                        <option value="assets/images/article-socialissues.svg">Social Issues Theme</option>
                        <option value="assets/images/article-philosophy.svg">Philosophy Theme</option>
                        <option value="assets/images/article-dialogue.svg">Community Dialogue Theme</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Or Upload Custom Cover (JPG, PNG, SVG)</label>
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
