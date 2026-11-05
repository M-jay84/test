<?php
declare(strict_types=1);

/**
 * URL: /demo/params/one/two/three
 * File matches at demo/params.php; leftover = params
 */

theme_header('Demo – Multiple params');
render_flash();

block_begin('Params');
echo '<p><code>param(0)</code> = ' . e((string) param(0, '')) . '</p>';
echo '<p><code>param(1)</code> = ' . e((string) param(1, '')) . '</p>';
echo '<p><code>param(2)</code> = ' . e((string) param(2, '')) . '</p>';
echo '<p>Full list: <code>' . e(json_encode($GLOBALS['params'] ?? [])) . '</code></p>';
echo '<p><a class="ttbtn" href="' . url('home') . '">Back home</a></p>';
block_end();

theme_footer();