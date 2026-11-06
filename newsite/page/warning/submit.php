<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\WarningsLogic;

$current_user = Auth::user();
$userId = (int) param(0, 0);

if (!Auth::check() || !Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if ($userId < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('warning/index/' . $userId, __('INVALID_REQUEST'), 'warning');
}

WarningsLogic::newwarn($userId);