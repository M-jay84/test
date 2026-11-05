<?php
declare(strict_types=1);
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$postid = (int) param(0, 0);
$sure   = (int) param(1, 0);

if ($postid < 1) {
    redirect('forum/view', __('MISSING_ID'), 'warning');
}
PostsLogic::deletePost($postid, $current_user, $sure);