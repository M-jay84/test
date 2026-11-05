<?php
declare(strict_types=1);

/**
 * Application bootstrap.
 * Order matters: paths → errors → autoload → config → session → language → helpers → router.
 */

// ---------------------------------------------------------------
// 1. Disk paths (LOG_PATH, etc.) — needed before error logging
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/paths.php';

// ---------------------------------------------------------------
// 2. Error / exception handlers (catch failures from here on)
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/functions/error_functions.php';

// true on local XAMPP, false on live (shows exception detail in red box)
if (!defined('APP_TESTING')) {
    define('APP_TESTING', true);
}
error_register_handlers(APP_TESTING);

// ---------------------------------------------------------------
// 3. Class autoloader
//    Bootstrap\Libs\Auth → BASEROOT/Bootstrap/Libs/Auth.php
// ---------------------------------------------------------------
spl_autoload_register(static function (string $className): void {
    $filename = BASEROOT . '/' . str_replace('\\', '/', $className) . '.php';
    if (is_file($filename)) {
        require_once $filename;
    }
});

// ---------------------------------------------------------------
// 4. Database credentials + site CONFIG array
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/config/database.php';
require_once BASEROOT . '/Bootstrap/config/settings.php';

// ---------------------------------------------------------------
// 5. Timezone + PHP session
// ---------------------------------------------------------------
date_default_timezone_set('Europe/London');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('MBITSYSESSID');
    session_start([
        'cookie_httponly'  => true,
        'cookie_samesite'  => 'Lax',
        'cookie_secure'    => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'use_strict_mode'  => true,
    ]);
}

// ---------------------------------------------------------------
// 6. Language (cookie → DEFAULTLANG → english.php)
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/functions/language_functions.php';
load_language();

// ---------------------------------------------------------------
// 7. Helpers (url, redirect, flash, csrf, e, …) + theme UI
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/functions/helper_functions.php';
require_once BASEROOT . '/Bootstrap/functions/style_functions.php';

// ---------------------------------------------------------------
// 8. Legacy helpers (old code still in transition)
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/functions/legacy_functions.php';

// ---------------------------------------------------------------
// 9. Router (URL → file include)
// ---------------------------------------------------------------
require_once BASEROOT . '/Bootstrap/functions/router_functions.php';

// ---------------------------------------------------------------
// 10. Identify visitor (session or remember-me cookie)
//     Must run after session + DB config are ready
// ---------------------------------------------------------------
\Bootstrap\Libs\Auth::user(true);