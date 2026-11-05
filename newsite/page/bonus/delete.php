<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BonusLogic;

$id = (int) param(0, 0);

if (!Auth::check() || !Auth::isStaff()) {
    redirect('bonus/view', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('bonus/view', __('MISSING_ID'), 'warning');
}

BonusLogic::deleteone($id);