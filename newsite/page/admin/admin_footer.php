    </div><!-- .admin-content -->
</div><!-- .admin-wrapper -->

<footer style="padding:12px 16px;background:#121212;border-top:1px solid #1e3a8a;font-size:13px;">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;max-width:1600px;margin:0 auto;">
        <div>
            &copy; <a href="<?= e(url()) ?>"><?= e(CONFIG['SITENAME'] ?? '') ?></a>
        </div>
        <div>
            M-BiTsy By <a href="https://github.com/M-jay84/M-BiTsy">M-jay</a> |
            <?php
            $totaltime = microtime(true) - (float) ($GLOBALS['tstart'] ?? microtime(true));
            printf(e(__('PAGE_GENERATED_IN')), $totaltime);
            ?>
        </div>
    </div>
</footer>

<script>
function toggleMenu(header) {
    const items = header.nextElementSibling;
    const open = items.classList.contains('open');
    items.classList.toggle('open', !open);
    header.classList.toggle('active', !open);
    saveMenuState();
}
function saveMenuState() {
    const openSections = [];
    document.querySelectorAll('.menu-section').forEach((section, index) => {
        if (section.querySelector('.menu-items')?.classList.contains('open')) {
            openSections.push(index);
        }
    });
    localStorage.setItem('adminMenuOpen', JSON.stringify(openSections));
}
function restoreMenuState() {
    const openSections = JSON.parse(localStorage.getItem('adminMenuOpen') || '[]');
    document.querySelectorAll('.menu-section').forEach((section, index) => {
        if (openSections.includes(index)) {
            section.querySelector('.menu-items')?.classList.add('open');
            section.querySelector('.menu-header')?.classList.add('active');
        }
    });
}
function highlightCurrentLink() {
    const currentPath = window.location.pathname + window.location.search;
    document.querySelectorAll('.admin-sidebar .menu-items a').forEach(link => {
        const href = link.getAttribute('href') || '';
        const path = href.replace(/^https?:\/\/[^/]+/, '');
        if (path && currentPath.includes(path)) {
            link.classList.add('active');
            const menuItems = link.closest('.menu-items');
            const header = menuItems?.previousElementSibling;
            menuItems?.classList.add('open');
            header?.classList.add('active');
        }
    });
}
function toggleDropdown(id) {
    document.querySelectorAll('.dropdown').forEach(d => {
        if (d.id !== id) d.classList.remove('open');
    });
    document.getElementById(id)?.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
    }
});
function toggleSidebar() {
    document.getElementById('adminSidebar')?.classList.toggle('open');
}
function syncSidebarToggle() {
    const btn = document.getElementById('sidebarToggle');
    if (btn) btn.style.display = window.innerWidth <= 900 ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function () {
    restoreMenuState();
    highlightCurrentLink();
    syncSidebarToggle();
});
window.addEventListener('resize', syncSidebarToggle);
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="<?= e(url('page/theme/default/js/smileIT_checkall.js')) ?>"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<script type="module">
    import Tags from "<?= e(url('page/theme/default/js/tags.js')) ?>";
    Tags.init("select[multiple]");
</script>
</body>
</html>
<?php if (ob_get_level() > 0) { ob_end_flush(); } ?>