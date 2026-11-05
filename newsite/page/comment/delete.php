<?php
declare(strict_types=1);

/**
 * URL: /comment/delete/{id}/{type?}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

$current_user = Auth::user();
$id   = (int) param(0, 0);
$type = param(1, 0);

if (!Auth::check() || !Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

CommentsLogic::delete($id, (int) $current_user->id, $type);