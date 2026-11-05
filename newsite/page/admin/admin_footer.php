        </div>

    </div>

    <!-- Footer -->
    <footer style="padding: 12px 16px; background:#121212; border-top:1px solid #1e3a8a; font-size:13px;">
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; max-width:1600px; margin:0 auto;">
            <div>
                &copy; <a href="<?=url() ?>"><?= CONFIG['SITENAME'] ?></a>
            </div>
            <div>
                M-BiTsy By <a href="https://github.com/M-jay84/M-BiTsy">M-jay</a> |
                <?php
                $totaltime = array_sum(explode(" ", microtime())) - $GLOBALS['tstart'];
                printf(LANG["PAGE_GENERATED_IN"], $totaltime);
                ?>
            </div>
        </div>
    </footer>

<script>
    // Toggle menu sections (stay open)
    function toggleMenu(header) {
        const items = header.nextElementSibling;
        const isOpen = items.classList.contains('open');

        if (isOpen) {
            items.classList.remove('open');
            header.classList.remove('active');
        } else {
            items.classList.add('open');
            header.classList.add('active');
        }

        // Save open sections to localStorage
        saveMenuState();
    }

    // Save which sections are open
    function saveMenuState() {
        const openSections = [];
        document.querySelectorAll('.menu-section').forEach((section, index) => {
            if (section.querySelector('.menu-items').classList.contains('open')) {
                openSections.push(index);
            }
        });
        localStorage.setItem('adminMenuOpen', JSON.stringify(openSections));
    }

    // Restore open sections on page load
    function restoreMenuState() {
        const openSections = JSON.parse(localStorage.getItem('adminMenuOpen') || '[]');
        document.querySelectorAll('.menu-section').forEach((section, index) => {
            if (openSections.includes(index)) {
                section.querySelector('.menu-items').classList.add('open');
                section.querySelector('.menu-header').classList.add('active');
            }
        });
    }

    // Highlight current page link
    function highlightCurrentLink() {
        const currentPath = window.location.pathname + window.location.search;
        document.querySelectorAll('.admin-sidebar .menu-items a').forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(/https?:\/\/[^\/]+/, ''))) {
                link.classList.add('active');

                // Also open the parent section
                const menuItems = link.closest('.menu-items');
                const header = menuItems.previousElementSibling;
                menuItems.classList.add('open');
                header.classList.add('active');
            }
        });
    }

    // User dropdown
    function toggleDropdown(id) {
        document.querySelectorAll('.dropdown').forEach(d => {
            if (d.id !== id) d.classList.remove('open');
        });
        document.getElementById(id).classList.toggle('open');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
        }
    });

    // Mobile sidebar
    function toggleSidebar() {
        document.getElementById('adminSidebar').classList.toggle('open');
    }

    // Init on page load
    document.addEventListener('DOMContentLoaded', function() {
        restoreMenuState();
        highlightCurrentLink();

        // Show mobile toggle
        const toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn) {
            toggleBtn.style.display = window.innerWidth <= 900 ? 'block' : 'none';
        }
    });

    window.addEventListener('resize', function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn) {
            toggleBtn.style.display = window.innerWidth <= 900 ? 'block' : 'none';
        }
    });
</script>

    <!-- Keep existing scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="<?=url('page/theme/default/js/smileIT_checkall.js') ?>"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
    <script type="module">
        import Tags from "<?=url('page/theme/default/js/tags.js') ?>";
        Tags.init("select[multiple]");
    </script>

</body>
</html>
<?php ob_end_flush(); ?>