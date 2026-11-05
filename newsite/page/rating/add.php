<?php
declare(strict_types=1);

/**
 * URL: /rating/add/{torrent_id}
 * POST field: rating (1–5)
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RatingsLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('NOTHING_SELECTED'), 'warning');
}

if (!is_post()) {
    redirect('torrent/' . $id, __('NOTHING_SELECTED'), 'warning');
}

if (!csrf_verify()) {
    redirect('torrent/' . $id, 'Invalid request', 'warning');
}

RatingsLogic::addRating($id, (int) $current_user->id);