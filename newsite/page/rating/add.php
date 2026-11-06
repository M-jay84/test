<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RatingsLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('NOTHING_SELECTED'), 'warning');
}

if (!is_post()) {
    redirect('torrent/' . $id, __('NOTHING_SELECTED'), 'warning');
}

csrf_verify() || redirect('torrent/' . $id, __('INVALID_REQUEST'), 'warning');

RatingsLogic::addRating($id, (int) $current_user->id);