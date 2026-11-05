<?php
declare(strict_types=1);

/**
 * URL: /profile/changepassword/{user_id}
 * Fields: chpassword, passagain
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ProfilechangeLogic;

$current_user = Auth::user();
$userId = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if ($userId < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!Auth::isStaff() && (int) $current_user->id !== $userId) {
    redirect('profile/view/' . $userId, __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('profile/view/' . $userId, __('MISSING_FORM_DATA'), 'warning');
}

if (!csrf_verify()) {
    redirect('profile/view/' . $userId, 'Invalid request', 'warning');
}

ProfilechangeLogic::submitPassword($userId);