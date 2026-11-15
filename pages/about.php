<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$pageTitle = 'About the Ideology Division';
$pageDescription = 'Learn about the mission, intellectual charter, editorial principles, and leadership of the LagunaWISE Ideology Division.';

require_once dirname(__DIR__) . '/includes/header.php';
?>

<!-- About Hero Section -->
<section class="bg-navy-900 text-white py-16 sm:py-24 relative overflow-hidden border-b border-navy-800">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2799E8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center space-y-6">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-navy-800 border border-navy-700 text-xs font-semibold tracking-wider text-lwblue-ice uppercase">
            <span>Organizational Identity</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
            The Ideology Division of LagunaWISE
        </h1>
        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal max-w-2xl mx-auto">
            Cultivating an intellectually vigilant, media-literate, and critically engaged citizenry across Laguna and the broader digital sphere.
        </p>
    </div>
</section>

<!-- Organizational Mission & Story -->
<section class="py-16 bg-white border-b border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-12">
        
        <div class="space-y-6">
            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Our Purpose &amp; Genesis</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900">
                An Intellectual &amp; Educational Pillar
            </h2>
            <div class="editorial-prose text-base text-slate-700 leading-relaxed space-y-4">
                <p>
                    Established as an integral intellectual and educational division of <strong>LagunaWISE</strong>, based in Barangay Barandal, Calamba City, Laguna, the <strong>Ideology Division</strong> was conceived to address one of the defining crises of the contemporary era: the erosion of public deliberation caused by algorithmic polarization, rampant disinformation, and the sensationalization of complex social issues.
                </p>
                <p>
                    We operate not as a political party organ or partisan campaigning body, but as an open, scholarly, and community-centered publication platform. Our mandate is educational: to provide readers with the conceptual tools, historical contexts, and analytical frameworks required to evaluate ideas independently, interrogate established dogmas, and reach well-grounded conclusions.
                </p>
            </div>
        </div>

        <!-- 3 Core Tenets Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="p-6 bg-surface rounded-2xl border border-slate-200">
                <div class="w-10 h-10 rounded-xl bg-lwblue text-white flex items-center justify-center text-base mb-4">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Nonpartisan Integrity</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    We evaluate arguments on the strength of their evidence and reasoning, rather than partisan affiliations or political expediency.
                </p>
            </div>

            <div class="p-6 bg-surface rounded-2xl border border-slate-200">
                <div class="w-10 h-10 rounded-xl bg-lwblue-light text-white flex items-center justify-center text-base mb-4">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Educational Pedagogy</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Translating academic sociological research and digital forensics into accessible toolkits for students, teachers, and civic leaders.
                </p>
            </div>

            <div class="p-6 bg-surface rounded-2xl border border-slate-200">
                <div class="w-10 h-10 rounded-xl bg-navy-800 text-white flex items-center justify-center text-base mb-4">
                    <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <h3 class="text-base font-bold text-navy-900 mb-2">Respectful Discourse</h3>
                <p class="text-xs text-editorial-muted leading-relaxed">
                    Fostering an online culture where intellectual humility, charity toward opposing viewpoints, and civil engagement replace toxic vitriol.
                </p>
            </div>
        </div>

        <!-- Division Leadership & Fellows -->
        <div class="space-y-6 pt-8 border-t border-slate-200">
            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Division Governance</span>
            <h2 class="text-2xl font-extrabold text-navy-900">
                Editorial Board &amp; Research Fellows
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
                
                <div class="bg-surface p-5 rounded-2xl border border-slate-200 text-center space-y-3">
                    <img src="<?= asset('assets/images/avatar-editor.svg') ?>" alt="Elena Vance" class="w-16 h-16 mx-auto rounded-full object-cover shadow-sm">
                    <div>
                        <h4 class="text-sm font-bold text-navy-900">Elena Vance</h4>
                        <p class="text-xs font-semibold text-lwblue">Senior Editorial Director</p>
                        <p class="text-[11px] text-editorial-muted mt-2">Specializing in media ecosystem analysis and verification protocols.</p>
                    </div>
                </div>

                <div class="bg-surface p-5 rounded-2xl border border-slate-200 text-center space-y-3">
                    <img src="<?= asset('assets/images/avatar-writer1.svg') ?>" alt="Marcus Reyes" class="w-16 h-16 mx-auto rounded-full object-cover shadow-sm">
                    <div>
                        <h4 class="text-sm font-bold text-navy-900">Marcus Reyes</h4>
                        <p class="text-xs font-semibold text-lwblue">Senior Research Fellow</p>
                        <p class="text-[11px] text-editorial-muted mt-2">Focusing on algorithmic polarization, AI ethics, and civic technology.</p>
                    </div>
                </div>

                <div class="bg-surface p-5 rounded-2xl border border-slate-200 text-center space-y-3">
                    <img src="<?= asset('assets/images/avatar-writer2.svg') ?>" alt="Sophia Dela Cruz" class="w-16 h-16 mx-auto rounded-full object-cover shadow-sm">
                    <div>
                        <h4 class="text-sm font-bold text-navy-900">Sophia Dela Cruz</h4>
                        <p class="text-xs font-semibold text-lwblue">Digital Citizenship Specialist</p>
                        <p class="text-[11px] text-editorial-muted mt-2">Leading community outreach, youth literacy workshops, and civic education.</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Editorial Charter Section -->
        <div id="editorial-charter" class="space-y-6 pt-8 border-t border-slate-200">
            <span class="text-xs font-bold tracking-widest text-lwblue uppercase">Standards &amp; Principles</span>
            <h2 class="text-2xl font-extrabold text-navy-900">
                Editorial Charter &amp; Publication Ethics
            </h2>
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-4 text-xs text-slate-700 leading-relaxed">
                <p>
                    <strong>1. Sourcing &amp; Attribution:</strong> All articles published by the Ideology Division must attribute empirical claims to verifiable sources, academic literature, or primary documents. Anonymous accusations or unsubstantiated rumors are strictly rejected.
                </p>
                <p>
                    <strong>2. Peer Review Workflow:</strong> Every submission undergoes a multi-tier editorial review process: from initial draft creation to peer editor evaluation and editorial approval prior to publication.
                </p>
                <p>
                    <strong>3. Transparent Corrections:</strong> If an error of fact occurs in a published monograph, the editorial staff is committed to issuing a clear, dated correction note appended directly to the article.
                </p>
                <p>
                    <strong>4. Respectful Debate:</strong> Commentaries and reader contributions that engage in hate speech, personal harassment, or bad-faith misrepresentation will not be permitted on our public forums.
                </p>
            </div>
        </div>

        <!-- Privacy & Terms Notices -->
        <div id="privacy" class="space-y-4 pt-8 border-t border-slate-200">
            <h3 class="text-lg font-bold text-navy-900">Privacy &amp; Data Stewardship</h3>
            <p class="text-xs text-editorial-muted leading-relaxed">
                The LagunaWISE Ideology Division collects only the minimal personal information necessary to deliver subscriber newsletters and manage authenticated reader bookmarks. We do not sell or monetize personal reader information, nor do we deploy invasive third-party ad-tracking cookies.
            </p>
        </div>

        <div id="terms" class="space-y-4 pt-4 border-t border-slate-200">
            <h3 class="text-lg font-bold text-navy-900">Terms of Use &amp; Intellectual Property</h3>
            <p class="text-xs text-editorial-muted leading-relaxed">
                Articles published on this platform may be shared, cited, and distributed for non-commercial educational purposes, provided that attribution to LagunaWISE Ideology Division and the original contributing author is explicitly maintained.
            </p>
        </div>

    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
