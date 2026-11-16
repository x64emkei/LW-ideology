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
    $error = "Notice: Could not load categories from database.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    $title = Security::sanitize($_POST['title'] ?? '');
    $subtitle = Security::sanitize($_POST['subtitle'] ?? '');
    $excerpt = Security::sanitize($_POST['excerpt'] ?? '');
    // Markdown content is preserved as written
    $content = trim($_POST['content'] ?? '');
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
                $content,
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
        <p class="text-xs text-slate-500 mt-0.5">Author a research commentary, media literacy analysis, or philosophical essay in Markdown format.</p>
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

            <!-- Markdown Studio with Toolbar & Live Preview -->
            <div class="space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Article Manuscript * (Markdown Format &mdash; .md)
                        </label>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-lwblue border border-sky-200">MD Active</span>
                    </div>

                    <!-- Mode Tabs: Write vs Preview -->
                    <div class="inline-flex rounded-lg border border-slate-200 bg-slate-100 p-0.5 text-xs font-semibold">
                        <button type="button" id="tabWrite" onclick="switchEditorTab('write')" class="px-3 py-1 rounded-md bg-white text-navy-900 shadow-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-nib text-[10px] text-lwblue"></i>
                            <span>Write (MD)</span>
                        </button>
                        <button type="button" id="tabPreview" onclick="switchEditorTab('preview')" class="px-3 py-1 rounded-md text-slate-600 hover:text-navy-900 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-[10px]"></i>
                            <span>Live Preview</span>
                        </button>
                    </div>
                </div>

                <!-- Markdown Toolbar -->
                <div id="mdToolbar" class="flex flex-wrap items-center gap-1 p-2 bg-slate-100 rounded-t-xl border border-slate-200 text-xs select-none">
                    <button type="button" onclick="insertMd('## ', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-bold text-navy-900 hover:border-slate-300 transition" title="Section Heading (## Section)">H2</button>
                    <button type="button" onclick="insertMd('### ', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-bold text-navy-900 hover:border-slate-300 transition" title="Sub-section (### Sub-section)">H3</button>
                    <div class="h-4 w-px bg-slate-300 mx-0.5"></div>
                    <button type="button" onclick="insertMd('**', '**')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-bold text-navy-900 hover:border-slate-300 transition" title="Bold (**text**)"><i class="fa-solid fa-bold text-xs"></i></button>
                    <button type="button" onclick="insertMd('*', '*')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 italic text-navy-900 hover:border-slate-300 transition" title="Italic (*text*)"><i class="fa-solid fa-italic text-xs"></i></button>
                    <div class="h-4 w-px bg-slate-300 mx-0.5"></div>
                    <button type="button" onclick="insertMd('> ', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-navy-900 hover:border-slate-300 transition" title="Blockquote (> Quote)"><i class="fa-solid fa-quote-left text-xs"></i></button>
                    <button type="button" onclick="insertMd('- ', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-navy-900 hover:border-slate-300 transition" title="Bullet List (- item)"><i class="fa-solid fa-list-ul text-xs"></i></button>
                    <button type="button" onclick="insertMd('1. ', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-navy-900 hover:border-slate-300 transition" title="Numbered List (1. item)"><i class="fa-solid fa-list-ol text-xs"></i></button>
                    <div class="h-4 w-px bg-slate-300 mx-0.5"></div>
                    <button type="button" onclick="insertMdLink()" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-navy-900 hover:border-slate-300 transition" title="Link [text](url)"><i class="fa-solid fa-link text-xs"></i></button>
                    <button type="button" onclick="insertMdImage()" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-navy-900 hover:border-slate-300 transition" title="Image ![alt](url)"><i class="fa-solid fa-image text-xs"></i></button>
                    <button type="button" onclick="insertMd('`', '`')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-mono text-[11px] text-pink-600 hover:border-slate-300 transition" title="Inline Code (`code`)"><i class="fa-solid fa-code text-xs"></i></button>
                    <button type="button" onclick="insertMdCodeBlock()" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-mono text-[11px] text-slate-700 hover:border-slate-300 transition" title="Code Block (```)">&lt;/&gt;</button>
                    <div class="h-4 w-px bg-slate-300 mx-0.5"></div>
                    <button type="button" onclick="insertMd('\n\n---\n\n', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-navy-900 hover:border-slate-300 transition" title="Horizontal Divider (---)"><i class="fa-solid fa-minus text-xs"></i></button>
                    <button type="button" onclick="insertMd('\n\n— *Source / Citation*', '')" class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 font-mono text-[11px] text-slate-700 hover:border-slate-300 transition" title="Citation (Cite)">Cite</button>
                </div>

                <!-- Textarea Editor (Write Mode) -->
                <div id="editorWriteContainer">
                    <textarea name="content" id="articleContent" rows="18" required 
                              placeholder="Write your article in Markdown (.md) format.

Double Enter creates a clean, elegant paragraph break:

First paragraph of your monograph. In an information environment saturated with artificial intelligence and digital mediation, clear evidence matters more than ever.

Second paragraph separates cleanly and renders with high-readability editorial margins.

## Key Section Subheading
Detail your thesis arguments, research methodologies, or policy insights.

> Notable quotes or highlighted principles start with a greater-than symbol.

- Unordered evidence points start with a hyphen
- Second point of civic inquiry
1. Step-by-step or prioritized arguments use numbers"
                              class="w-full text-sm p-4 bg-surface border border-t-0 border-slate-200 rounded-b-xl focus:outline-none focus:border-lwblue focus:bg-white font-mono leading-relaxed transition"><?= e($_POST['content'] ?? '') ?></textarea>
                </div>

                <!-- Live Preview Container (Preview Mode) -->
                <div id="editorPreviewContainer" class="hidden min-h-[420px] p-6 sm:p-8 bg-surface rounded-b-xl border border-t-0 border-slate-200 overflow-y-auto">
                    <div id="livePreviewOutput" class="editorial-prose max-w-none">
                        <!-- Populated by JavaScript preview renderer -->
                    </div>
                </div>

                <!-- Markdown Helper Cheat Sheet -->
                <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 flex items-start gap-2 text-[11px] text-slate-600">
                    <i class="fa-solid fa-circle-info text-lwblue mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="text-navy-900 font-semibold">Markdown Formatting Quick Guide:</strong>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-slate-500 font-mono">
                            <span><code class="text-navy-900 font-bold">## Heading</code></span>
                            <span><code class="text-navy-900 font-bold">**Bold**</code></span>
                            <span><code class="text-navy-900 font-bold">*Italic*</code></span>
                            <span><code class="text-navy-900 font-bold">&gt; Quote</code></span>
                            <span><code class="text-navy-900 font-bold">- Bullet</code></span>
                            <span><code class="text-navy-900 font-bold">1. Numbered</code></span>
                            <span><code class="text-navy-900 font-bold">[Link](url)</code></span>
                            <span><code class="text-navy-900 font-bold">![Alt](image.png)</code></span>
                            <span class="text-lwblue font-sans font-semibold">Blank line = New Paragraph</span>
                        </div>
                    </div>
                </div>
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
function insertMd(prefix, suffix) {
    const textarea = document.getElementById('articleContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);
    const replacement = prefix + (selected || '') + suffix;
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    const cursor = start + prefix.length + (selected ? selected.length : 0);
    textarea.setSelectionRange(cursor, cursor);
}

function insertMdLink() {
    const url = prompt('Enter Hyperlink URL (e.g. https://...):', 'https://');
    if (url) {
        const textarea = document.getElementById('articleContent');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selected = textarea.value.substring(start, end) || 'Link Text';
        const linkMd = '[' + selected + '](' + encodeURI(url) + ')';
        textarea.value = textarea.value.substring(0, start) + linkMd + textarea.value.substring(end);
        textarea.focus();
    }
}

function insertMdImage() {
    const imgUrl = prompt('Enter Image URL or Path:', 'assets/images/article-medialiteracy.svg');
    if (imgUrl) {
        const alt = prompt('Enter Alt Description / Caption:', 'Descriptive illustration caption') || '';
        const imgMd = '\n\n![' + alt + '](' + imgUrl + ')\n\n';
        insertMd(imgMd, '');
    }
}

function insertMdCodeBlock() {
    insertMd('\n```\n', '\n```\n');
}

function switchEditorTab(mode) {
    const writeTab = document.getElementById('tabWrite');
    const previewTab = document.getElementById('tabPreview');
    const writeContainer = document.getElementById('editorWriteContainer');
    const previewContainer = document.getElementById('editorPreviewContainer');
    const toolbar = document.getElementById('mdToolbar');
    const previewOutput = document.getElementById('livePreviewOutput');
    const textarea = document.getElementById('articleContent');

    if (mode === 'preview') {
        writeTab.classList.remove('bg-white', 'text-navy-900', 'shadow-xs', 'font-bold');
        writeTab.classList.add('text-slate-600');
        previewTab.classList.add('bg-white', 'text-navy-900', 'shadow-xs', 'font-bold');
        previewTab.classList.remove('text-slate-600');

        writeContainer.classList.add('hidden');
        previewContainer.classList.remove('hidden');
        toolbar.classList.add('opacity-50', 'pointer-events-none');

        previewOutput.innerHTML = parseMarkdownToHtml(textarea.value);
    } else {
        previewTab.classList.remove('bg-white', 'text-navy-900', 'shadow-xs', 'font-bold');
        previewTab.classList.add('text-slate-600');
        writeTab.classList.add('bg-white', 'text-navy-900', 'shadow-xs', 'font-bold');
        writeTab.classList.remove('text-slate-600');

        previewContainer.classList.add('hidden');
        writeContainer.classList.remove('hidden');
        toolbar.classList.remove('opacity-50', 'pointer-events-none');
        textarea.focus();
    }
}

function parseMarkdownToHtml(md) {
    if (!md || !md.trim()) {
        return '<p class="text-slate-400 italic text-sm">Nothing to preview yet. Start typing your article in Write mode...</p>';
    }

    let text = md.trim().replace(/\r\n/g, '\n');

    // Code blocks
    const codeBlocks = [];
    text = text.replace(/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g, (match, lang, code) => {
        const id = '%%CB_' + codeBlocks.length + '%%';
        codeBlocks.push('<pre class="my-6 p-4 rounded-xl bg-slate-900 text-slate-100 overflow-x-auto text-xs font-mono"><code>' + escapeHtml(code) + '</code></pre>');
        return '\n\n' + id + '\n\n';
    });

    // Split paragraphs
    const blocks = text.split(/\n{2,}/);
    const htmlBlocks = [];

    blocks.forEach(block => {
        block = block.trim();
        if (!block) return;

        if (block.startsWith('%%CB_')) {
            const idx = parseInt(block.replace(/\D/g, ''), 10);
            if (codeBlocks[idx]) {
                htmlBlocks.push(codeBlocks[idx]);
                return;
            }
        }

        // Horizontal Rule
        if (/^(\*{3,}|-{3,}|_{3,})$/.test(block)) {
            htmlBlocks.push('<hr class="my-8 border-slate-200">');
            return;
        }

        // Headings
        const hMatch = block.match(/^(#{1,6})\s+(.+)$/);
        if (hMatch && !block.includes('\n')) {
            const level = hMatch[1].length;
            htmlBlocks.push('<h' + level + '>' + parseInline(hMatch[2]) + '</h' + level + '>');
            return;
        }

        // Blockquote
        if (block.startsWith('>')) {
            const lines = block.split('\n').map(l => l.replace(/^>\s?/, ''));
            htmlBlocks.push('<blockquote><p>' + lines.map(parseInline).join('<br>') + '</p></blockquote>');
            return;
        }

        // Unordered list
        if (/^[\*\-]\s+/m.test(block)) {
            const items = block.split('\n')
                .filter(l => /^[\*\-]\s+/.test(l.trim()))
                .map(l => '<li>' + parseInline(l.replace(/^[\*\-]\s+/, '')) + '</li>');
            if (items.length) {
                htmlBlocks.push('<ul>' + items.join('') + '</ul>');
                return;
            }
        }

        // Ordered list
        if (/^\d+\.\s+/m.test(block)) {
            const items = block.split('\n')
                .filter(l => /^\d+\.\s+/.test(l.trim()))
                .map(l => '<li>' + parseInline(l.replace(/^\d+\.\s+/, '')) + '</li>');
            if (items.length) {
                htmlBlocks.push('<ol>' + items.join('') + '</ol>');
                return;
            }
        }

        // HTML block passthrough
        if (/^<(p|div|figure|blockquote|h[1-6]|ul|ol)\b/i.test(block)) {
            htmlBlocks.push(parseInline(block));
            return;
        }

        // Paragraph
        htmlBlocks.push('<p>' + parseInline(block).replace(/\n/g, '<br>') + '</p>');
    });

    return htmlBlocks.join('\n\n');
}

function parseInline(text) {
    // Images
    text = text.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, (m, alt, url) => {
        return '<figure class="my-6"><img src="' + escapeHtml(url) + '" alt="' + escapeHtml(alt) + '" class="rounded-xl shadow-md mx-auto max-h-[400px] object-cover"><figcaption class="text-xs text-slate-500 mt-2 text-center">' + escapeHtml(alt) + '</figcaption></figure>';
    });

    // Links
    text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (m, label, url) => {
        return '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer" class="text-lwblue underline font-semibold">' + label + '</a>';
    });

    // Bold + Italic
    text = text.replace(/\*\*\*([^\*]+)\*\*\*/g, '<strong><em>$1</em></strong>');
    // Bold
    text = text.replace(/\*\*([^\*]+)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/__([^_\n]+)__/g, '<strong>$1</strong>');
    // Italic
    text = text.replace(/\*([^\*\n]+)\*/g, '<em>$1</em>');
    text = text.replace(/(?<=\s|^)_([^_\n]+)_(?=\s|$|[.,;:!?])/g, '<em>$1</em>');
    // Code
    text = text.replace(/`([^`\n]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-slate-100 text-pink-600 font-mono text-xs font-semibold">$1</code>');
    // Em dash
    text = text.replace(/(?<=\s)--(?=\s)/g, '&mdash;');

    return text;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
