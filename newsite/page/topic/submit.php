<?php
declare(strict_types=1);

use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$forumid = (int) param(0, 0);
if ($forumid < 1 || !is_post() || !csrf_verify()) {
    redirect('forum/view', __('NOTHING_FOUND'), 'warning');
}
TopicsLogic::insertTopic($forumid, $current_user);