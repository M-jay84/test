<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$targetid = (int) param(0, 0);
$type     = (string) param(1, 'torrent');

if ($targetid < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect("comment/view/{$targetid}/{$type}", __('INVALID_REQUEST'), 'warning');
}

CommentsLogic::action($targetid, (int) Auth::id(), $type);