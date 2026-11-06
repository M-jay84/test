<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ClientsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('client/view/client', __('INVALID_REQUEST'), 'warning');
}

ClientsLogic::insert();