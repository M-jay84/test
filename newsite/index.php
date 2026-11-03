<?php
declare(strict_types=1);

// 1. Realpath normalises Windows separators; set base root.
$baseRoot = realpath(__DIR__);
if ($baseRoot === false) {
    $baseRoot = dirname(__DIR__);
}
define('BASEROOT', $baseRoot);

// 2. Load all files in the Bootstrap directory.
require BASEROOT.'/Bootstrap/init.php';

// 3. Start router .
router_dispatch();