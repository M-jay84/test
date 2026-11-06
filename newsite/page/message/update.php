<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$id = (int) param(0, 0);

if ($id < 1 || !is_post() || !csrf_verify()) {
    redirect('message/mailbox/inbox', __('NOTHING_FOUND'), 'warning');
}

MessagesLogic::update($id, (int) Auth::id());