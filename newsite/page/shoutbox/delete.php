<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ShoutboxLogic;

$id   = (int) param(0, 0);
$sure = (int) param(1, 0);

if (!Auth::check() || $id < 1) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
ShoutboxLogic::delete($id, Auth::user(), $sure);