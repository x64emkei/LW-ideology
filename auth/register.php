<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

// If already logged in, redirect
if (is_logged_in()) {
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    if (!Security::checkRateLimit('registration', 5, 600)) {
        $error = 'Too many registration requests. Please wait a few minutes before trying again.';
    } else {
        $name = Security::sanitize($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $email = Security::validateEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation rules
        if (empty($name) || empty($username) || !$email || empty($password)) {
            $error = 'Please fill out all required fields with a valid email address.';
        } elseif (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            $error = 'Username must be at least 3 characters and contain only letters, numbers, dots, and hyphens.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long for account security.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password confirmation does not match.';
        } else {
            $db = Database::getConnection();

            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'An account with that username or email address already exists.';
            } else {
                try {
                    $db->beginTransaction();

                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $db->prepare("
                        INSERT INTO users (name, username, email, password_hash, avatar, status, must_change_password, created_at)
                        VALUES (?, ?, ?, ?, 'assets/images/avatar-reader.svg', 'active', 0, NOW())
                    ");
                    $ins->execute([$name, $username, $email, $hash]);
                    $newUserId = (int)$db->lastInsertId();

                    // Assign reader role (role ID 5)
                    $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = 'reader' LIMIT 1");
                    $roleStmt->execute();
                    $readerRoleId = (int)($roleStmt->fetchColumn() ?: 5);

                    $assignRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    $assignRole->execute([$newUserId, $readerRoleId]);

                    $db->commit();

                    // Log activity
                    log_activity($newUserId, 'user_registered', 'user', $newUserId, "Public reader account created: {$username}");

                    // Auto-login
                    $userRecord = [
                        'id' => $newUserId,
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                        'avatar' => 'assets/images/avatar-reader.svg'
                    ];
                    login_user($userRecord, ['reader']);

                    flash('success', 'Your reader account was created successfully! Welcome to the LagunaWISE Ideology Division.');
                    header('Location: ' . url('pages/dashboard.php'));
                    exit;

                } catch (Exception $e) {
                    $db->rollBack();
                    error_log("Registration error: " . $e->getMessage());
                    $error = 'An unexpected error occurred during account creation. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Join the Community — Register Reader Account';
$pageDescription = 'Create an account on the LagunaWISE Ideology Division platform to bookmark monographs, join research discussions, and follow updates.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="py-16 sm:py-24 bg-surface flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-lg px-4 sm:px-6">
        
        <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/90 shadow-xl space-y-6">
            
            <div class="text-center space-y-2">
                <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="LagunaWISE Emblem" class="w-14 h-14 mx-auto object-contain rounded-full shadow-xs">
                <span class="text-[11px] font-bold tracking-widest text-lwblue uppercase">Public Community Registration</span>
                <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Create a Reader Account</h1>
                <p class="text-xs text-editorial-muted">Engage with research insights, save reading lists, and participate in civic discussions.</p>
            </div>

            <?php if ($error): ?>
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= url('auth/register.php') ?>" method="POST" class="space-y-4">
                <?= Security::csrfField() ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Full Legal / Academic Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Juan P. Dela Cruz" 
                           value="<?= e($_POST['name'] ?? '') ?>"
                           class="w-full text-xs px-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                        <input type="text" name="username" required placeholder="e.g. juan.delacruz" 
                               value="<?= e($_POST['username'] ?? '') ?>"
                               class="w-full text-xs px-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="juan@domain.com" 
                               value="<?= e($_POST['email'] ?? '') ?>"
                               class="w-full text-xs px-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password * (Min 8 chars)</label>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full text-xs px-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password *</label>
                        <input type="password" name="confirm_password" required placeholder="••••••••" 
                               class="w-full text-xs px-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 leading-relaxed">
                    By registering, you agree to uphold respectful discourse as outlined in the 
                    <a href="<?= url('pages/about.php#editorial-charter') ?>" class="text-lwblue underline">Editorial Charter</a>.
                </p>

                <button type="submit" class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2">
                    <span>Register Reader Account</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </form>

            <div class="text-center pt-2 border-t border-slate-100 text-xs text-slate-500">
                <p>Already have an authorized account? <a href="<?= url('auth/login.php') ?>" class="font-bold text-navy-900 hover:underline">Log in here</a></p>
            </div>

        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
