<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\InvitesLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('invite/home', __('MISSING_FORM_DATA'), 'warning');
}

if (!csrf_verify()) {
    redirect('invite/home', 'Invalid request', 'warning');
}

InvitesLogic::sendInvite($current_user);