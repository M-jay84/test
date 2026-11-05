<?php
declare(strict_types=1);

/**
 * Admin – process inactive users (PM / delete / disable)
 * URL: /inactive/submit
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

$current_user = Auth::user();

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('inactive/view', __('NOTHING_SELECTED'), 'warning');
}

$username = (string) ($current_user->username ?? '');
UsersLogic::inactiveUsersSubmit($username);
// Model should redirect('inactive/view', __('SUCCESS'), 'success') or similar