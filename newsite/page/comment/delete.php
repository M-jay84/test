<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

if (!Auth::check() || !Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$id   = (int) param(0, 0);
$type = (string) param(1, '');

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

CommentsLogic::delete($id, (int) Auth::id(), $type);