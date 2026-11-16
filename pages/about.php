<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'About the Ideology Division';
$pageDescription = 'Learn about the intellectual mission, division purpose, editorial charter, and nonpartisan governance of the LagunaWISE Ideology Division.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- About Hero Section -->
<section class="bg-navy-900 text-white py-16 sm:py-24 relative overflow-hidden border-b border-navy-800">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-6">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-navy-800 border border-navy-700 text-xs font-semibold tracking-wider text-lwblue-ice uppercase">
            <span>Organizational Identity &amp; Advocacy</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight font-heading">
            The Ideology Division of LagunaWISE
        </h1>
        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal max-w-3xl mx-auto">
            Cultivating an intellectually vigilant, media-literate, and critically engaged citizenry across Laguna and the broader digital sphere.
        </p>
    </div>
</section>

<!-- Main About Content with Two-Column Desktop Distribution (66% Main / 34% Sidebar) -->
<section class="py-16 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Main Content Column (Desktop ~66% / 8 cols) -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-10 rounded-3xl border border-slate-200/90 shadow-sm space-y-10">
                
                <!-- Purpose & Genesis -->
                <div class="space-y-4">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Our Purpose &amp; Genesis</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">
                        An Intellectual &amp; Educational Pillar
                    </h2>
                    <div class="editorial-prose text-base text-slate-700 leading-relaxed space-y-4">
                        <p>
                            Established as an integral intellectual and educational division of <strong>LagunaWISE</strong> in Calamba City, Laguna, the <strong>Ideology Division</strong> was conceived to address one of the defining crises of the contemporary era: the erosion of public deliberation caused by algorithmic polarization, viral disinformation, and sensationalized public discourse.
                        </p>
                        <p>
                            We operate not as a political party organ or partisan campaigning body, but as an open, scholarly, and community-centered editorial publication platform. Our mandate is educational: to provide citizens, educators, and student researchers with the conceptual tools, historical contexts, and analytical frameworks required to evaluate ideas independently, interrogate established dogmas, and reach well-grounded conclusions.
                        </p>
                    </div>
                </div>

                <!-- 3 Pillars Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-2">
                    <div class="p-5 bg-surface rounded-2xl border border-slate-200 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-lwblue text-white flex items-center justify-center text-sm">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <h3 class="text-sm font-bold text-navy-900">Nonpartisan Integrity</h3>
                        <p class="text-xs text-editorial-muted leading-relaxed">
                            Evaluating arguments on empirical evidence and logic rather than partisan loyalty.
                        </p>
                    </div>

                    <div class="p-5 bg-surface rounded-2xl border border-slate-200 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-lwblue-light text-white flex items-center justify-center text-sm">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <h3 class="text-sm font-bold text-navy-900">Educational Pedagogy</h3>
                        <p class="text-xs text-editorial-muted leading-relaxed">
                            Translating media literacy and digital forensics into accessible tools for all learners.
                        </p>
                    </div>

                    <div class="p-5 bg-surface rounded-2xl border border-slate-200 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-navy-800 text-white flex items-center justify-center text-sm">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <h3 class="text-sm font-bold text-navy-900">Respectful Dialogue</h3>
                        <p class="text-xs text-editorial-muted leading-relaxed">
                            Upholding intellectual charity, civil inquiry, and mutual respect in public debate.
                        </p>
                    </div>
                </div>

                <!-- Division Governance Structure -->
                <div class="pt-8 border-t border-slate-200 space-y-6">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Governance &amp; Operations</span>
                    <h2 class="text-2xl font-extrabold text-navy-900">
                        Editorial Governance Structure
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="p-5 bg-surface rounded-2xl border border-slate-200 space-y-2">
                            <h4 class="text-sm font-bold text-navy-900 flex items-center gap-2">
                                <i class="fa-solid fa-users text-lwblue"></i>
                                <span>Editorial Board &amp; Review Council</span>
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Authorized editors oversee the peer review queue, evaluate submitted drafts, provide revision feedback, and uphold attribution standards before approving monographs for public release.
                            </p>
                        </div>

                        <div class="p-5 bg-surface rounded-2xl border border-slate-200 space-y-2">
                            <h4 class="text-sm font-bold text-navy-900 flex items-center gap-2">
                                <i class="fa-solid fa-feather text-lwblue"></i>
                                <span>Contributing Inquirers &amp; Fellows</span>
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Any registered contributor can draft and submit original analytical monographs, investigation reports, and civic toolkits for evaluation through our transparent editorial workflow.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Editorial Charter Section -->
                <div id="editorial-charter" class="pt-8 border-t border-slate-200 space-y-6">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Standards &amp; Principles</span>
                    <h2 class="text-2xl font-extrabold text-navy-900">
                        Editorial Charter &amp; Publication Ethics
                    </h2>
                    
                    <div class="bg-surface p-6 rounded-2xl border border-slate-200 space-y-4 text-xs text-slate-700 leading-relaxed">
                        <div class="space-y-1">
                            <h4 class="font-bold text-navy-900 text-sm">1. Sourcing &amp; Verifiable Attribution</h4>
                            <p class="text-slate-600">All published monographs must attribute empirical claims to verifiable sources, scholarly literature, or primary archives. Anonymous assertions and ungrounded rumors are systematically rejected.</p>
                        </div>
                        <div class="space-y-1 pt-2 border-t border-slate-200/80">
                            <h4 class="font-bold text-navy-900 text-sm">2. Multi-tier Peer Review Workflow</h4>
                            <p class="text-slate-600">Every contributor manuscript undergoes structured editorial evaluation: from initial draft creation to peer review, revision requests with actionable feedback, and final publishing approval.</p>
                        </div>
                        <div class="space-y-1 pt-2 border-t border-slate-200/80">
                            <h4 class="font-bold text-navy-900 text-sm">3. Transparent Corrections &amp; Revisions</h4>
                            <p class="text-slate-600">If an error of fact occurs in a published article, our editorial team promptly issues a dated, transparent correction notice appended directly to the permanent publication record.</p>
                        </div>
                        <div class="space-y-1 pt-2 border-t border-slate-200/80">
                            <h4 class="font-bold text-navy-900 text-sm">4. Civil Public Engagement</h4>
                            <p class="text-slate-600">Commentaries and community discussions that engage in ad hominem harassment, defamation, or bad-faith misrepresentation will not be permitted on our public forums.</p>
                        </div>
                    </div>
                </div>

                <!-- Legal Disclosures -->
                <div id="terms" class="pt-8 border-t border-slate-200 space-y-4 text-xs text-slate-500">
                    <h3 class="font-bold text-navy-900 text-sm">Terms of Use &amp; Intellectual Property</h3>
                    <p class="leading-relaxed">
                        Content published on this platform is licensed for non-commercial educational use under academic fair attribution standards. Contributing authors retain moral attribution over their monographs while granting LagunaWISE publishing rights.
                    </p>
                </div>

                <div id="privacy" class="pt-4 border-t border-slate-100 space-y-4 text-xs text-slate-500">
                    <h3 class="font-bold text-navy-900 text-sm">Privacy &amp; Data Ethics</h3>
                    <p class="leading-relaxed">
                        We collect only essential account information required for authentication and editorial communication. We do not sell user data, utilize commercial ad trackers, or deploy invasive surveillance telemetry.
                    </p>
                </div>

            </div>

            <!-- Secondary Organization Sidebar (Desktop ~34% / 4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Organization Branding Card -->
                <div class="bg-navy-900 text-white p-8 rounded-3xl border border-navy-800 shadow-xl space-y-5 text-center relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

                    <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" 
                         alt="LagunaWISE Emblem" 
                         class="w-20 h-20 mx-auto object-contain rounded-full shadow-lg relative z-10">

                    <div class="relative z-10 space-y-1">
                        <span class="text-xs font-bold tracking-widest text-lwblue-light uppercase">Official Entity</span>
                        <h3 class="text-lg font-extrabold text-white">LagunaWISE</h3>
                        <p class="text-xs text-slate-300">Ideology Division</p>
                    </div>

                    <div class="relative z-10 pt-4 border-t border-navy-800 text-xs text-slate-300 space-y-2 text-left">
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-lwblue-light w-4 shrink-0"></i>
                            <span><?= e(get_setting('organization_location', 'Barangay Barandal, Calamba City, Laguna, Philippines')) ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-lwblue-light w-4 shrink-0"></i>
                            <a href="mailto:<?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>" class="hover:text-white transition">
                                <?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>
                            </a>
                        </p>
                    </div>

                    <div class="relative z-10 pt-2">
                        <div class="bg-white/95 rounded-xl p-2.5 inline-block shadow-sm">
                            <img src="<?= asset('assets/images/lagunawise-wordmark.png') ?>" alt="LagunaWISE Wordmark" class="h-8 object-contain">
                        </div>
                    </div>
                </div>

                <!-- Advocacy Callout Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-lwblue">Call for Writers</span>
                    <h3 class="text-base font-bold text-navy-900">Become a Contributor</h3>
                    <p class="text-xs text-editorial-muted leading-relaxed">
                        Are you a student, educator, or researcher passionate about media literacy and civic discourse? Register for an account to draft and submit articles.
                    </p>
                    <a href="<?= url('auth/register.php') ?>" 
                       class="w-full py-3 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen-nib text-xs"></i>
                        <span>Register as Contributor</span>
                    </a>
                </div>

                <!-- Division Motto Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold text-navy-900 uppercase tracking-wider">Division Motto</h4>
                    <blockquote class="text-sm font-bold text-navy-900 border-l-2 border-lwblue pl-3 py-1 font-heading">
                        “Understanding Ideas. Challenging Perspectives. Empowering Minds.”
                    </blockquote>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        A commitment to reasoned skepticism, intellectual humility, and responsible civic agency.
                    </p>
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
