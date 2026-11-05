<?php
declare(strict_types=1);

/**
 * POST-only: report user / torrent / forum / etc.
 * Form fields: id, type, reason, (postid for forum)
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ReportsLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if (!is_post()) {
    redirect('home', __('NOTHING_SELECTED'), 'warning');
}

if (!csrf_verify()) {
    redirect('home', 'Invalid request', 'warning');
}

ReportsLogic::reportItem($current_user);