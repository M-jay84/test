<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$type = (string) param(0, 'inbox');
if (!in_array($type, ['inbox', 'outbox'], true)) {
    $type = 'inbox';
}

if (!is_post() || !csrf_verify()) {
    redirect('message/mailbox/' . $type, __('NOTHING_FOUND'), 'warning');
}

MessagesLogic::delete($type, (int) Auth::id());