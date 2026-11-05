<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\SnatchedLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('snatch/admin', __('NOTHING_SELECTED'), 'warning');
}
SnatchedLogic::deleteByIds();