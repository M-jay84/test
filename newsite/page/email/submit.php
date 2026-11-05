<?php
declare(strict_types=1);

/**
 * Admin – send mass email
 * URL: /email/submit
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\EmailLogic;

$current_user = Auth::user();

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (!is_post() || !csrf_verify()) {
    redirect('email/view', __('NOTHING_FOUND'), 'warning');
}

EmailLogic::emailAll($current_user);
// Model should redirect on success, e.g. redirect('email/view', __('SUCCESS'), 'success');