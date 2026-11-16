<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['administrator', 'super_admin', 'admin', 'editor', 'contributor', 'writer']);

$db = Database::getConnection();
$user = current_user();
if (!$user) {
    flash('error', 'Session expired. Please log in again.');
    header('Location: ' . url('auth/login.php'));
    exit;
}

$error = null;
$isStaff = user_has_any_role(['administrator', 'super_admin', 'admin', 'editor']);

// Fetch categories safely
$categories = [];
try {
    $categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC, name ASC")->fetchAll();
} catch (Throwable $e) {
    error_log("Failed to load categories: " . $e->getMessage());
    $error = "Notice: Could not load categories from database. Please verify that 'database/infinityfree_setup.sql' was imported in phpMyAdmin.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $title = Security::sanitize($_POST['title'] ?? '');
    $subtitle = Security::sanitize($_POST['subtitle'] ?? '');
    $excerpt = Security::sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $referencesSources = Security::sanitize($_POST['references_sources'] ?? '');
    $tags = Security::sanitize($_POST['tags'] ?? '');
    $readingTime = max(1, (int)($_POST['reading_time'] ?? 5));
    $coverImage = trim($_POST['cover_image'] ?? 'assets/images/article-medialiteracy.svg');
    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);
    
    // Action determines initial status
    $submitAction = $_POST['submit_action'] ?? 'draft';
    
    if ($submitAction === 'save_draft') {
        $status = 'draft';
    } elseif ($submitAction === 'submit_review') {
        $status = 'pending_review';
    } elseif ($submitAction === 'publish_now' && $isStaff) {
        $status = 'published';
    } else {
        $status = $isStaff ? ($_POST['status'] ?? 'draft') : 'draft';
    }

    // Role check: Contributors cannot directly publish
    if (!$isStaff && in_array($status, ['published', 'approved'], true)) {
        $status = 'pending_review';
    }

    // Handle file upload if provided
    if (!empty($_FILES['cover_upload']['name']) && $_FILES['cover_upload']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_upload'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        
        // Safe MIME type detection
        $mime = 'image/jpeg';
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($file['tmp_name']);
            if ($detected) $mime = $detected;
        } elseif (!empty($file['type'])) {
            $mime = $file['type'];
        }

        if (in_array($ext, $allowedExts, true) && $file['size'] <= MAX_UPLOAD_SIZE) {
            $newFilename = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!is_dir(UPLOAD_DIR)) {
                @mkdir(UPLOAD_DIR, 0755, true);
            }
            $destination = UPLOAD_DIR . '/' . $newFilename;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $coverImage = 'assets/uploads/' . $newFilename;
                try {
                    $db->prepare("INSERT INTO media (uploader_id, filename, original_name, file_path, mime_type, file_size, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                       ->execute([$user['id'], $newFilename, $file['name'], $coverImage, $mime, $file['size']]);
                } catch (Throwable $ignore) {
                    // Non-fatal if media table log fails
                }
            }
        } else {
            $error = 'Invalid image upload. Please use JPG, PNG, WEBP, or SVG under 5MB.';
        }
    }

    if (empty($title) || empty($excerpt) || empty($content)) {
        $error = 'Please fill in Title, Summary/Excerpt, and Article Content.';
    } elseif (!$error) {
        // Sanitize article body HTML
        $sanitizedContent = Security::sanitizeArticleHtml($content);

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
                INSERT INTO articles (author_id, title, slug, subtitle, excerpt, content, references_sources, tags, cover_image, reading_time, status, published_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $user['id'],
                $title,
                $slug,
                $subtitle,
                $excerpt,
                $sanitizedContent,
                $referencesSources,
                $tags,
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

            if ($status === 'draft') {
                flash('success', "Draft '{$title}' saved successfully to MySQL.");
            } elseif ($status === 'pending_review') {
                flash('success', "Article '{$title}' submitted for editorial peer review.");
            } else {
                flash('success', "Article '{$title}' published live to the public website.");
            }

            // Redirect appropriately
            if ($isStaff) {
                header('Location: ' . url('admin/articles.php'));
            } else {
                header('Location: ' . url('pages/dashboard.php'));
            }
            exit;

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Article create error: " . $e->getMessage());
            $error = 'Database error during article creation: ' . $e->getMessage();
        }
    }
}

$adminPageTitle = 'Create Editorial Article';
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header Breadcrumb & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Editorial Studio</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Compose New Article</h1>
        <p class="text-xs text-slate-500 mt-0.5">Author a research commentary, media literacy analysis, or philosophical essay.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= $isStaff ? url('admin/articles.php') : url('pages/dashboard.php') ?>" 
           class="text-xs font-bold text-slate-600 hover:text-navy-900 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Return</span>
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
<form action="<?= url('admin/article-create.php') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
    <?= Security::csrfField() ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Main Content Area (Desktop ~66% / 8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-6">
            
            <!-- Article Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Article Title *</label>
                <input type="text" name="title" required placeholder="e.g. Navigating Algorithmic Echo Chambers in Contemporary Society"
                       value="<?= e($_POST['title'] ?? '') ?>"
                       class="w-full text-base sm:text-lg font-bold px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <!-- Subtitle / Thesis -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subtitle / Thesis Statement</label>
                <input type="text" name="subtitle" placeholder="A secondary thesis statement outlining the research inquiry..."
                       value="<?= e($_POST['subtitle'] ?? '') ?>"
                       class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

            <!-- Summary / Excerpt -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Summary / Excerpt * (Used in card previews &amp; SEO)</label>
                <textarea name="excerpt" rows="3" required placeholder="A concise 2-3 sentence abstract summarizing the monograph findings and recommendations..."
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition leading-relaxed"><?= e($_POST['excerpt'] ?? '') ?></textarea>
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
                    <button type="button" onclick="formatInsert('<h2>', '</h2>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold text-navy-900" title="Section Heading 2">H2</button>
                    <button type="button" onclick="formatInsert('<h3>', '</h3>')" class="px-2.5 py-1 bg-white border border-slate-200 rounded hover:bg-slate-50 font-bold text-navy-900" title="Sub-section Heading 3">H3</button>
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
                          placeholder="Compose your full article here. Use the toolbar buttons above to format headings, paragraphs, blockquotes, lists, links, and citations. Server sanitization ensures clean semantic rendering..."
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-b-xl focus:outline-none focus:border-lwblue focus:bg-white font-mono leading-relaxed transition"><?= e($_POST['content'] ?? '') ?></textarea>
            </div>

            <!-- References and Sources Field -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    References &amp; Cited Academic Sources
                </label>
                <textarea name="references_sources" rows="3" 
                          placeholder="List bibliographical references, URLs, peer-reviewed monographs, or archive documents supporting your article claims..."
                          class="w-full text-xs p-4 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition leading-relaxed font-mono"><?= e($_POST['references_sources'] ?? '') ?></textarea>
            </div>

            <!-- Optional Tags Field -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keywords / Optional Tags (Comma separated)
                </label>
                <input type="text" name="tags" placeholder="e.g. Media Literacy, AI Ethics, Algorithmic Bias, Digital Citizenship"
                       value="<?= e($_POST['tags'] ?? '') ?>"
                       class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
            </div>

        </div>

        <!-- Sidebar Options Area (Desktop ~34% / 4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Publishing Actions Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Workflow &amp; Actions</span>
                    <i class="fa-solid fa-cloud-arrow-up text-lwblue"></i>
                </h3>

                <div class="space-y-2.5">
                    <button type="submit" name="submit_action" value="save_draft" 
                            class="w-full py-3 bg-surface hover:bg-slate-200 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fa-regular fa-floppy-disk text-xs"></i>
                        <span>Save Draft</span>
                    </button>

                    <button type="submit" name="submit_action" value="submit_review" 
                            class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Submit for Peer Review</span>
                    </button>

                    <?php if ($isStaff): ?>
                        <button type="submit" name="submit_action" value="publish_now" 
                                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                            <i class="fa-solid fa-globe text-xs"></i>
                            <span>Publish Immediately</span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estimated Reading Time</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="reading_time" value="5" min="1" max="120" 
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
                    <?php foreach ($categories as $cat): ?>
                        <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-surface cursor-pointer transition">
                            <input type="checkbox" name="category_ids[]" value="<?= (int)$cat['id'] ?>" class="rounded text-lwblue focus:ring-lwblue">
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

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Upload File (JPG, PNG, WEBP, SVG &lt; 5MB)</label>
                    <input type="file" name="cover_upload" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-surface file:text-navy-900 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Or Select Predefined Illustration</label>
                    <select name="cover_image" class="w-full text-xs p-2.5 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none">
                        <option value="assets/images/article-medialiteracy.svg">Media Literacy Artwork</option>
                        <option value="assets/images/article-misinformation.svg">Misinformation / Disinformation Shield</option>
                        <option value="assets/images/article-criticalthinking.svg">Critical Thinking Concept</option>
                        <option value="assets/images/article-digitalcitizenship.svg">Digital Citizenship Network</option>
                        <option value="assets/images/article-socialissues.svg">Social Issues &amp; Community</option>
                        <option value="assets/images/article-ai-society.svg">Technology &amp; Artificial Intelligence</option>
                        <option value="assets/images/article-philosophy.svg">Philosophy &amp; Democratic Ethics</option>
                        <option value="assets/images/article-dialogue.svg">Informed Public Discourse</option>
                    </select>
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
