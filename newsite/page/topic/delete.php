<?php
declare(strict_types=1);

use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$topicid = (int) param(0, 0);
$sure    = (int) param(1, 0);

if ($topicid < 1) {
    redirect('forum/view', __('NOTHING_FOUND'), 'warning');
}

TopicsLogic::delTopic($topicid, $sure, $current_user);