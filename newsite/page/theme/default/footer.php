        </div><!-- /.portalmain -->

        <!-- Right Sidebar -->
        <div class="portal_sidebar">
            <?php
            $currentUrl = $_GET['url'] ?? '';
            $hideSidebars = false; // same flag as header – fix later

            if (!$hideSidebars) {
              /* TODO
                include BASEROOT . '/page/block/navigate_block.php';
                include BASEROOT . '/page/block/usersonline_block.php';
                include BASEROOT . '/page/block/invite_block.php';
                include BASEROOT . '/page/block/newestmember_block.php';
                include BASEROOT . '/page/block/search_block.php';
                include BASEROOT . '/page/block/statistics_block.php';
                */
            }
            ?>
        </div>

    </div><!-- /.layout -->
</div><!-- /.container -->

<!-- Footer -->
<footer>
    <div class="footer-block">
        <div class="footer-inner">
            <div>
                &copy; <a href="<?= url() ?>"><?= e(CONFIG['SITENAME'] ?? 'Site') ?></a><br>
                RSS
                <a href="<?= url('rss') ?>"><i class="fa fa-rss" style="color:#3b82f6"></i></a>
                <a href="<?= url('rss/custom') ?>"><i class="fa fa-rss-square" style="color:#60a5fa"></i></a>
            </div>
            <div style="text-align:right">
                M-BiTsy By <a href="https://github.com/M-jay84/M-BiTsy">M-jay</a><br>
                Support @ <a href="https://torrenttrader.uk" target="_blank">Torrent Trader</a><br>
                <?php
                $totaltime = microtime(true) - ($GLOBALS['tstart'] ?? microtime(true));
                printf(__('PAGE_GENERATED_IN'), $totaltime);
                ?>
            </div>
        </div>
    </div>
</footer>

<!-- Dropdown JS -->
<script>
function toggleDropdown(id) {
    document.querySelectorAll('.dropdown').forEach(d => {
        if (d.id !== id) d.classList.remove('open');
    });
    document.getElementById(id).classList.toggle('open');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
    }
});
</script>

<!-- Scripts -->
<script src="<?= url('assets/js/smileIT_checkall.js') ?>"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<script src="<?= url('assets/js/overlib.js') ?>"></script>

<script type="module">
    import Tags from "<?= url('assets/js/tags.js') ?>";
    Tags.init("select[multiple]");
</script>

<?php
// These were required as files – keep them if they still exist
$extraJs = [
    'assets/js/shoutbox.js',
    'assets/js/carousel.js',
    'assets/js/usersearch.js',
];
foreach ($extraJs as $js) {
    $full = BASEROOT . '/public/' . $js; // adjust path if needed
    if (is_file($full)) {
        require $full;
    }
}
?>

</body>
</html>
<?php
if (ob_get_level() > 0) {
    ob_end_flush();
}