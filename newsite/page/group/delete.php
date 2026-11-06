<?php
declare(strict_types=1);

/**
 * Admin – delete group
 * URL: /group/delete/{group_id}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\GroupsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$groupId = (int) param(0, 0);
if ($groupId < 1) {
    redirect('group/view', __('MISSING_ID'), 'warning');
}

GroupsLogic::delete($groupId); // model redirects