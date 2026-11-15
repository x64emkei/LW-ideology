<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['super_admin', 'admin']);

$db = Database::getConnection();
$user = current_user();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = Security::sanitize($_POST['name'] ?? '');
        $description = Security::sanitize($_POST['description'] ?? '');
        $icon = Security::sanitize($_POST['icon'] ?? 'bookmark');
        $colorHex = Security::sanitize($_POST['color_hex'] ?? '#1557A6');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $order = (int)($_POST['display_order'] ?? 0);

        if (!empty($name)) {
            $slug = slugify($name);
            try {
                $stmt = $db->prepare("
                    INSERT INTO categories (name, slug, description, icon, color_hex, is_featured, display_order, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $slug, $description, $icon, $colorHex, $isFeatured, $order]);
                log_activity((int)$user['id'], 'category_created', 'category', (int)$db->lastInsertId(), "Created category: {$name}");
                flash('success', "Category '{$name}' created successfully.");
            } catch (Exception $e) {
                flash('error', "Category creation failed: A category with that name or slug may already exist.");
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = Security::sanitize($_POST['name'] ?? '');
        $description = Security::sanitize($_POST['description'] ?? '');
        $icon = Security::sanitize($_POST['icon'] ?? 'bookmark');
        $colorHex = Security::sanitize($_POST['color_hex'] ?? '#1557A6');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $order = (int)($_POST['display_order'] ?? 0);

        if ($id > 0 && !empty($name)) {
            $slug = slugify($name);
            $stmt = $db->prepare("
                UPDATE categories 
                SET name = ?, slug = ?, description = ?, icon = ?, color_hex = ?, is_featured = ?, display_order = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $slug, $description, $icon, $colorHex, $isFeatured, $order, $id]);
            log_activity((int)$user['id'], 'category_updated', 'category', $id, "Updated category: {$name}");
            flash('success', "Category updated.");
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            log_activity((int)$user['id'], 'category_deleted', 'category', $id, "Deleted category #{$id}");
            flash('success', "Category deleted.");
        }
    }

    header('Location: ' . url('admin/categories.php'));
    exit;
}

// Fetch categories with article counts
$categories = $db->query("
    SELECT c.*, COUNT(ac.article_id) AS article_count
    FROM categories c
    LEFT JOIN article_categories ac ON c.id = ac.category_id
    GROUP BY c.id
    ORDER BY c.display_order ASC, c.name ASC
")->fetchAll();

$adminPageTitle = 'Categories & Topic Management';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Taxonomy</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Topics &amp; Categories</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage thematic research pillars, editorial classifications, and navigation topics.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Add Category Form (4 cols) -->
    <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100">
            Create New Topic
        </h2>

        <form action="<?= url('admin/categories.php') ?>" method="POST" class="space-y-4">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Media Literacy"
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Brief explanation of this topic area..."
                          class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Icon (FontAwesome)</label>
                    <input type="text" name="icon" value="bookmark" placeholder="newspaper, brain..." 
                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Color (Hex)</label>
                    <input type="color" name="color_hex" value="#1557A6" 
                           class="w-full h-11 p-1 bg-surface border border-slate-200 rounded-xl cursor-pointer">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 items-center">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Display Order</label>
                    <input type="number" name="display_order" value="1" min="0" 
                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                </div>
                <div class="pt-5">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="is_featured" value="1" checked class="rounded text-lwblue focus:ring-lwblue">
                        <span>Featured Topic</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Category</span>
            </button>
        </form>
    </div>

    <!-- Category Table (8 cols) -->
    <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface text-slate-400 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200">
                        <th class="py-3.5 px-6">Name &amp; Slug</th>
                        <th class="py-3.5 px-4">Description</th>
                        <th class="py-3.5 px-4 text-center">Featured</th>
                        <th class="py-3.5 px-4 text-center">Articles</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-6 font-bold text-navy-900">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: <?= e($cat['color_hex'] ?? '#1557A6') ?>"></span>
                                    <div>
                                        <p class="leading-tight"><?= e($cat['name']) ?></p>
                                        <p class="text-[10px] text-slate-400 font-mono font-normal">/<?= e($cat['slug']) ?></p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4 text-slate-500 max-w-xs truncate">
                                <?= e($cat['description']) ?>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <?= $cat['is_featured'] ? '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-check"></i></span>' : '<span class="text-slate-300">—</span>' ?>
                            </td>

                            <td class="py-4 px-4 text-center font-bold">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px]">
                                    <?= (int)$cat['article_count'] ?>
                                </span>
                            </td>

                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <a href="<?= url('pages/topic.php?slug=' . urlencode($cat['slug'])) ?>" target="_blank" class="p-1.5 text-slate-400 hover:text-lwblue" title="View Public Topic">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>

                                <form action="<?= url('admin/categories.php') ?>" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700" title="Delete Category">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
