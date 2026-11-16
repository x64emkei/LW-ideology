<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();
    $email = Security::validateEmail($_POST['email'] ?? '');
    if ($email) {
        log_activity(null, 'password_reset_request', 'user', null, "Password reset requested for {$email}");
    }
    // Always show uniform message to prevent email enumeration attacks
    $sent = true;
}

$pageTitle = 'Password Recovery — LagunaWISE Ideology Division';
$pageDescription = 'Recover access to your LagunaWISE account.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="py-16 sm:py-24 bg-surface flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-md px-4 sm:px-6">
        <div class="bg-white p-8 sm:p-10 rounded-lg border border-slate-200 shadow-md space-y-6">
            
            <div class="text-center space-y-2">
                <span class="text-[11px] font-bold tracking-widest text-lwblue uppercase">Account Security</span>
                <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Recover Password</h1>
                <p class="text-xs text-editorial-muted">Enter your registered email address</p>
            </div>

            <?php if ($sent): ?>
                <div class="p-6 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs space-y-2">
                    <p class="font-bold flex items-center gap-1.5 text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Recovery Instructions Transmitted</span>
                    </p>
                    <p class="text-emerald-700 leading-relaxed">
                        If an account exists under that email address, security instructions have been dispatched. For local development inquiries, please contact the local administrator.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('auth/login.php') ?>" class="font-bold text-emerald-900 underline">Return to Sign In</a>
                    </div>
                </div>
            <?php else: ?>
                <form action="<?= url('auth/forgot-password.php') ?>" method="POST" class="space-y-4">
                    <?= Security::csrfField() ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Registered Email Address</label>
                        <input type="email" name="email" required placeholder="name@domain.com" 
                               class="w-full text-xs px-3.5 py-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue focus:bg-white transition">
                    </div>

                    <button type="submit" class="w-full py-3 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-xl transition duration-150 shadow-md">
                        Request Password Reset
                    </button>
                </form>

                <div class="text-center pt-2 text-xs text-slate-500">
                    <a href="<?= url('auth/login.php') ?>" class="text-lwblue hover:underline">Back to Sign In</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
