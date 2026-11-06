<?php
declare(strict_types=1);

/**
 * Admin – process inactive users (PM / delete / disable)
 * URL: /inactive/submit
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('inactive/view', __('NOTHING_SELECTED'), 'warning');
}

$user = Auth::user();
UsersLogic::inactiveUsersSubmit((string) ($user->username ?? ''));
// Model redirects to inactive/view