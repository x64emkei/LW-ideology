<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

require_login();
$db = Database::getConnection();
$user = current_user();

$profileError = null;
$passwordError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = Security::sanitize($_POST['name'] ?? '');
        $bio = Security::sanitize($_POST['bio'] ?? '');
        $email = Security::validateEmail($_POST['email'] ?? '');
        $avatar = trim($_POST['avatar'] ?? $user['avatar']);

        if (empty($name) || !$email) {
            $profileError = 'Please provide a valid name and email address.';
        } else {
            // Check email uniqueness if changed
            if ($email !== $user['email']) {
                $chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk->execute([$email, $user['id']]);
                if ($chk->fetch()) {
                    $profileError = 'That email address is already claimed by another account.';
                }
            }

            if (!$profileError) {
                $upd = $db->prepare("UPDATE users SET name = ?, bio = ?, email = ?, avatar = ?, updated_at = NOW() WHERE id = ?");
                $upd->execute([$name, $bio, $email, $avatar, $user['id']]);
                log_activity((int)$user['id'], 'profile_updated', 'user', (int)$user['id'], "Profile details updated.");
                flash('success', "Your profile details have been updated.");
                header('Location: ' . url('admin/profile.php'));
                exit;
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Fetch current password hash
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $currentHash = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $currentHash)) {
            $passwordError = 'Current password verification failed. Please enter your existing password correctly.';
        } elseif (strlen($newPassword) < 8) {
            $passwordError = 'New password must be at least 8 characters in length.';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = 'New password confirmation does not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET password_hash = ?, must_change_password = 0, updated_at = NOW() WHERE id = ?");
            $upd->execute([$newHash, $user['id']]);
            log_activity((int)$user['id'], 'password_changed', 'user', (int)$user['id'], "Password updated successfully.");
            flash('success', "Your password was updated successfully. Your account is fully secured.");
            header('Location: ' . url('admin/profile.php'));
            exit;
        }
    }
}

$adminPageTitle = 'My Profile & Credentials';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-lwblue">Account Governance</span>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Profile &amp; Security Settings</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage your personal credentials, biography, and security keys.</p>
    </div>
</div>

<?php if (!empty($user['must_change_password'])): ?>
    <div class="p-4 bg-amber-50 border border-amber-300 rounded-2xl text-amber-900 text-xs font-semibold flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base"></i>
        <span>Security Notice: You are using the default local demonstration credentials. Please update your password below to secure this administrative account.</span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 max-w-5xl">
    
    <!-- Profile Info Form (7 cols) -->
    <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-user-pen text-lwblue"></i>
            <span>Personal Information</span>
        </h2>

        <?php if ($profileError): ?>
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                <?= e($profileError) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/profile.php') ?>" method="POST" class="space-y-4">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="update_profile">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Display Name *</label>
                <input type="text" name="name" required value="<?= e($user['name']) ?>" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Username (Immutable)</label>
                    <input type="text" value="<?= e($user['username']) ?>" disabled
                           class="w-full text-xs p-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" required value="<?= e($user['email']) ?>" 
                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Biography / Academic Focus</label>
                <textarea name="bio" rows="3" placeholder="Tell readers about your research areas and background..."
                          class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"><?= e($user['bio'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Avatar Preset</label>
                <select name="avatar" class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-lwblue">
                    <option value="assets/images/avatar-admin.svg" <?= $user['avatar'] === 'assets/images/avatar-admin.svg' ? 'selected' : '' ?>>Admin Crest (Navy &amp; Blue)</option>
                    <option value="assets/images/avatar-editor.svg" <?= $user['avatar'] === 'assets/images/avatar-editor.svg' ? 'selected' : '' ?>>Editorial Lead (Marla Adriano)</option>
                    <option value="assets/images/avatar-writer1.svg" <?= $user['avatar'] === 'assets/images/avatar-writer1.svg' ? 'selected' : '' ?>>Research Director (Michael Ordenes)</option>
                    <option value="assets/images/avatar-writer2.svg" <?= $user['avatar'] === 'assets/images/avatar-writer2.svg' ? 'selected' : '' ?>>Writer / Contributor</option>
                    <option value="assets/images/avatar-reader.svg" <?= $user['avatar'] === 'assets/images/avatar-reader.svg' ? 'selected' : '' ?>>Reader Emblem (Blue)</option>
                    <option value="assets/images/default-avatar.svg" <?= $user['avatar'] === 'assets/images/default-avatar.svg' ? 'selected' : '' ?>>Standard Monogram</option>
                </select>
            </div>

            <button type="submit" class="px-6 py-2.5 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-xl transition shadow">
                Save Profile
            </button>
        </form>
    </div>

    <!-- Password Change Form (5 cols) -->
    <div class="lg:col-span-5 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-5">
        <h2 class="text-sm font-bold text-navy-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-key text-lwblue"></i>
            <span>Update Password</span>
        </h2>

        <?php if ($passwordError): ?>
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                <?= e($passwordError) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/profile.php') ?>" method="POST" class="space-y-4">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="change_password">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Current Password *</label>
                <input type="password" name="current_password" required placeholder="••••••••" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">New Password * (Min 8 chars)</label>
                <input type="password" name="new_password" required placeholder="••••••••" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password *</label>
                <input type="password" name="confirm_password" required placeholder="••••••••" 
                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
            </div>

            <button type="submit" class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow">
                Change Password
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
