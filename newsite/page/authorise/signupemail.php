<?php
declare(strict_types=1);

use Bootstrap\Models\ConfirmLogic;

$id     = (int) param(0, 0);
$secret = (string) param(1, '');

if ($id < 1 || strlen($secret) < 10) {
    redirect('authorise/login', __('NOTHING_FOUND'), 'warning');
}

// Model currently redirects via message()
ConfirmLogic::confirmemail($id, $secret);