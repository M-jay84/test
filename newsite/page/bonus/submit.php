<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BonusLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('bonus/view', __('INVALID_REQUEST'), 'warning');
}

BonusLogic::getById(Auth::user());