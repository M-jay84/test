<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BonusLogic;

if (!Auth::check() || !Auth::isStaff()) {
    redirect('bonus/view', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('bonus/view', __('INVALID_REQUEST'), 'warning');
}

BonusLogic::insertOrUpdate();