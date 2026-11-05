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
    redirect('home', __('NOTHING_SELECTED'), 'warning');
}

// Prefer POST + reason from warning page form
if (!is_post()) {
    redirect('profile/view/' . $id, __('NOTHING_SELECTED'), 'warning');
}

if (!csrf_verify()) {
    redirect('profile/view/' . $id, 'Invalid request', 'warning');
}

ProfileLogic::deleteprofile($id, $current_user);