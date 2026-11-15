    </main>

    <!-- Master Footer -->
    <footer class="bg-navy-900 text-slate-300 border-t border-navy-800 pt-16 pb-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-navy-800">
                
                <!-- Col 1: Brand & Division Statement (2 cols wide on desktop) -->
                <div class="lg:col-span-2 space-y-5">
                    <div class="flex items-center gap-3">
                        <img src="<?= asset('assets/images/lagunawise-map-logo.png') ?>" 
                             alt="LagunaWISE Emblem" 
                             class="w-12 h-12 object-contain rounded-full shadow-md">
                        <div>
                            <span class="block text-xs font-bold tracking-widest text-lwblue-light uppercase">LagunaWISE</span>
                            <span class="text-xl font-extrabold text-white tracking-tight">IDEOLOGY DIVISION</span>
                        </div>
                    </div>
                    
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        <?= e(get_setting('division_statement', 'An intellectual and educational division of LagunaWISE dedicated to fostering critical thinking, media literacy, informed discussions, and responsible digital citizenship.')) ?>
                    </p>

                    <!-- LagunaWISE Wordmark with Socials supplied visual reference -->
                    <div class="pt-2">
                        <div class="inline-block bg-white/95 rounded-lg p-2.5 shadow-sm border border-navy-700 max-w-xs">
                            <img src="<?= asset('assets/images/lagunawise-wordmark.png') ?>" 
                                 alt="LagunaWISE Official Wordmark and Social Presence" 
                                 class="h-8 object-contain">
                        </div>
                    </div>

                    <div class="text-xs text-slate-400 space-y-1.5 pt-1">
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-lwblue-light w-4"></i>
                            <span><?= e(get_setting('organization_location', 'Barangay Barandal, Calamba City, Laguna, Philippines')) ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-lwblue-light w-4"></i>
                            <a href="mailto:<?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>" class="hover:text-white transition">
                                <?= e(get_setting('contact_email', 'lagunawise.ph@gmail.com')) ?>
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h3 class="text-xs font-bold tracking-wider uppercase text-white mb-4">Division Platform</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?= url() ?>" class="hover:text-white transition">Home Page</a></li>
                        <li><a href="<?= url('pages/about.php') ?>" class="hover:text-white transition">About the Division</a></li>
                        <li><a href="<?= url('pages/articles.php') ?>" class="hover:text-white transition">Articles &amp; Research</a></li>
                        <li><a href="<?= url('pages/topics.php') ?>" class="hover:text-white transition">Featured Topics</a></li>
                        <li><a href="<?= url('pages/news.php') ?>" class="hover:text-white transition">News &amp; Campaigns</a></li>
                        <li><a href="<?= url('pages/contact.php') ?>" class="hover:text-white transition">Contact &amp; Partnerships</a></li>
                    </ul>
                </div>

                <!-- Col 3: Research Topics -->
                <div>
                    <h3 class="text-xs font-bold tracking-wider uppercase text-white mb-4">Core Research Topics</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?= url('pages/topic.php?slug=media-literacy') ?>" class="hover:text-white transition">Media &amp; Information Literacy</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=misinformation') ?>" class="hover:text-white transition">Misinformation Ecosystems</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=digital-citizenship') ?>" class="hover:text-white transition">Digital Citizenship &amp; Ethics</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=critical-thinking') ?>" class="hover:text-white transition">Critical Thinking &amp; Fallacies</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=technology-and-society') ?>" class="hover:text-white transition">Technology &amp; Artificial Intelligence</a></li>
                        <li><a href="<?= url('pages/topic.php?slug=social-issues') ?>" class="hover:text-white transition">Contemporary Social Issues</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter Subscription in Footer -->
                <div>
                    <h3 class="text-xs font-bold tracking-wider uppercase text-white mb-3">Editorial Dispatch</h3>
                    <p class="text-xs text-slate-400 mb-3 leading-relaxed">
                        Receive curated research briefs, community toolkits, and civic analysis directly to your inbox.
                    </p>
                    <form action="<?= url('actions/newsletter-action.php') ?>" method="POST" class="space-y-2" id="footerNewsletterForm">
                        <?= Security::csrfField() ?>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   required 
                                   placeholder="Your email address" 
                                   class="w-full text-xs px-3.5 py-2.5 bg-navy-800 border border-navy-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-lwblue-light focus:ring-1 focus:ring-lwblue-light">
                        </div>
                        <button type="submit" class="w-full text-xs font-bold py-2.5 bg-lwblue hover:bg-lwblue-hover text-white rounded-lg transition duration-150 flex items-center justify-center gap-2">
                            <span>Subscribe to Dispatch</span>
                            <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        </button>
                    </form>
                    <p class="text-[10px] text-slate-400 mt-2">Zero spam. Nonpartisan, educational dispatches only.</p>
                </div>

            </div>

            <!-- Bottom Legal Bar -->
            <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-400">
                <div class="text-center md:text-left">
                    <p><?= e(get_setting('footer_copyright', '© 2026 LagunaWISE Ideology Division. All rights reserved. Intellectual and educational nonpartisan publication.')) ?></p>
                </div>
                <div class="flex items-center gap-6">
                    <a href="<?= url('pages/about.php#editorial-charter') ?>" class="hover:text-slate-200 transition">Editorial Charter</a>
                    <a href="<?= url('pages/about.php#privacy') ?>" class="hover:text-slate-200 transition">Privacy Policy</a>
                    <a href="<?= url('pages/about.php#terms') ?>" class="hover:text-slate-200 transition">Terms of Use</a>
                    <a href="<?= url('admin/index.php') ?>" class="text-slate-400 hover:text-slate-200 transition">Staff Portal</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Vanilla JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            const mobileMenuBtn = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuIcon = document.getElementById('mobileMenuIcon');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
                    mobileMenuBtn.setAttribute('aria-expanded', !isExpanded);
                    mobileMenu.classList.toggle('hidden');
                    if (mobileMenuIcon) {
                        mobileMenuIcon.classList.toggle('fa-bars');
                        mobileMenuIcon.classList.toggle('fa-xmark');
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
                    // Cmd+K or Ctrl+K shortcut
                    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                        e.preventDefault();
                        if (searchModal.classList.contains('hidden')) {
                            openSearch();
                        } else {
                            closeSearch();
                        }
                    }
                });
            }

            // Interactive Bookmark AJAX Handler
            document.querySelectorAll('.bookmark-toggle-btn').forEach(button => {
                button.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const articleId = this.getAttribute('data-article-id');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '<?= Security::getCsrfToken() ?>';

                    if (!articleId) return;

                    try {
                        const response = await fetch('<?= url("actions/bookmark-action.php") ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new URLSearchParams({
                                'article_id': articleId,
                                'csrf_token': csrfToken
                            })
                        });

                        const data = await response.json();

                        if (data.status === 'unauthorized') {
                            window.location.href = '<?= url("auth/login.php") ?>?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                            return;
                        }

                        if (data.status === 'success') {
                            const icon = this.querySelector('i');
                            const label = this.querySelector('.bookmark-label');
                            if (data.action === 'bookmarked') {
                                if (icon) {
                                    icon.classList.remove('fa-regular');
                                    icon.classList.add('fa-solid', 'text-amber-500');
                                }
                                if (label) label.textContent = 'Bookmarked';
                            } else {
                                if (icon) {
                                    icon.classList.remove('fa-solid', 'text-amber-500');
                                    icon.classList.add('fa-regular');
                                }
                                if (label) label.textContent = 'Bookmark';
                            }
                        }
                    } catch (err) {
                        console.error('Bookmark toggle error:', err);
                    }
                });
            });

        });
    </script>
</body>
</html>
