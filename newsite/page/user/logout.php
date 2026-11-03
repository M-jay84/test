<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;

Auth::destroyCookies();

// Session was destroyed — start a new one so flash works
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

redirect('home', __('LOGOUT') . ' – success', 'success');