<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ProfileLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!Auth::isStaff() && (int) $current_user->id !== $id) {
    redirect('profile/view/' . $id, __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('profile/view/' . $id, __('NOTHING_SELECTED'), 'warning');
}

csrf_verify() || redirect('profile/view/' . $id, __('INVALID_REQUEST'), 'warning');

ProfileLogic::submit($id, $current_user);