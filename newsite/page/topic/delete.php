<?php
declare(strict_types=1);

use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$topicid = (int) param(0, 0);
$sure    = (int) param(1, 0);
TopicsLogic::delTopic($topicid, $sure);