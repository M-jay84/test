<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

$current_user = Auth::user();

if (!Auth::check() || (int) ($current_user->class ?? 0) <= 6) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('message/admin', __('NOTHING_SELECTED'), 'warning');
}
MessagesLogic::deleteByIds();