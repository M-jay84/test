<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TorrentsLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}
if ($id < 1 || !is_post()) {
    redirect('torrent/view/' . max($id, 0), __('ACTION_FAILED'), 'warning');
}
csrf_verify() || redirect('torrent/view/' . $id, __('INVALID_REQUEST'), 'warning');

TorrentsLogic::submitIdDetails($id, $current_user);