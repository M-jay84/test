<?php
declare(strict_types=1);

/**
 * Logout — no model needed beyond Auth.
 * URL: /authorise/logout
 */

use Bootstrap\Libs\Auth;

Auth::destroyCookies();

// Session was destroyed — start again so flash works
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

redirect('home', __('LOGOUT') !== 'LOGOUT' ? __('LOGOUT') : 'You have been logged out', 'success');