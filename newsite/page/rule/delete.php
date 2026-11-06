<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RulesLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!Auth::isStaff()) {
    redirect('rule', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('rule', __('MISSING_ID'), 'warning');
}

// Prefer POST later; for now model deletes
RulesLogic::delete($id, (string) $current_user->username);