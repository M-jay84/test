<?php
declare(strict_types=1);
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$topicid = (int) param(0, 0);

if ($topicid < 1 || !is_post()) {
    redirect('forum/view', __('NOTHING_FOUND'), 'warning');
}
if (!csrf_verify()) {
    redirect('post/view/' . $topicid, 'Invalid request', 'warning');
}
PostsLogic::addPost($topicid, $current_user);