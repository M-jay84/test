<?php
declare(strict_types=1);

/**
 * URL: /comment/submit/{targetid}/{type}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

$current_user = Auth::user();
$targetid = (int) param(0, 0);
$type     = (string) param(1, 'torrent');

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($targetid < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!is_post()) {
    redirect("comment/view/{$targetid}/{$type}", __('NOTHING_FOUND'), 'warning');
}

if (!csrf_verify()) {
    redirect("comment/view/{$targetid}/{$type}", 'Invalid request', 'warning');
}

CommentsLogic::action($targetid, (int) $current_user->id, $type);