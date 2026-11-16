<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'Contact & Partnerships — Ideology Division';
$pageDescription = 'Connect with the LagunaWISE Ideology Division for academic collaborations, media literacy seminars, research partnerships, and student inquiries.';

$submitted = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::enforceCsrf();

    if (!Security::checkRateLimit('contact_form', 4, 300)) {
        $error = 'Too many requests. Please wait a few minutes before submitting another inquiry.';
    } else {
        $name = Security::sanitize($_POST['name'] ?? '');
        $email = Security::validateEmail($_POST['email'] ?? '');
        $subject = Security::sanitize($_POST['subject'] ?? '');
        $message = Security::sanitize($_POST['message'] ?? '');

        if (empty($name) || !$email || empty($subject) || empty($message)) {
            $error = 'Please complete all required fields with a valid email address.';
        } else {
            // Save to activity log / contact messages
            log_activity(
                is_logged_in() ? (int)$_SESSION['user_id'] : null,
                'contact_inquiry',
                'contact',
                null,
                "Inquiry from {$name} ({$email}) | Subject: {$subject} | Message: " . substr($message, 0, 300)
            );
            $submitted = true;
            Security::clearRateLimit('contact_form');
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="bg-navy-900 text-white py-16 sm:py-20 border-b border-navy-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-4">
        <span class="text-xs font-bold tracking-widest text-lwblue-ice uppercase">Direct Engagement</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
            Connect with the Ideology Division
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto font-normal">
            We welcome academic inquiries, campus speaking requests, research collaborations, and community feedback.
        </p>
    </div>
</section>

<section class="py-16 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <!-- Left Info Card -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white p-8 rounded-lg border border-slate-200 shadow-sm space-y-6">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Headquarters</span>
                        <h2 class="text-xl font-bold text-navy-900 mt-1">LagunaWISE Center</h2>
                    </div>

                    <div class="space-y-4 text-xs text-slate-700">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-lwblue flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">Physical Registry</p>
                                <p class="text-slate-500 leading-relaxed">
                                    Barangay Barandal, Calamba City, Laguna 4027, Philippines
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-lwblue flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">Official Partnership Inquiries</p>
                                <a href="mailto:lagunawise.ph@gmail.com" class="text-lwblue hover:underline">
                                    lagunawise.ph@gmail.com
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-lwblue flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">Operational Inquiries</p>
                                <p class="text-slate-500">Monday &ndash; Friday: 9:00 AM &ndash; 5:00 PM PHT</p>
                            </div>
                        </div>
                    </div>

                    <!-- LagunaWISE Social References -->
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-navy-900 mb-3">Official LagunaWISE Channels</p>
                        <div class="flex items-center gap-3">
                            <a href="https://facebook.com/lagunawise" target="_blank" rel="noopener noreferrer" 
                               class="w-9 h-9 rounded-xl bg-surface hover:bg-lwblue hover:text-white flex items-center justify-center text-slate-600 transition">
                                <i class="fa-brands fa-facebook-f text-sm"></i>
                            </a>
                            <a href="https://instagram.com/lagunawise" target="_blank" rel="noopener noreferrer" 
                               class="w-9 h-9 rounded-xl bg-surface hover:bg-lwblue hover:text-white flex items-center justify-center text-slate-600 transition">
                                <i class="fa-brands fa-instagram text-sm"></i>
                            </a>
                            <a href="https://twitter.com/lagunawise" target="_blank" rel="noopener noreferrer" 
                               class="w-9 h-9 rounded-xl bg-surface hover:bg-lwblue hover:text-white flex items-center justify-center text-slate-600 transition">
                                <i class="fa-brands fa-x-twitter text-sm"></i>
                            </a>
                            <a href="https://linkedin.com/company/lagunawise" target="_blank" rel="noopener noreferrer" 
                               class="w-9 h-9 rounded-xl bg-surface hover:bg-lwblue hover:text-white flex items-center justify-center text-slate-600 transition">
                                <i class="fa-brands fa-linkedin-in text-sm"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Form Card -->
            <div class="lg:col-span-7">
                <div class="bg-white p-8 sm:p-10 rounded-lg border border-slate-200 shadow-sm space-y-6">
                    <div>
                        <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Direct Inquiry</span>
                        <h2 class="text-2xl font-bold text-navy-900 mt-1">Send a Message</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Fill out the form below. Our communications team responds within 2 business days.
                        </p>
                    </div>

                    <?php if ($submitted): ?>
                        <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-md text-emerald-800 space-y-2">
                            <div class="flex items-center gap-2 font-bold text-sm">
                                <i class="fa-solid fa-circle-check text-base"></i>
                                <span>Inquiry Received Successfully</span>
                            </div>
                            <p class="text-xs text-emerald-700 leading-relaxed">
                                Thank you for reaching out to the LagunaWISE Ideology Division. A confirmation has been logged, and an editorial representative will review your message shortly.
                            </p>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span><?= e($error) ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= url('pages/contact.php') ?>" method="POST" class="space-y-4">
                            <?= Security::csrfField() ?>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Your Full Name *</label>
                                    <input type="text" name="name" required placeholder="Juan Dela Cruz" 
                                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Your Email Address *</label>
                                    <input type="email" name="email" required placeholder="name@institution.edu" 
                                           class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Inquiry Nature / Subject *</label>
                                <input type="text" name="subject" required placeholder="e.g. Media Literacy Workshop Request for Senior High School" 
                                       class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Message Content *</label>
                                <textarea name="message" rows="5" required placeholder="Describe your proposed collaboration, question, or research paper inquiry..."
                                          class="w-full text-xs p-3 bg-surface border border-slate-200 rounded-xl focus:outline-none focus:border-lwblue"></textarea>
                            </div>

                            <button type="submit" class="px-7 py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center gap-2">
                                <span>Transmit Message</span>
                                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
