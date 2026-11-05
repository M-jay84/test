<?php
declare(strict_types=1);

/**
 * Sidebar: members active in the last 15 minutes (cached 2 min).
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\Cache;

if (!Auth::check()) {
    return;
}

block_begin(__('ONLINE_USERS'));

$TTCache  = new Cache();
$expires  = 120;
$cacheKey = 'sql.usersonline_block';

$rows = $TTCache->Get($cacheKey, $expires);
if ($rows === false) {
    $res = DB::run(
        'SELECT id, username FROM users
         WHERE enabled = ? AND status = ? AND privacy != ?
           AND UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(last_access) <= ?',
        ['yes', 'confirmed', 'strong', CONFIG['TT_DATE'], 900]
    );
    $rows = [];
    foreach ($res as $row) {
        $rows[] = $row;
    }
    $TTCache->Set($cacheKey, $rows, $expires);
}

if (empty($rows)) {
    echo '<p class="text-center">' . e(__('NO_USERS_ONLINE')) . '</p>';
} else {
    $n = count($rows);
    foreach ($rows as $i => $row) {
        echo '<b>' . coloredname((int) $row['id']) . '</b>';
        if ($i < $n - 1) {
            echo ', ';
        }
    }
}

block_end();