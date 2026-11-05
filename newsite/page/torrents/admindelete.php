<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TorrentsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
if (!is_post() || !csrf_verify()) {
    redirect('torrents/admin', __('NOTHING_SELECTED'), 'warning');
}

$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || $ids === []) {
    redirect('torrents/admin', __('NOTHING_SELECTED'), 'warning');
}

$uid = (int) Auth::user()->id;
foreach ($ids as $id) {
    TorrentsLogic::deleteTorrent((int) $id, $uid);
}
redirect('torrents/admin', __('DEL_COMPLETE'), 'success');