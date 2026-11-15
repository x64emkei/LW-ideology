<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    $user = current_user();
    if (user_has_any_role(['super_admin', 'admin', 'editor'])) {
        header('Location: ' . url('admin/index.php'));
    } else {
        header('Location: ' . url('pages/dashboard.php'));
    }
    exit;
}

$error = null;
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    if (!Security::checkRateLimit('login_attempt', 6, 300)) {
        $error = 'Too many failed login attempts. For security reasons, please wait 5 minutes before trying again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? '';

        if (empty($identifier) || empty($password)) {
            $error = 'Please enter both your username/email and password.';
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT u.*, GROUP_CONCAT(r.name) AS role_names
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE (u.username = ? OR u.email = ?) AND u.status = 'active'
                GROUP BY u.id
                LIMIT 1
            ");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                Security::clearRateLimit('login_attempt');
                $roles = !empty($user['role_names']) ? explode(',', $user['role_names']) : ['reader'];
                login_user($user, $roles);

                flash('success', "Welcome back, {$user['name']}!");

                // Check mandatory password change
                if (!empty($user['must_change_password'])) {
                    header('Location: ' . url('admin/profile.php'));
                    exit;
                }

                if (!empty($redirect) && str_starts_with($redirect, '/')) {
                    header('Location: ' . $redirect);
                    exit;
                }

                if (in_array('super_admin', $roles, true) || in_array('admin', $roles, true) || in_array('editor', $roles, true)) {
                    header('Location: ' . url('admin/index.php'));
                } else {
                    header('Location: ' . url('pages/dashboard.php'));
                }
                exit;
            } else {
                $error = 'Invalid credentials. Please verify your username/email and password.';
            }
        }
    }
}

$pageTitle = 'Sign In — LagunaWISE Ideology Division';
$pageDescription = 'Sign in to your LagunaWISE Ideology Division account to access editorial submissions, bookmarks, and contributor tools.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="py-16 sm:py-24 bg-surface flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-md px-4 sm:px-6">
        
        <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/90 shadow-xl space-y-6">
            
            <div class="text-center space-y-2">
                <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="LagunaWISE Emblem" class="w-14 h-14 mx-auto object-contain rounded-full shadow-xs">
                <span class="text-[11px] font-bold tracking-widest text-lwblue uppercase">Authentication</span>
                <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Access Your Account</h1>
                <p class="text-xs text-editorial-muted">Enter your registered credentials to proceed</p>
            </div>

            <?php if ($error): ?>
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= url('auth/login.php') ?>" method="POST" class="space-y-4">
                <?= Security::csrfField() ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Username or Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                        <input type="text" name="identifier" required placeholder="admin or username@lagunawise.org" 
                               value="<?= e($_POST['identifier'] ?? '') ?>"
                               class="w-full text-xs pl-10 pr-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-slate-700">Password</label>
                        <a href="<?= url('auth/forgot-password.php') ?>" class="text-[11px] font-semibold text-lwblue hover:underline">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                        <input type="password" name="password" id="loginPassword" required placeholder="••••••••" 
                               class="w-full text-xs pl-10 pr-10 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                        <button type="button" onclick="const p = document.getElementById('loginPassword'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute right-3.5 top-3 text-slate-400 hover:text-slate-600 text-xs">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </form>

            <div class="text-center pt-2 border-t border-slate-100 text-xs text-slate-500">
                <p>New to the platform? <a href="<?= url('auth/register.php') ?>" class="font-bold text-lwblue hover:underline">Create a Reader Account</a></p>
            </div>

            <!-- Local Demo Accounts Helper Box -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 text-[11px] text-slate-600 space-y-2">
                <p class="font-bold text-navy-900 flex items-center gap-1.5 text-xs">
                    <i class="fa-solid fa-key text-amber-500"></i> Local Demonstration Accounts:
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="document.getElementsByName('identifier')[0].value='admin'; document.getElementById('loginPassword').value='admin123!';" 
                            class="text-left p-2 rounded bg-white border border-slate-200 hover:border-lwblue hover:bg-blue-50/50 transition">
                        <span class="font-bold block text-slate-800">Super Admin</span>
                        <span class="text-slate-500 font-mono text-[10px]">admin / admin123!</span>
                    </button>
                    <button type="button" onclick="document.getElementsByName('identifier')[0].value='elena.vance'; document.getElementById('loginPassword').value='editor123!';" 
                            class="text-left p-2 rounded bg-white border border-slate-200 hover:border-lwblue hover:bg-blue-50/50 transition">
                        <span class="font-bold block text-slate-800">Editor</span>
                        <span class="text-slate-500 font-mono text-[10px]">elena.vance / editor123!</span>
                    </button>
                    <button type="button" onclick="document.getElementsByName('identifier')[0].value='marcus.reyes'; document.getElementById('loginPassword').value='writer123!';" 
                            class="text-left p-2 rounded bg-white border border-slate-200 hover:border-lwblue hover:bg-blue-50/50 transition">
                        <span class="font-bold block text-slate-800">Writer</span>
                        <span class="text-slate-500 font-mono text-[10px]">marcus.reyes / writer123!</span>
                    </button>
                    <button type="button" onclick="document.getElementsByName('identifier')[0].value='juan.reader'; document.getElementById('loginPassword').value='reader123!';" 
                            class="text-left p-2 rounded bg-white border border-slate-200 hover:border-lwblue hover:bg-blue-50/50 transition">
                        <span class="font-bold block text-slate-800">Reader</span>
                        <span class="text-slate-500 font-mono text-[10px]">juan.reader / reader123!</span>
                    </button>
                </div>
            </div>

        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
