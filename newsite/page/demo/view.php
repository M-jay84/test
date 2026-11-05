<?php
declare(strict_types=1);

/**
 * URL: /demo/view/42  → param(0) = "42"
 */

$id = (int) param(0, 0);

if ($id < 1) {
    error(__('MISSING_ID'), 400);
}

theme_header('Demo – View #' . $id);
render_flash();

block_begin('Router param');
echo '<p>Matched file: <code>page/demo/view.php</code></p>';
echo '<p><strong>param(0)</strong> = ' . e((string) $id) . '</p>';
echo '<p>All params: <code>' . e(json_encode($GLOBALS['params'] ?? [])) . '</code></p>';
echo '<p><a class="ttbtn" href="' . url('home') . '">Back home</a></p>';
block_end();

theme_footer();