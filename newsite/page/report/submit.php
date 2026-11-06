<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ReportsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('report/view', __('ACTION_FAILED'), 'warning');
}

if (post('mark')) {
    ReportsLogic::update((int) Auth::id());
    return;
}

if (post('delete')) {
    ReportsLogic::deleteByIds();
    return;
}

redirect('report/view', __('ACTION_FAILED'), 'warning');