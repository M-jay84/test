<?php
declare(strict_types=1);

use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$topicid = (int) param(0, 0);

if ($topicid < 1 || !is_post()) {
    redirect('forum/view', __('NOTHING_SELECTED'), 'warning');
}
csrf_verify() || redirect('forum/view', __('INVALID_REQUEST'), 'warning');

TopicsLogic::rename($topicid, $current_user);