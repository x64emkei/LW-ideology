<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_role(['administrator', 'super_admin', 'admin']);

$db = Database::getConnection();
$currentUser = current_user();

// Handle Role Updates or Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($targetUserId > 0) {
        // Prevent modifying own super_admin account state
        if ($targetUserId === (int)$currentUser['id'] && ($action === 'toggle_status' || $action === 'change_role')) {
            flash('warning', 'You cannot alter or suspend your own active administrator session.');
        } else {
            if ($action === 'toggle_status') {
                $newStatus = ($_POST['status'] === 'active') ? 'inactive' : 'active';
                $db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $targetUserId]);
                log_activity((int)$currentUser['id'], 'user_status_changed', 'user', $targetUserId, "User #{$targetUserId} status toggled to {$newStatus}.");
                flash('success', "User status updated to {$newStatus}.");
            } elseif ($action === 'change_role') {
                $newRoleName = $_POST['role_name'] ?? 'reader';
                $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
                $roleStmt->execute([$newRoleName]);
                $roleId = $roleStmt->fetchColumn();

                if ($roleId) {
                    $db->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$targetUserId]);
                    $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$targetUserId, $roleId]);
                    log_activity((int)$currentUser['id'], 'user_role_changed', 'user', $targetUserId, "User #{$targetUserId} role updated to {$newRoleName}.");
                    flash('success', "User role successfully changed to " . ucfirst(str_replace('_', ' ', $newRoleName)) . ".");
                }
            }
        }
    }

    header('Location: ' . url('admin/users.php'));
    exit;
}

// Fetch users
$search = trim($_GET['q'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$where = ['1=1'];
$params = [];

if (!empty($search)) {
    $where[] = '(u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($roleFilter)) {
    $where[] = 'r.name = ?';
    $params[] = $roleFilter;
}

$whereSql = implode(' AND ', $where);

$query = "
    SELECT u.*, r.name AS role_name, r.display_name AS role_display
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    WHERE {$whereSql}
    ORDER BY u.created_at DESC
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$usersList = $stmt->fetchAll();

// Roles for dropdown
$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();

$adminPageTitle = 'User Management & Role Governance';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Governance</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">User Governance &amp; Roles</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage staff permissions, review author roles, and oversee reader accounts.</p>
    </div>
</div>

<!-- Search & Role Filter -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between text-xs">
    
    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= url('admin/users.php') ?>" class="px-3 py-1.5 rounded-lg <?= empty($roleFilter) ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">All Users</a>
        <?php foreach ($roles as $r): ?>
            <a href="<?= url('admin/users.php?role=' . urlencode($r['name'])) ?>" class="px-3 py-1.5 rounded-lg <?= $roleFilter === $r['name'] ? 'bg-navy-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100' ?>">
                <?= e($r['display_name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form action="<?= url('admin/users.php') ?>" method="GET" class="relative max-w-xs w-full">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, username, email..." 
               class="w-full pl-8 pr-3 py-2 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
    </form>

</div>

<!-- Users Table -->
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-surface text-slate-400 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200">
                    <th class="py-3.5 px-6">User Identity</th>
                    <th class="py-3.5 px-4">Email</th>
                    <th class="py-3.5 px-4">Assigned Role</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Registered</th>
                    <th class="py-3.5 px-6 text-right">Role Assignment / Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($usersList)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">No registered users found.</td>
                    </tr>
                <?php else: ?>
                <?php foreach ($usersList as $u): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-4 px-6 font-bold text-navy-900">
                            <div class="flex items-center gap-3">
                                <img src="<?= asset($u['avatar'] ?? 'assets/images/default-avatar.svg') ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                <div>
                                    <p><?= e($u['name']) ?></p>
                                    <p class="text-[10px] text-slate-400 font-mono font-normal">@<?= e($u['username']) ?></p>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-slate-600">
                            <?= e($u['email']) ?>
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                <?= match($u['role_name']) {
                                    'super_admin' => 'bg-purple-100 text-purple-800',
                                    'admin' => 'bg-indigo-100 text-indigo-800',
                                    'editor' => 'bg-blue-100 text-blue-800',
                                    'writer' => 'bg-teal-100 text-teal-800',
                                    default => 'bg-slate-100 text-slate-700'
                                } ?>">
                                <?= e($u['role_display'] ?? 'Reader') ?>
                            </span>
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                <?= e($u['status']) ?>
                            </span>
                        </td>

                        <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                            <?= e(format_date($u['created_at'])) ?>
                        </td>

                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                <!-- Role Change Dropdown Form -->
                                <form action="<?= url('admin/users.php') ?>" method="POST" class="inline-flex items-center gap-2">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">

                                    <select name="role_name" onchange="this.form.submit()" class="text-[11px] p-1.5 bg-surface border border-slate-200 rounded-lg font-medium focus:outline-none">
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= e($r['name']) ?>" <?= $u['role_name'] === $r['name'] ? 'selected' : '' ?>>
                                                Set as <?= e($r['display_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                <!-- Toggle Status Form -->
                                <form action="<?= url('admin/users.php') ?>" method="POST" class="inline ml-1" onsubmit="return confirm('Toggle status for this user?')">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="status" value="<?= e($u['status']) ?>">
                                    <button type="submit" class="p-1.5 <?= $u['status'] === 'active' ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' ?>" title="Toggle Status">
                                        <i class="fa-solid <?= $u['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-[11px] text-slate-400 font-semibold italic">Your Session</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
