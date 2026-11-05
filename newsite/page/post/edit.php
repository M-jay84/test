<?php
declare(strict_types=1);
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
if (!is_post()) {
    redirect('forum/view', __('NOTHING_SELECTED'), 'warning');
}
if (!csrf_verify()) {
    redirect('forum/view', 'Invalid request', 'warning');
}
PostsLogic::edit($current_user);