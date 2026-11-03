<?php
declare(strict_types=1);

// 1. Paths are required before logging can be configured.
require_once BASEROOT.'/Bootstrap/paths.php';

// 2. Register error handling before loading the rest of the application.
require_once BASEROOT.'/Bootstrap/functions/error_functions.php';
error_register_handlers();

// 3. Autoload Classes
spl_autoload_register(function ($className) {
    $filename = BASEROOT . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($filename)) {
        require_once($filename);
    }
});

// 4. Load Config & DB Details
require_once BASEROOT.'/Bootstrap/config/database.php';
require_once BASEROOT.'/Bootstrap/config/settings.php';

// 5. Timezone and session.
date_default_timezone_set('Europe/London');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('MBITSYSESSID');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'use_strict_mode' => true,
    ]);
}

// 6. Load language before presentation helpers so error pages can be translated.
require_once BASEROOT.'/Bootstrap/functions/language_functions.php';
load_language();

// 7. Load procedural helper files in dependency order.
require_once BASEROOT.'/Bootstrap/functions/helper_functions.php';
require_once BASEROOT.'/Bootstrap/functions/style_functions.php';

// 8. Legacy Bootstrap/functions.php.
require_once BASEROOT.'/Bootstrap/functions/legacy_functions.php';

// 9. Load the rest of the application as needed by the router.
require_once BASEROOT.'/Bootstrap/functions/router_functions.php';