<?php
declare(strict_types=1);

/**
 * URL: /like/submit/{id}/{type}
 * Example types: thanksforum, etc.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\LikesLogic;

$current_user = Auth::user();
$id   = (int) param(0, 0);
$type = (string) param(1, '');

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1 || $type === '') {
    redirect('home', __('NOTHING_FOUND'), 'warning');
}

LikesLogic::switch($id, $type, $current_user);