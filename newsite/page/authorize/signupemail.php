<?php
declare(strict_types=1);

/**
 * Confirm signup email link.
 * URL: /authorize/signupemail/{id}/{secret}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ConfirmLogic;

$id     = (int) param(0, 0);
$secret = (string) param(1, '');

if ($id < 1 || strlen($secret) < 10) {
    redirect('authorize/login', __('NOTHING_FOUND'), 'warning');
}

// Model currently ends with message()/redirect — call it
ConfirmLogic::confirmemail($id, $secret);

// If model is updated to return instead of redirect:
// ConfirmLogic::confirmemail($id, $secret);
// redirect('authorize/login', __('ACCOUNT_ACTIVATED'), 'success');