<?php
declare(strict_types=1);
use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
$comments  = CommentsLogic::grabAll();
$COMMENTS  = $comments['stmt'] ?? [];
$PAGER     = $comments['pager'] ?? '';

theme_admin_header(__('COMMENT'));
render_flash();
block_begin(__('COMMENT'));
echo '<div class="rounded p-3">';
foreach ($COMMENTS as $row) {
    $title = $row['title'] ?: ($row['name'] ?? '');
    $comentario = stripslashes(format_comment($row['text'] ?? ''));
    $type = 'Other';
    if ((int) ($row['torrent'] ?? 0) > 0) {
        $type = 'Torrent: <a href="' . url('torrent/view/' . (int) $row['torrent']) . '">' . e($title) . '</a> '
              . 'Posted <b>' . e($row['added'] ?? '') . '</b> by '
              . '<a href="' . url('profile/view/' . (int) $row['user']) . '">' . coloredname((int) $row['user']) . '</a> '
              . '[ <a href="' . url('comment/delete/' . (int) $row['id']) . '">delete</a> ]';
    }
    if ((int) ($row['news'] ?? 0) > 0) {
        $type = 'News: <a href="' . url('comment/view/' . (int) $row['news'] . '/news') . '">' . e($title) . '</a> '
              . 'Posted <b>' . e($row['added'] ?? '') . '</b> by '
              . '<a href="' . url('profile/view/' . (int) $row['user']) . '">' . coloredname((int) $row['user']) . '</a> '
              . '[ <a href="' . url('comment/delete/' . (int) $row['id']) . '">delete</a> ]';
    }
    echo "<div class='table-responsive'><table class='table table-striped'><thead><tr><th>{$type}</th></tr></thead>";
    echo "<tr><td>{$comentario}</td></tr></table></div>";
}
echo '</div>' . $PAGER;
block_end();
theme_admin_footer();