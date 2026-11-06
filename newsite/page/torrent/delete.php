<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TorrentsLogic;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}
if (($current_user->delete_torrents ?? '') !== 'yes') {
    redirect('torrents/browse', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('torrents/browse', __('INVALID_REQUEST'), 'warning');
}

TorrentsLogic::delete($current_user);