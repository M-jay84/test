<?php
declare(strict_types=1);

/**
 * Front controller — every request is rewritten here by .htaccess.
 *
 * Flow:
 *   1. Define BASEROOT (project root on disk)
 *   2. Bootstrap the app (config, session, helpers, Auth, …)
 *   3. Hand off to the router (URL → page file)
 */

// Resolve absolute path (normalises Windows \ vs /)
$baseRoot = realpath(__DIR__);
if ($baseRoot === false) {
    $baseRoot = __DIR__;
}
define('BASEROOT', $baseRoot);

// Load the rest of the application
require BASEROOT . '/Bootstrap/init.php';

// Match URL to a page file under page/user or page/admin
router_dispatch();