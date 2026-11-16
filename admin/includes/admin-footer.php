                <!-- Admin Global Footer Demonstration Notice -->
                <footer class="mt-auto pt-8 pb-4 text-center text-xs text-slate-400 border-t border-slate-200">
                    <p class="font-semibold text-slate-600">PROJECT DEMONSTRATION for LAGUNAWISE, developed by MICHAEL BENEDICT ORDENES, CSSWB</p>
                    <p class="text-[11px] text-amber-700 font-mono mt-0.5">NOTICE: THIS ENTIRE WEBSITE IS A PROTOTYPE ONLY</p>
                </footer>
            </div>
        </main>
    </div>

    <!-- Admin Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            if (toggle && sidebar) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
