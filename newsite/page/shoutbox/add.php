<?php
declare(strict_types=1);
use Bootstrap\Libs\Auth;
use Bootstrap\Models\ShoutboxLogic;

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('home', __('NOTHING_FOUND'), 'warning');
}
ShoutboxLogic::add(Auth::user());