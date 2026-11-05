<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\NewsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
$id = (int) param(0, 0);
if ($id < 1) {
    redirect('news/view', __('MISSING_ID'), 'warning');
}
NewsLogic::delete($id, (int) Auth::user()->id);