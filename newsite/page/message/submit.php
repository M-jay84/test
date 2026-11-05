<?php
declare(strict_types=1);
use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('message/create', __('NOTHING_FOUND'), 'warning');
}
MessagesLogic::insertByType((int) Auth::user()->id);