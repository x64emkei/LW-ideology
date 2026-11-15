<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin', 'editor']);

$db = Database::getConnection();
$user = current_user();
$error = null;

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload' && !empty($_FILES['media_file']['name'])) {
        $file = $_FILES['media_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'File upload failed. Please try again.';
        } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
            $error = 'File exceeds maximum upload size (5MB).';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
            $mime = mime_content_type($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/gif'];

            if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                $error = 'Security Alert: Only valid image files (JPG, PNG, WEBP, SVG, GIF) are accepted.';
            } else {
                $safeName = 'media_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = UPLOAD_DIR . '/' . $safeName;

                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $relPath = 'assets/uploads/' . $safeName;
                    $stmt = $db->prepare("
                        INSERT INTO media (uploader_id, filename, original_name, file_path, mime_type, file_size, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([$user['id'], $safeName, $file['name'], $relPath, $mime, $file['size']]);
                    log_activity((int)$user['id'], 'media_uploaded', 'media', (int)$db->lastInsertId(), "Uploaded image: {$file['name']}");
                    flash('success', "Media asset uploaded successfully.");
                    header('Location: ' . url('admin/media.php'));
                    exit;
                } else {
                    $error = 'Failed to move uploaded file to target storage directory.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $fetch = $db->prepare("SELECT file_path FROM media WHERE id = ?");
            $fetch->execute([$id]);
            $path = $fetch->fetchColumn();

            if ($path) {
                $fullPath = ROOT_PATH . '/' . ltrim($path, '/');
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
            log_activity((int)$user['id'], 'media_deleted', 'media', $id, "Deleted media asset #{$id}");
            flash('success', "Media asset deleted.");
            header('Location: ' . url('admin/media.php'));
            exit;
        }
    }
}

// Fetch media files
$mediaItems = $db->query("
    SELECT m.*, u.name AS uploader_name
    FROM media m
    LEFT JOIN users u ON m.uploader_id = u.id
    ORDER BY m.created_at DESC
")->fetchAll();

$adminPageTitle = 'Media Library & Assets';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Asset Storage</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Media Library</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage photographs, infographics, and visual assets for editorial monographs.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= e($error) ?></span>
    </div>
<?php endif; ?>

<!-- Upload Card -->
<div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
        Upload New Visual Asset
    </h2>

    <form action="<?= url('admin/media.php') ?>" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-center">
        <?= Security::csrfField() ?>
        <input type="hidden" name="action" value="upload">

        <input type="file" name="media_file" required accept="image/*"
               class="flex-1 text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-lwblue hover:file:bg-blue-100 cursor-pointer">

        <button type="submit" class="px-6 py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Upload Image</span>
        </button>
    </form>
</div>

<!-- Media Gallery Grid -->
<div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
    <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
        Stored Assets (<?= count($mediaItems) ?>)
    </h2>

    <?php if (empty($mediaItems)): ?>
        <div class="p-12 text-center text-xs text-slate-400 space-y-2">
            <i class="fa-regular fa-image text-3xl mb-1 text-slate-300"></i>
            <p>No uploaded media assets yet. Use the uploader above to add article imagery.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($mediaItems as $m): ?>
                <div class="bg-surface rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between group">
                    <div class="aspect-square bg-slate-900 overflow-hidden relative">
                        <img src="<?= asset($m['file_path']) ?>" alt="<?= e($m['original_name']) ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-navy-900/80 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2 p-2">
                            <button type="button" onclick="navigator.clipboard.writeText('<?= e($m['file_path']) ?>'); alert('Asset path copied!');"
                                    class="p-2 rounded-lg bg-white text-navy-900 text-xs hover:bg-lwblue hover:text-white" title="Copy Path">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                            <form action="<?= url('admin/media.php') ?>" method="POST" class="inline" onsubmit="return confirm('Delete this image?')">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                <button type="submit" class="p-2 rounded-lg bg-rose-600 text-white text-xs hover:bg-rose-700" title="Delete Image">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-2.5 text-[10px] text-slate-500 space-y-0.5">
                        <p class="font-bold text-navy-900 truncate" title="<?= e($m['original_name']) ?>"><?= e($m['original_name']) ?></p>
                        <p><?= round($m['file_size'] / 1024, 1) ?> KB • <?= e(date('M j', strtotime($m['created_at']))) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
