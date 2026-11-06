<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('confirmreg/view', __('NOTHING_SELECTED'), 'warning');
}

UsersLogic::confirmReg(); // model redirects