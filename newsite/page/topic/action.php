<?php
declare(strict_types=1);

use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$forumid = (int) param(0, 0);
$topicid = (int) param(1, 0);
$type    = (string) param(2, '');

if ($forumid < 1 || $topicid < 1 || $type === '') {
    redirect('forum/view', __('NOTHING_FOUND'), 'warning');
}

TopicsLogic::action($forumid, $topicid, $type, $current_user);