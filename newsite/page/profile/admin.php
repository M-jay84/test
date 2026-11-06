<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ProfileLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check() || !Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!is_post()) {
    redirect('profile/view/' . $id, __('NOTHING_SELECTED'), 'warning');
}

csrf_verify() || redirect('profile/view/' . $id, __('INVALID_REQUEST'), 'warning');

ProfileLogic::adminSubmited($id, $current_user);