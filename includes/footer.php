    </main>

    <!-- GLOBAL PROTOTYPE ADVISORY BANNER (VISIBLE ACROSS ALL PAGES) -->
    <aside class="bg-amber-400 text-navy-950 border-t-2 border-b-2 border-amber-500 py-3.5 px-4 shadow-sm w-full max-w-full overflow-hidden" aria-label="Prototype Notice">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-navy-950 text-amber-300 font-extrabold uppercase text-[10px] tracking-wider shrink-0 shadow-xs">
                    <i class="fa-solid fa-flask-vial text-amber-400"></i> PROTOTYPE ONLY
                </span>
                <p class="font-bold text-navy-950 text-xs sm:text-sm">
                    ACADEMIC DEMONSTRATION &bull; RESEARCH PROTOTYPE SYSTEM
                </p>
            </div>
            <p class="text-[11px] sm:text-xs text-navy-900 font-medium text-center sm:text-right">
                This platform is an educational prototype developed for academic and research evaluation. Content is for illustrative study.
            </p>
        </div>
    </aside>

    <!-- Master News-Portal Footer (Solid Dark Navy with Verified WCAG AAA Contrast) -->
    <footer class="bg-navy-950 text-slate-300 border-t border-navy-800 pt-16 pb-12" style="background-color: #030D22;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            
            <!-- Top Footer Bar: Brand, Official Emblem, Wordmark Badge, and Headquarters Details -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pb-10 border-b border-navy-800 items-start">
                
                <div class="lg:col-span-6 space-y-4">
                    <div class="flex items-center gap-3.5">
                        <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" 
                             alt="LagunaWISE Emblem" 
                             width="48" 
                             height="48" 
                             style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; max-width: 48px; max-height: 48px;" 
                             class="w-12 h-12 shrink-0 object-contain rounded-full shadow-md border border-navy-700 bg-navy-900">
                        <div>
                            <span class="block text-xs font-black tracking-widest text-lwblue-light uppercase">LagunaWISE</span>
                            <span class="text-xl sm:text-2xl font-black text-white tracking-tight font-heading">IDEOLOGY DIVISION</span>
                        </div>
                    </div>
                    
                    <p class="text-xs text-slate-300 leading-relaxed max-w-lg">
                        <?= e(get_setting('division_statement', 'An intellectual and educational division of LagunaWISE dedicated to fostering critical thinking, media literacy, informed discussions, and responsible digital citizenship.')) ?>
                    </p>

                    <!-- Official Wordmark Branding Badge (Crisp & High Contrast) -->
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <div class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded bg-navy-900 border border-navy-700 text-white shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-lwblue-light"></span>
                            <span class="font-extrabold text-xs tracking-tight text-white">Laguna<span class="text-lwblue-light">WISE</span></span>
                            <span class="text-[10px] text-slate-400 border-l border-navy-700 pl-2">Digital Publication</span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">Civic &amp; Media Literacy Portal</span>
                    </div>
                </div>

                <div class="lg:col-span-6 flex flex-col sm:flex-row sm:items-start justify-between lg:justify-end gap-6 text-xs text-slate-300">
                    <div class="space-y-2">
                        <span class="text-[11px] font-black uppercase tracking-wider text-white block">Headquarters &amp; Secretariat</span>
                        <p class="flex items-start gap-2.5 max-w-xs text-slate-300">
                            <i class="fa-solid fa-location-dot text-lwblue-light mt-0.5 shrink-0"></i>
                            <span><?= e(get_setting('organization_location', 'Barangay Barandal, Calamba City, Laguna 4027, Philippines')) ?></span>
                        </p>
                        <p class="flex items-center gap-2.5 text-slate-300">
                            <i class="fa-solid fa-envelope text-lwblue-light shrink-0"></i>
                            <a href="mailto:<?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>" class="text-slate-200 hover:text-white transition">
                                <?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>
                            </a>
                        </p>
                    </div>

                    <div class="space-y-2">
                        <span class="text-[11px] font-black uppercase tracking-wider text-white block">Connect &amp; Follow</span>
                        <div class="flex items-center gap-2 text-sm">
                            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" 
                               aria-label="Follow LagunaWISE on Facebook"
                               class="w-8 h-8 rounded bg-navy-850 border border-navy-700 flex items-center justify-center text-white hover:bg-lwblue transition">
                                <i class="fa-brands fa-facebook-f text-xs"></i>
                            </a>
                            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" 
                               aria-label="Follow LagunaWISE on X"
                               class="w-8 h-8 rounded bg-navy-850 border border-navy-700 flex items-center justify-center text-white hover:bg-lwblue transition">
                                <i class="fa-brands fa-x-twitter text-xs"></i>
                            </a>
                            <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" 
                               aria-label="Follow LagunaWISE on LinkedIn"
                               class="w-8 h-8 rounded bg-navy-850 border border-navy-700 flex items-center justify-center text-white hover:bg-lwblue transition">
                                <i class="fa-brands fa-linkedin-in text-xs"></i>
                            </a>
                            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" 
                               aria-label="Subscribe to LagunaWISE on YouTube"
                               class="w-8 h-8 rounded bg-navy-850 border border-navy-700 flex items-center justify-center text-white hover:bg-red-600 transition">
                                <i class="fa-brands fa-youtube text-xs"></i>
                            </a>
                        </div>
                        <span class="text-[10px] text-slate-400 block font-mono">@lagunawise • Nonpartisan</span>
                    </div>
                </div>

            </div>

            <!-- Multi-Column Direct Links Grid (High Contrast WCAG AAA Typography) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 py-10 border-b border-navy-800 text-xs">
                
                <!-- Col 1: Platform & Editorial Sections -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-lwblue-light"></span>
                        <span>Platform</span>
                    </h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="<?= url() ?>" class="hover:text-white transition">Home Page</a></li>
                        <li><a href="<?= url('pages/about.php') ?>" class="hover:text-white transition">About the Division</a></li>
                        <li><a href="<?= url('pages/articles.php') ?>" class="hover:text-white transition">Articles &amp; Research</a></li>
                        <li><a href="<?= url('pages/topics.php') ?>" class="hover:text-white transition">Inquiry Desks</a></li>
                        <li><a href="<?= url('pages/news.php') ?>" class="hover:text-white transition">News &amp; Campaigns</a></li>
                        <li><a href="<?= url('pages/contact.php') ?>" class="hover:text-white transition">Contact &amp; Inquiries</a></li>
                        <li><a href="<?= url('pages/sitemap.php') ?>" class="hover:text-white transition">Site Directory &amp; Map</a></li>
                    </ul>
                </div>

                <!-- Col 2: Core Inquiry Desks (Direct Topics) -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        <span>Inquiry Desks</span>
                    </h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="<?= url('pages/topic.php?slug=media-literacy') ?>" class="hover:text-white transition">Media &amp; Information Literacy</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=critical-thinking') ?>" class="hover:text-white transition">Critical Thinking &amp; Fallacies</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=digital-citizenship') ?>" class="hover:text-white transition">Digital Citizenship &amp; Ethics</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=informed-discourse') ?>" class="hover:text-white transition">Informed Public Discourse</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=youth-civic-engagement') ?>" class="hover:text-white transition">Youth Civic Engagement</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=ethical-technology') ?>" class="hover:text-white transition">Ethical Tech &amp; AI</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=community-research') ?>" class="hover:text-white transition">Community Research</a></li>
                    </ul>
                </div>

                <!-- Col 3: Editorial Standards & Governance -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                        <span>Governance</span>
                    </h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="<?= url('pages/about.php#editorial-charter') ?>" class="hover:text-white transition">Editorial Charter</a></li>
                        <li><a href="<?= url('pages/about.php#governance') ?>" class="hover:text-white transition">Institutional Governance</a></li>
                        <li><a href="<?= url('pages/about.php#ethics') ?>" class="hover:text-white transition">Code of Journalistic Ethics</a></li>
                        <li><a href="<?= url('pages/about.php#peer-review') ?>" class="hover:text-white transition">Peer-Review Framework</a></li>
                        <li><a href="<?= url('pages/about.php#corrections') ?>" class="hover:text-white transition">Fact-Checking &amp; Corrections</a></li>
                        <li><a href="<?= url('pages/about.php#citations') ?>" class="hover:text-white transition">Academic Citations Standard</a></li>
                        <li><a href="<?= url('tests/index.php') ?>" class="hover:text-white transition flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>System QA Test Suite</span>
                        </a></li>
                    </ul>
                </div>

                <!-- Col 4: Contributor & Writing Pipeline -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        <span>Contributors</span>
                    </h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="<?= url('auth/register.php') ?>" class="text-lwblue-light font-bold hover:underline">Register as Contributor</a></li>
                        <li><a href="<?= url('auth/login.php') ?>" class="hover:text-white transition">Sign In to Dashboard</a></li>
                        <li><a href="<?= url('pages/dashboard.php') ?>" class="hover:text-white transition">Contributor Dashboard</a></li>
                        <li><a href="<?= url('admin/article-create.php') ?>" class="hover:text-white transition">Draft New Manuscript</a></li>
                        <li><a href="<?= url('admin/submissions.php') ?>" class="hover:text-white transition">Editorial Review Queue</a></li>
                        <li><a href="<?= url('admin/index.php') ?>" class="hover:text-white transition">Administrative Portal</a></li>
                        <li><a href="<?= url('pages/contact.php') ?>" class="hover:text-white transition">Partnerships &amp; Symposia</a></li>
                    </ul>
                </div>

                <!-- Col 5: Fortnightly Dispatch Newsletter Box -->
                <div class="col-span-2 sm:col-span-1 space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                        <span>Dispatch</span>
                    </h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Curated research briefs, verification fieldbooks, and civic dispatches twice a month.
                    </p>
                    <form action="<?= url('actions/newsletter-action.php') ?>" method="POST" class="space-y-2" id="footerNewsletterForm">
                        <?= csrf_field() ?>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   required 
                                   placeholder="name@example.com" 
                                   class="w-full text-xs px-3 py-2 bg-navy-900 border border-navy-700 rounded text-white placeholder-slate-400 focus:outline-hidden focus:border-lwblue-light">
                        </div>
                        <button type="submit" class="w-full text-xs font-bold py-2 bg-lwblue hover:bg-lwblue-hover text-white rounded transition duration-150 flex items-center justify-center gap-2 shadow">
                            <span>Subscribe to Dispatch</span>
                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        </button>
                    </form>
                    <p class="text-[10px] text-slate-400">Zero spam. Nonpartisan, educational dispatches only.</p>
                </div>

            </div>

            <!-- Bottom Legal & Compliance Strip -->
            <div class="pt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-400">
                <div class="text-center md:text-left">
                    <p><?= e(get_setting('footer_copyright', '© 2026 LagunaWISE Ideology Division. All rights reserved. Intellectual and educational nonpartisan publication.')) ?></p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-5 text-[11px]">
                    <a href="<?= url('pages/about.php#editorial-charter') ?>" class="hover:text-slate-200 transition">Editorial Charter</a>
                    <span>•</span>
                    <a href="<?= url('pages/about.php#privacy') ?>" class="hover:text-slate-200 transition">Privacy Policy</a>
                    <span>•</span>
                    <a href="<?= url('pages/about.php#terms') ?>" class="hover:text-slate-200 transition">Terms of Service</a>
                    <span>•</span>
                    <a href="<?= url('pages/sitemap.php') ?>" class="hover:text-slate-200 transition">Sitemap</a>
                    <span>•</span>
                    <a href="<?= url('admin/index.php') ?>" class="text-slate-300 hover:text-white transition">Staff CMS</a>
                </div>
            </div>

            <!-- Prominent Global Advisory Notice: Prototype Only -->
            <div class="mt-6 pt-4 border-t border-navy-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-amber-300/90 font-mono">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span class="font-bold tracking-wide">NOTICE: THIS ENTIRE WEBSITE IS A PROTOTYPE ONLY</span>
                </div>
                <p class="text-[10px] text-slate-400 text-center sm:text-right font-sans">
                    Constructed for academic research and educational demonstration &bull; Not for official commercial or legal reliance.
                </p>
            </div>

        </div>
    </footer>

    <!-- Interactive Vanilla JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page Loading Animation Bar
            const topBar = document.getElementById('top-loading-bar');
            if (topBar) {
                topBar.style.transform = 'scaleX(1)';
                setTimeout(() => {
                    topBar.style.opacity = '0';
                    setTimeout(() => {
                        topBar.style.display = 'none';
                    }, 300);
                }, 250);
            }

            // Mobile Menu Toggle (supports both header toggle and sticky navbar toggle)
            const mobileMenuToggles = document.querySelectorAll('#mobileMenuToggle, .mobileMenuToggleBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuIcons = document.querySelectorAll('#mobileMenuIcon, .mobileMenuIcon');

            if (mobileMenuToggles.length > 0 && mobileMenu) {
                mobileMenuToggles.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const isExpanded = btn.getAttribute('aria-expanded') === 'true';
                        const nextState = !isExpanded;
                        
                        mobileMenuToggles.forEach(b => b.setAttribute('aria-expanded', nextState ? 'true' : 'false'));
                        mobileMenu.classList.toggle('hidden', !nextState);
                        mobileMenuIcons.forEach(icon => {
                            icon.classList.toggle('fa-bars', !nextState);
                            icon.classList.toggle('fa-xmark', nextState);
                        });
                    });
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!mobileMenu.classList.contains('hidden') && !mobileMenu.contains(e.target)) {
                        let clickedToggle = false;
                        mobileMenuToggles.forEach(b => {
                            if (b.contains(e.target)) clickedToggle = true;
                        });
                        if (!clickedToggle) {
                            mobileMenu.classList.add('hidden');
                            mobileMenuToggles.forEach(b => b.setAttribute('aria-expanded', 'false'));
                            mobileMenuIcons.forEach(icon => {
                                icon.classList.add('fa-bars');
                                icon.classList.remove('fa-xmark');
                            });
                        }
                    }
                });
            }

            // User Dropdown Menu Toggle
            const userMenuBtn = document.getElementById('userMenuButton');
            const userMenuDropdown = document.getElementById('userMenuDropdown');

            if (userMenuBtn && userMenuDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isHidden = userMenuDropdown.classList.contains('hidden');
                    userMenuDropdown.classList.toggle('hidden');
                    userMenuBtn.setAttribute('aria-expanded', isHidden);
                });

                document.addEventListener('click', function(e) {
                    if (!userMenuDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                        userMenuDropdown.classList.add('hidden');
                        userMenuBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Search Modal Dialog
            const searchModalBtn = document.getElementById('searchModalBtn');
            const searchModal = document.getElementById('searchModal');
            const closeSearchModal = document.getElementById('closeSearchModal');
            const globalSearchInput = document.getElementById('globalSearchInput');

            if (searchModalBtn && searchModal) {
                function openSearch() {
                    searchModal.classList.remove('hidden');
                    setTimeout(() => {
                        if (globalSearchInput) globalSearchInput.focus();
                    }, 50);
                    document.body.style.overflow = 'hidden';
                }

                function closeSearch() {
                    searchModal.classList.add('hidden');
                    document.body.style.overflow = '';
                }

                searchModalBtn.addEventListener('click', openSearch);
                if (closeSearchModal) closeSearchModal.addEventListener('click', closeSearch);

                searchModal.addEventListener('click', function(e) {
                    if (e.target === searchModal) {
                        closeSearch();
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !searchModal.classList.contains('hidden')) {
                        closeSearch();
                    }
                });
            }

            // AJAX Newsletter Subscription with Skeleton / Loading Feedback
            const newsletterForm = document.getElementById('footerNewsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(newsletterForm);
                    const submitBtn = newsletterForm.querySelector('button[type="submit"]');
                    const originalBtnContent = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Subscribing...</span>';

                    fetch(newsletterForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            newsletterForm.innerHTML = '<div class="p-3 bg-emerald-900/80 border border-emerald-600 text-emerald-100 text-xs rounded flex items-center gap-2"><i class="fa-solid fa-check text-xs text-emerald-400"></i><span>' + (data.message || 'Thank you for subscribing to our dispatch.') + '</span></div>';
                        } else {
                            alert(data.error || 'Subscription failed. Please check your email address and try again.');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnContent;
                        }
                    })
                    .catch(err => {
                        newsletterForm.submit();
                    });
                });
            }
        });
    </script>
</body>
</html>
