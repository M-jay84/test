<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\InvitesLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('invite/home', __('INVALID_REQUEST'), 'warning');
}

InvitesLogic::sendInvite(Auth::user());