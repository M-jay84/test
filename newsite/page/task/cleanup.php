<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TasksLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
TasksLogic::forceCleanup();