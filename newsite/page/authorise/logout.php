<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;

Auth::destroyCookies();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

redirect('home', __('LOGOUT'), 'success');