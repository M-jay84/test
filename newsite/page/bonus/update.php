<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BonusLogic;

// Staff-only create/edit (original had inverted isStaff check)
if (!Auth::check() || !Auth::isStaff()) {
    redirect('bonus/view', __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('bonus/view', __('NOTHING_FOUND'), 'warning');
}

if (!csrf_verify()) {
    redirect('bonus/view', 'Invalid request', 'warning');
}

BonusLogic::insertOrUpdate();