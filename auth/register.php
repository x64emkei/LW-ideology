<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

// If already logged in, redirect
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
        $agreeTerms = isset($_POST['agree_terms']) && $_POST['agree_terms'] === '1';

        // Strict Server-Side Validation Rules
        if (empty($name)) {
            $error = 'Please provide your full legal or academic name.';
        } elseif (empty($username)) {
            $error = 'Please choose a username.';
        } elseif (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            $error = 'Username must be at least 3 characters and contain only letters, numbers, dots, and hyphens.';
        } elseif (!$email) {
            $error = 'Please provide a valid, deliverable email address.';
        } elseif (empty($password)) {
            $error = 'Please provide a secure password.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long for account security.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password confirmation does not match the entered password.';
        } elseif (!$agreeTerms) {
            $error = 'You must agree to the LagunaWISE Editorial Terms and Privacy Policy to create an account.';
        } else {
            $db = Database::getConnection();

            // Check if username already exists
            $stmtUser = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmtUser->execute([$username]);
            if ($stmtUser->fetch()) {
                $error = "The username '{$username}' is already taken. Please select a different username.";
            } else {
                // Check if email already exists
                $stmtEmail = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmtEmail->execute([$email]);
                if ($stmtEmail->fetch()) {
                    $error = "An account with email '{$email}' already exists. Please log in instead.";
                } else {
                    try {
                        $db->beginTransaction();

                        // Count existing users to determine bootstrap role
                        $userCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                        $isFirstUser = ($userCount === 0);

                        // If first user ever, assign administrator; otherwise assign contributor
                        $targetRoleName = $isFirstUser ? 'administrator' : 'contributor';
                        $avatar = $isFirstUser ? 'assets/images/avatar-admin.svg' : 'assets/images/avatar-writer2.svg';

                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $ins = $db->prepare("
                            INSERT INTO users (name, username, email, password_hash, avatar, status, must_change_password, created_at)
                            VALUES (?, ?, ?, ?, ?, 'active', 0, NOW())
                        ");
                        $ins->execute([$name, $username, $email, $hash, $avatar]);
                        $newUserId = (int)$db->lastInsertId();

                        // Fetch target role id
                        $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
                        $roleStmt->execute([$targetRoleName]);
                        $targetRoleId = (int)$roleStmt->fetchColumn();

                        if (!$targetRoleId) {
                            // Fallback to role ID 3 or 1 if name mapping differs
                            $targetRoleId = $isFirstUser ? 1 : 3;
                        }

                        $assignRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                        $assignRole->execute([$newUserId, $targetRoleId]);

                        $db->commit();

                        // Log activity
                        log_activity($newUserId, 'user_registered', 'user', $newUserId, "Public account created: {$username} with role {$targetRoleName}");

                        // Auto-login
                        $userRecord = [
                            'id' => $newUserId,
                            'name' => $name,
                            'username' => $username,
                            'email' => $email,
                            'avatar' => $avatar
                        ];
                        login_user($userRecord, [$targetRoleName]);

                        if ($isFirstUser) {
                            flash('success', "Initial setup complete! Welcome, {$name}. Your account has been initialized as an Administrator. You have full governance over the platform.");
                            header('Location: ' . url('admin/index.php'));
                        } else {
                            flash('success', "Registration successful! Welcome to the LagunaWISE Ideology Division, {$name}. As a Contributor, you can now write, draft, and submit articles for review.");
                            header('Location: ' . url('pages/dashboard.php'));
                        }
                        exit;

                    } catch (Exception $e) {
                        $db->rollBack();
                        error_log("Registration error: " . $e->getMessage());
                        $error = 'An unexpected database error occurred during account registration. Please try again.';
                    }
                }
            }
        }
    }
}

$pageTitle = 'Register Contributor Account — LagunaWISE Ideology Division';
$pageDescription = 'Create an account on the LagunaWISE Ideology Division publication platform to draft monographs, submit articles for peer review, and engage in civic discussions.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- Responsive Two-Column Registration Section -->
<section class="py-12 sm:py-20 bg-surface min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-5xl px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- Column 1: Organization Branding & Mission (Desktop ~42% / 5 cols) -->
            <div class="lg:col-span-5 bg-gradient-to-br from-navy-900 via-navy-800 to-navy-900 text-white p-8 sm:p-10 rounded-3xl border border-navy-700 shadow-xl flex flex-col justify-between relative overflow-hidden">
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

                    <div class="space-y-3 pt-2">
                        <span class="inline-block px-3 py-1 rounded-full bg-navy-800 border border-navy-700 text-[10px] font-bold tracking-wider text-lwblue-ice uppercase">
                            Open Contributor Access
                        </span>
                        <h2 class="text-2xl font-extrabold text-white tracking-tight leading-snug">
                            Join Our Community of Inquirers &amp; Writers
                        </h2>
                        <p class="text-xs text-slate-300 leading-relaxed font-normal">
                            Registering grants you immediate access to our author portal. Draft research commentaries, submit evidence-grounded essays for peer review, and contribute to informed civic dialogue.
                        </p>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-navy-700/80 text-xs text-slate-300">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-pen-nib text-lwblue-light mt-0.5 shrink-0"></i>
                            <span><strong>Contributor Access:</strong> Draft and submit editorial articles immediately upon registration.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-scale-balanced text-lwblue-light mt-0.5 shrink-0"></i>
                            <span><strong>Peer Review Workflow:</strong> All articles undergo rigorous, constructive review by division editors.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-shield-halved text-lwblue-light mt-0.5 shrink-0"></i>
                            <span><strong>Privacy &amp; Security:</strong> Modern password hashing, CSRF protection, and zero commercial tracking.</span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-8 mt-8 border-t border-navy-700/80 text-[11px] text-slate-400">
                    <p>Already have an account? <a href="<?= url('auth/login.php') ?>" class="text-lwblue-light font-bold hover:underline">Sign in here &rarr;</a></p>
                </div>
            </div>

            <!-- Column 2: Registration Form (Desktop ~58% / 7 cols) -->
            <div class="lg:col-span-7 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/90 shadow-xl space-y-6 flex flex-col justify-between">
                
                <div class="space-y-2">
                    <span class="text-[11px] font-bold tracking-widest text-lwblue uppercase">Create Your Account</span>
                    <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Contributor Registration</h1>
                    <p class="text-xs text-editorial-muted">Complete the fields below to activate your contributor portal.</p>
                </div>

                <?php if ($error): ?>
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation shrink-0 text-sm"></i>
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= url('auth/register.php') ?>" method="POST" class="space-y-4">
                    <?= Security::csrfField() ?>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Full Legal / Academic Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Maria Clara Santos" 
                               value="<?= e($_POST['name'] ?? '') ?>"
                               class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Displayed on your published articles and author profile.</span>
                    </div>

                    <!-- Username & Email (Responsive 2-col on tablet/desktop) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username *</label>
                            <input type="text" name="username" required placeholder="e.g. maria.santos" 
                                   value="<?= e($_POST['username'] ?? '') ?>"
                                   class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Letters, numbers, dots, and hyphens.</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                            <input type="email" name="email" required placeholder="maria@domain.com" 
                                   value="<?= e($_POST['email'] ?? '') ?>"
                                   class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Used for notifications and account recovery.</span>
                        </div>
                    </div>

                    <!-- Password & Confirm Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Password * (Min 8 chars)</label>
                            <input type="password" name="password" required placeholder="••••••••" 
                                   class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password *</label>
                            <input type="password" name="confirm_password" required placeholder="••••••••" 
                                   class="w-full text-xs px-4 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                        </div>
                    </div>

                    <!-- Agreement Checkbox -->
                    <div class="pt-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="agree_terms" value="1" <?= isset($_POST['agree_terms']) ? 'checked' : '' ?> required
                                   class="mt-0.5 rounded text-lwblue focus:ring-lwblue shrink-0">
                            <span class="text-xs text-slate-600 leading-snug">
                                I agree to the <a href="<?= url('pages/about.php#editorial-charter') ?>" target="_blank" class="text-lwblue font-bold underline">Editorial Charter</a>, 
                                <a href="<?= url('pages/about.php#terms') ?>" target="_blank" class="text-lwblue font-bold underline">Terms of Use</a>, and 
                                <a href="<?= url('pages/about.php#privacy') ?>" target="_blank" class="text-lwblue font-bold underline">Privacy Policy</a> of the LagunaWISE Ideology Division.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button type="submit" 
                                class="w-full py-3.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition duration-150 shadow-md shadow-lwblue/20 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>Create Contributor Account</span>
                        </button>
                    </div>
                </form>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Already registered?</span>
                    <a href="<?= url('auth/login.php') ?>" class="text-lwblue font-bold hover:underline">
                        Sign In &rarr;
                    </a>
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
