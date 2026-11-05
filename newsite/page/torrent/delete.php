<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TorrentsLogic;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}
// Fixed inverted check from old code
if (($current_user->delete_torrents ?? '') !== 'yes') {
    redirect('browse', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('browse', __('NO_PERMISSION'), 'warning');
}
TorrentsLogic::delete($current_user);