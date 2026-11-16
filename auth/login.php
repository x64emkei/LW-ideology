<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    $user = current_user();
    if (user_has_any_role(['administrator', 'super_admin', 'admin', 'editor'])) {
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
                $roles = !empty($user['role_names']) ? explode(',', $user['role_names']) : ['contributor'];
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

                if (in_array('administrator', $roles, true) || in_array('super_admin', $roles, true) || in_array('admin', $roles, true) || in_array('editor', $roles, true)) {
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

<!-- Responsive Two-Column Login Section -->
<section class="py-12 sm:py-20 bg-surface min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-4xl px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- Column 1: Organization Identity & Mission (Desktop ~45% / 5 cols) -->
            <div class="lg:col-span-5 bg-gradient-to-br from-navy-900 via-navy-800 to-navy-900 text-white p-8 sm:p-10 rounded-lg border border-navy-700 shadow-md flex flex-col justify-between relative overflow-hidden" style="background-color: #061638;">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-lwblue-light/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <div class="flex items-center gap-3">
                        <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="LagunaWISE Emblem" class="w-12 h-12 object-contain rounded-full shadow-md">
                        <div>
                            <span class="block text-xs font-bold tracking-widest text-lwblue-light uppercase">LagunaWISE</span>
                            <span class="text-base font-extrabold text-white tracking-tight">IDEOLOGY DIVISION</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4">
                        <span class="inline-block px-3 py-1 rounded-full bg-navy-800 border border-navy-700 text-[10px] font-bold tracking-wider text-lwblue-ice uppercase">
                            Editorial Portal
                        </span>
                        <h2 class="text-2xl font-extrabold text-white tracking-tight leading-snug">
                            Understanding Ideas.<br>
                            <span class="text-lwblue-light">Challenging Perspectives.</span><br>
                            Empowering Minds.
                        </h2>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Sign in to draft research monographs, review peer submissions, or manage your personal reading bookmarks.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-navy-700/80 space-y-2 text-xs text-slate-300">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-lwblue-light text-xs"></i>
                            <span>Peer-reviewed editorial monographs</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-lwblue-light text-xs"></i>
                            <span>Transparent editorial workflows</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-lwblue-light text-xs"></i>
                            <span>Nonpartisan media literacy toolkits</span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-8 mt-8 border-t border-navy-700/80 text-[11px] text-slate-400">
                    <p>New to LagunaWISE? <a href="<?= url('auth/register.php') ?>" class="text-lwblue-light font-bold hover:underline">Register here &rarr;</a></p>
                </div>
            </div>

            <!-- Column 2: Login Form (Desktop ~55% / 7 cols) -->
            <div class="lg:col-span-7 bg-white p-8 sm:p-10 rounded-lg border border-slate-200 shadow-md space-y-6 flex flex-col justify-between">
                
                <div class="space-y-2">
                    <span class="text-[11px] font-bold tracking-widest text-lwblue uppercase">Authentication</span>
                    <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Access Your Account</h1>
                    <p class="text-xs text-editorial-muted">Enter your registered credentials to access your dashboard.</p>
                </div>

                <?php if ($error): ?>
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation shrink-0 text-sm"></i>
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= url('auth/login.php') ?>" method="POST" class="space-y-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username or Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" 
                                   name="identifier" 
                                   required 
                                   placeholder="e.g. maria.santos or maria@domain.com" 
                                   value="<?= e($_POST['identifier'] ?? '') ?>"
                                   class="w-full text-xs pl-10 pr-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Password</label>
                            <a href="<?= url('auth/forgot-password.php') ?>" class="text-[11px] text-lwblue hover:underline">Forgot?</a>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" 
                                   name="password" 
                                   required 
                                   placeholder="••••••••" 
                                   class="w-full text-xs pl-10 pr-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition duration-150 shadow-md shadow-lwblue/20 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                            <span>Sign In to Dashboard</span>
                        </button>
                    </div>
                </form>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Don't have an account yet?</span>
                    <a href="<?= url('auth/register.php') ?>" class="text-lwblue font-bold hover:underline">
                        Register Contributor &rarr;
                    </a>
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
