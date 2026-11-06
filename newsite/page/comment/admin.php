<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$data     = CommentsLogic::grabAll();
$COMMENTS = $data['stmt'] ?? [];
$PAGER    = $data['pager'] ?? '';

theme_admin_header(__('COMMENT'));
render_flash();
block_begin(__('COMMENT'));

echo '<div class="rounded p-3">';
foreach ($COMMENTS as $row) {
    $title = $row['title'] ?: ($row['name'] ?? '');
    $body  = stripslashes(format_comment($row['text'] ?? ''));
    $meta  = '';

    if ((int) ($row['torrent'] ?? 0) > 0) {
        $meta = e(__('TORRENT')) . ': <a href="' . e(url('torrent/view/' . (int) $row['torrent'])) . '">' . e($title) . '</a> '
              . e(__('POSTED')) . ' <b>' . e($row['added'] ?? '') . '</b> '
              . e(__('BY')) . ' <a href="' . e(url('profile/view/' . (int) $row['user'])) . '">' . coloredname((int) $row['user']) . '</a> '
              . '[ <a href="' . e(url('comment/delete/' . (int) $row['id'] . '/torrent')) . '">' . e(__('DEL')) . '</a> ]';
    } elseif ((int) ($row['news'] ?? 0) > 0) {
        $meta = e(__('NEWS')) . ': <a href="' . e(url('comment/view/' . (int) $row['news'] . '/news')) . '">' . e($title) . '</a> '
              . e(__('POSTED')) . ' <b>' . e($row['added'] ?? '') . '</b> '
              . e(__('BY')) . ' <a href="' . e(url('profile/view/' . (int) $row['user'])) . '">' . coloredname((int) $row['user']) . '</a> '
              . '[ <a href="' . e(url('comment/delete/' . (int) $row['id'] . '/news')) . '">' . e(__('DEL')) . '</a> ]';
    } else {
        $meta = e(__('OTHER'));
    }

    echo "<div class='table-responsive'><table class='table table-striped'><thead><tr><th>{$meta}</th></tr></thead>";
    echo "<tr><td>{$body}</td></tr></table></div>";
}
echo '</div>' . $PAGER;

block_end();
theme_admin_footer();