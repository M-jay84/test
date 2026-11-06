<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ReportsLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('home', __('NOTHING_SELECTED'), 'warning');
}

csrf_verify() || redirect('home', __('INVALID_REQUEST'), 'warning');

ReportsLogic::reportItem($current_user);