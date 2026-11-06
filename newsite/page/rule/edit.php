<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RulesLogic;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!Auth::isStaff()) {
    redirect('rule', __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('rule', __('NOTHING_SELECTED'), 'warning');
}

if (!csrf_verify()) {
    redirect('rule', 'Invalid request', 'warning');
}

RulesLogic::insertOrUpdate($current_user->username);