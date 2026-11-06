<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ClientsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$id = (int) param(0, 0);
if ($id < 1) {
    redirect('client/view/client', __('MISSING_ID'), 'warning');
}

ClientsLogic::delete($id);