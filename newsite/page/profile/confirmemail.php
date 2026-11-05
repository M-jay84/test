<?php
declare(strict_types=1);

/**
 * URL: /profile/confirmemail/{id}/{secret}/{email}
 * email may be urlencoded in the link
 */

use Bootstrap\Models\ConfirmLogic;

$id     = (int) param(0, 0);
$secret = (string) param(1, '');
$email  = rawurldecode((string) param(2, ''));

if ($id < 1 || $secret === '' || $email === '') {
    redirect('home', __('NOTHING_FOUND'), 'warning');
}

ConfirmLogic::confirmprofileemail($id, $secret, $email);