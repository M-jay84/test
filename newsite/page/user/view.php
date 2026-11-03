<?php
declare(strict_types=1);

$id = (int) param(0, 0);

if ($id < 1) {
    error(__('MISSING_ID'), 400);
}

theme_header('Account #' . $id);
render_flash();

block_begin('Account view');
echo '<p>File: <code>page/user/account/view.php</code></p>';
echo '<p>URL param 0: <strong>' . e((string) $id) . '</strong></p>';
echo '<p>All params: <code>' . e(json_encode($GLOBALS['params'] ?? [])) . '</code></p>';
echo '<p><a class="ttbtn" href="' . url('home') . '">Back home</a></p>';
block_end();

theme_footer();