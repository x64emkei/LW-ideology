<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'About the Ideology Division';
$pageDescription = 'Learn about the intellectual mission, division purpose, editorial charter, and nonpartisan governance of the LagunaWISE Ideology Division.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- About Hero Section (Solid Dark Navy Background with High Contrast) -->
<section class="bg-navy-900 text-white py-14 sm:py-20 relative overflow-hidden border-b border-navy-800" style="background-color: #061638;">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-5 animate-fade-in">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-navy-800 border border-navy-700 text-xs font-semibold tracking-wider text-lwblue-ice uppercase">
            <span class="w-2 h-2 rounded-full bg-lwblue-light animate-ping"></span>
            <span>Organizational Identity &amp; Advocacy</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight font-heading">
            The Ideology Division of LagunaWISE
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-normal max-w-3xl mx-auto">
            Cultivating an intellectually vigilant, media-literate, and critically engaged citizenry across Laguna and the broader digital sphere.
        </p>
    </div>
</section>

<!-- Dedicated In-Page Section Jump Navigation -->
<nav class="bg-white border-b border-slate-200 shadow-2xs sticky top-12 sm:top-13 z-30" aria-label="About Page Section Navigation">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between gap-4 py-2.5 overflow-x-auto text-xs font-bold text-slate-700 uppercase tracking-wider">
            <div class="flex items-center gap-1 sm:gap-2">
                <a href="#purpose" class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-navy-900 transition whitespace-nowrap">
                    Purpose &amp; Genesis
                </a>
                <a href="#pillars" class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-navy-900 transition whitespace-nowrap">
                    Inquiry Pillars
                </a>
                <a href="#governance" class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-navy-900 transition whitespace-nowrap">
                    Governance
                </a>
                <a href="#editorial-charter" class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-navy-900 transition whitespace-nowrap">
                    Editorial Charter
                </a>
                <a href="#technology" class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-navy-900 transition whitespace-nowrap">
                    Tech &amp; AI
                </a>
                <a href="#terms" class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-navy-900 transition whitespace-nowrap">
                    Ethics &amp; Terms
                </a>
            </div>
            <a href="<?= url('auth/register.php') ?>" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-lwblue hover:bg-lwblue-hover text-white rounded-md transition shadow-xs whitespace-nowrap">
                <i class="fa-solid fa-pen-nib text-[10px]"></i>
                <span>Join Contributors</span>
            </a>
        </div>
    </div>
</nav>

<!-- Main About Content with Two-Column Desktop Distribution (Cardless on Mobile / 66% Main & 34% Sidebar on Desktop) -->
<section class="py-6 sm:py-14 bg-white lg:bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Main Content Column (Cardless on Mobile, Elevated Container on Desktop) -->
            <div class="lg:col-span-8 bg-white p-0 sm:p-10 rounded-none lg:rounded-lg border-0 lg:border lg:border-slate-200 shadow-none lg:shadow-xs space-y-8 sm:space-y-10">
                
                <!-- Purpose & Genesis -->
                <div id="purpose" class="space-y-4 scroll-mt-28">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Our Purpose &amp; Genesis</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight font-heading">
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
                <div id="pillars" class="space-y-4 scroll-mt-28">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Our Core Tenets</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pt-2">
                        <div class="p-5 bg-surface rounded-lg border border-slate-200 space-y-2">
                            <div class="w-10 h-10 rounded-md bg-lwblue text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </div>
                            <h3 class="text-sm font-bold text-navy-900 font-heading">Nonpartisan Integrity</h3>
                            <p class="text-xs text-editorial-muted leading-relaxed">
                                Evaluating arguments on empirical evidence and logic rather than partisan loyalty.
                            </p>
                        </div>

                        <div class="p-5 bg-surface rounded-lg border border-slate-200 space-y-2">
                            <div class="w-10 h-10 rounded-md bg-lwblue-light text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <h3 class="text-sm font-bold text-navy-900 font-heading">Educational Pedagogy</h3>
                            <p class="text-xs text-editorial-muted leading-relaxed">
                                Translating media literacy and digital forensics into accessible tools for all learners.
                            </p>
                        </div>

                        <div class="p-5 bg-surface rounded-lg border border-slate-200 space-y-2">
                            <div class="w-10 h-10 rounded-md bg-navy-800 text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <h3 class="text-sm font-bold text-navy-900 font-heading">Respectful Dialogue</h3>
                            <p class="text-xs text-editorial-muted leading-relaxed">
                                Upholding intellectual charity, civil inquiry, and mutual respect in public debate.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Division Governance Structure -->
                <div id="governance" class="pt-8 border-t border-slate-200 space-y-6 scroll-mt-28">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Governance &amp; Operations</span>
                    <h2 class="text-2xl font-extrabold text-navy-900 font-heading">
                        Editorial Governance Structure
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="p-0 pb-4 sm:p-5 bg-transparent sm:bg-surface rounded-none sm:rounded-lg border-0 sm:border border-b sm:border-b border-slate-200 space-y-2">
                            <h4 class="text-sm font-bold text-navy-900 flex items-center gap-2 font-heading">
                                <i class="fa-solid fa-users text-lwblue"></i>
                                <span>Editorial Board &amp; Review Council</span>
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Authorized editors oversee the peer review queue, evaluate submitted drafts, provide revision feedback, and uphold attribution standards before approving monographs for public release.
                            </p>
                        </div>

                        <div class="p-0 pb-4 sm:p-5 bg-transparent sm:bg-surface rounded-none sm:rounded-lg border-0 sm:border border-b sm:border-b-0 border-slate-200 space-y-2">
                            <h4 class="text-sm font-bold text-navy-900 flex items-center gap-2 font-heading">
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
                <div id="editorial-charter" class="pt-8 border-t border-slate-200 space-y-6 scroll-mt-28">
                    <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Standards &amp; Principles</span>
                    <h2 class="text-2xl font-extrabold text-navy-900 font-heading">
                        Editorial Charter &amp; Publication Ethics
                    </h2>
                    
                    <div class="bg-transparent sm:bg-surface p-0 sm:p-6 rounded-none sm:rounded-lg border-0 sm:border border-slate-200 space-y-4 text-xs text-slate-700 leading-relaxed">
                        <div class="space-y-1">
                            <h4 class="font-bold text-navy-900 text-sm font-heading">1. Sourcing &amp; Verifiable Attribution</h4>
                            <p class="text-slate-600">All published monographs must attribute empirical claims to verifiable sources, scholarly literature, or primary archives. Anonymous assertions and ungrounded rumors are systematically rejected.</p>
                        </div>
                        <div class="space-y-1 pt-2 border-t border-slate-200/80">
                            <h4 class="font-bold text-navy-900 text-sm font-heading">2. Multi-tier Peer Review Workflow</h4>
                            <p class="text-slate-600">Every contributor manuscript undergoes structured editorial evaluation: from initial draft creation to peer review, revision requests with actionable feedback, and final publishing approval.</p>
                        </div>
                        <div class="space-y-1 pt-2 border-t border-slate-200/80">
                            <h4 class="font-bold text-navy-900 text-sm font-heading">3. Transparent Corrections &amp; Revisions</h4>
                            <p class="text-slate-600">If an error of fact occurs in a published article, our editorial team promptly issues a dated, transparent correction notice appended directly to the permanent publication record.</p>
                        </div>
                        <div class="space-y-1 pt-2 border-t border-slate-200/80">
                            <h4 class="font-bold text-navy-900 text-sm font-heading">4. Civil Public Engagement</h4>
                            <p class="text-slate-600">Commentaries and community discussions that engage in ad hominem harassment, defamation, or bad-faith misrepresentation will not be permitted on our public forums.</p>
                        </div>
                    </div>
                </div>

                <!-- Technology Stack & AI Integration Section -->
                <div id="technology" class="pt-8 border-t border-slate-200 space-y-6 scroll-mt-28">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Platform Engineering &amp; Innovation</span>
                            <h2 class="text-2xl font-extrabold text-navy-900 font-heading mt-1">
                                Technology Stack &amp; AI-Assisted Development
                            </h2>
                        </div>
                        <a href="<?= url('TECH_STACK.md') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-lwblue hover:text-lwblue-hover hover:underline" target="_blank">
                            <span>Technical Specification</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        <!-- Tech Stack Pillar -->
                        <div class="p-0 pb-4 sm:p-5 bg-transparent sm:bg-surface rounded-none sm:rounded-lg border-0 sm:border border-b sm:border-b-0 border-slate-200 space-y-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-md bg-lwblue text-white flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-layer-group"></i>
                                </span>
                                <h3 class="text-sm font-bold text-navy-900 font-heading">Core Technology Stack</h3>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Engineered on a modern, robust, and framework-free open-source foundation prioritizing speed, data integrity, and longevity:
                            </p>
                            <ul class="text-xs text-slate-700 space-y-2 pt-1">
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-emerald-600 text-[11px] mt-0.5"></i>
                                    <span><strong>PHP 8.2+ (Strict Types)</strong>: High-performance backend runtime enforcing type safety, CSRF tokens, and secure session state.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-emerald-600 text-[11px] mt-0.5"></i>
                                    <span><strong>MariaDB 10.4+ / MySQL</strong>: 14 normalized relational InnoDB tables with foreign key constraints and zero fake data.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-emerald-600 text-[11px] mt-0.5"></i>
                                    <span><strong>Tailwind CSS 3.4</strong>: Mobile-first utility styling utilizing custom LagunaWISE editorial palette tokens.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-emerald-600 text-[11px] mt-0.5"></i>
                                    <span><strong>Vanilla ES6+ JavaScript</strong>: Zero bloated runtime dependencies, ensuring instant interactive performance and accessibility.</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Role of AI Pillar -->
                        <div class="p-0 pb-4 sm:p-5 bg-transparent sm:bg-surface rounded-none sm:rounded-lg border-0 sm:border border-b sm:border-b-0 border-slate-200 space-y-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-md bg-navy-900 text-white flex items-center justify-center text-xs" style="background-color: #061638;">
                                    <i class="fa-solid fa-microchip"></i>
                                </span>
                                <h3 class="text-sm font-bold text-navy-900 font-heading">How AI Became Vital</h3>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Artificial Intelligence functioned as a critical agentic engineering and quality assurance accelerator throughout platform development:
                            </p>
                            <ul class="text-xs text-slate-700 space-y-2 pt-1">
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-sparkles text-lwblue text-[11px] mt-0.5"></i>
                                    <span><strong>Agentic Code Synthesis</strong>: Accelerated implementation of defensive security patterns, parameterized PDO queries, and multi-column grids.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-sparkles text-lwblue text-[11px] mt-0.5"></i>
                                    <span><strong>Autonomous Test Automation</strong>: Generated and executed 32 live end-to-end acceptance assertions simulating multi-role editorial pipelines.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-sparkles text-lwblue text-[11px] mt-0.5"></i>
                                    <span><strong>A11y &amp; Responsive Verification</strong>: Automated WCAG 2.2 color contrast audits and headless multi-device rendering across 9 viewports.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-sparkles text-lwblue text-[11px] mt-0.5"></i>
                                    <span><strong>Human-in-the-Loop Governance</strong>: Ensuring the human editorial board retains exclusive authority over published scholarship and civic opinions.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Legal Disclosures -->
                <div id="terms" class="pt-8 border-t border-slate-200 space-y-4 text-xs text-slate-500 scroll-mt-28">
                    <h3 class="font-bold text-navy-900 text-sm font-heading">Terms of Use &amp; Intellectual Property</h3>
                    <p class="leading-relaxed">
                        Content published on this platform is licensed for non-commercial educational use under academic fair attribution standards. Contributing authors retain moral attribution over their monographs while granting LagunaWISE publishing rights.
                    </p>
                </div>

                <div id="privacy" class="pt-4 border-t border-slate-100 space-y-4 text-xs text-slate-500 scroll-mt-28">
                    <h3 class="font-bold text-navy-900 text-sm font-heading">Privacy &amp; Data Ethics</h3>
                    <p class="leading-relaxed">
                        We collect only essential account information required for authentication and editorial communication. We do not sell user data, utilize commercial ad trackers, or deploy invasive surveillance telemetry.
                    </p>
                </div>

            </div>

            <!-- Secondary Organization Sidebar (Desktop ~34% / 4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Organization Branding Card -->
                <div class="bg-navy-900 text-white p-8 rounded-lg border border-navy-800 shadow-xl space-y-5 text-center relative overflow-hidden" style="background-color: #061638;">
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

                    <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" 
                         alt="LagunaWISE Emblem" 
                         width="72"
                         height="72"
                         style="width: 72px; height: 72px; min-width: 72px; max-width: 72px;"
                         class="w-18 h-18 mx-auto object-contain rounded-full shadow-lg relative z-10 bg-white/10 p-1 border border-white/20">

                    <div class="relative z-10 space-y-1">
                        <span class="text-xs font-bold tracking-widest text-lwblue-light uppercase">Official Entity</span>
                        <h3 class="text-lg font-extrabold text-white font-heading">LagunaWISE</h3>
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
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-white text-navy-900 shadow-xs border border-navy-700">
                            <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" alt="LagunaWISE Emblem" class="w-5 h-5 object-contain rounded-full">
                            <span class="font-extrabold text-xs tracking-tight">Laguna<span class="text-lwblue">WISE</span></span>
                            <span class="text-[10px] text-slate-500 border-l border-slate-200 pl-2">Ideology Division</span>
                        </div>
                    </div>
                </div>

                <!-- Advocacy Callout Card -->
                <div class="bg-transparent lg:bg-white p-0 pb-6 lg:p-6 rounded-none lg:rounded-lg border-0 lg:border border-b lg:border-b-0 border-slate-200 shadow-none lg:shadow-xs space-y-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-lwblue">Call for Writers</span>
                    <h3 class="text-base font-bold text-navy-900 font-heading">Become a Contributor</h3>
                    <p class="text-xs text-editorial-muted leading-relaxed">
                        Are you a student, educator, or researcher passionate about media literacy and civic discourse? Register for an account to draft and submit articles.
                    </p>
                    <a href="<?= url('auth/register.php') ?>" 
                       class="w-full py-2.5 bg-lwblue hover:bg-lwblue-hover text-white text-xs font-bold rounded-md transition shadow-xs flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen-nib text-xs"></i>
                        <span>Register as Contributor</span>
                    </a>
                </div>

                <!-- Division Motto Card -->
                <div class="bg-transparent lg:bg-white p-0 pb-6 lg:p-6 rounded-none lg:rounded-lg border-0 lg:border border-b lg:border-b-0 border-slate-200 shadow-none lg:shadow-xs space-y-3">
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
