<?php
declare(strict_types=1);

/**
 * URL: /profile/changeavatar/{user_id}
 * Form: multipart, field name="file"
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ProfilechangeLogic;

$current_user = Auth::user();
$userId = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($userId < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!Auth::isStaff() && (int) $current_user->id !== $userId) {
    redirect('profile/view/' . $userId, __('NO_PERMISSION'), 'warning');
}

if (empty($_FILES['file']['name'])) {
    redirect('profile/view/' . $userId, __('ACTION_FAILED'), 'warning');
}

if (!csrf_verify()) {
    redirect('profile/view/' . $userId, 'Invalid request', 'warning');
}

ProfilechangeLogic::addavatar($userId);