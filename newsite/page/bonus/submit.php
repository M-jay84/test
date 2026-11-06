<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BonusLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('bonus/view', __('NOTHING_FOUND'), 'warning');
}

if (!csrf_verify()) {
    redirect('bonus/view', 'Invalid request', 'warning');
}

BonusLogic::getById($current_user);