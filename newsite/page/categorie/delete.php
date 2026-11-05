<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CatagoriesLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}
$id   = (int) param(0, 0);
$sure = (int) param(1, 0);
if ($id < 1) {
    redirect('categorie/view', __('MISSING_ID'), 'warning');
}
CatagoriesLogic::deleteAndUpdateTorrent($id, $sure);