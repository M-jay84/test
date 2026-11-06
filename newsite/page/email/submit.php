<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\EmailLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('email/view', __('NOTHING_FOUND'), 'warning');
}

EmailLogic::emailAll(Auth::user());