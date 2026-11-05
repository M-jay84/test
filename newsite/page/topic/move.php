<?php
declare(strict_types=1);

use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$topicid = (int) param(0, 0);
if (!is_post() || !csrf_verify()) {
    redirect('forum/view', __('NOTHING_SELECTED'), 'warning');
}
TopicsLogic::move($topicid, $current_user);