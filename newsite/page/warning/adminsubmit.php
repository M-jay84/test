<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\WarningsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('warning/admin', __('NOTHING_SELECTED'), 'warning');
}
WarningsLogic::deleteAllAdmin();