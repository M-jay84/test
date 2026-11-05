<?php
declare(strict_types=1);

/**
 * Sidebar: five newest confirmed members (privacy != strong), cached 10 min.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\Cache;

if (!Auth::check()) {
    return;
}

block_begin(__('NEWEST_MEMBERS'));

$TTCache  = new Cache();
$expire   = 600;
$cacheKey = 'sql.newestmember_block';

$rows = $TTCache->Get($cacheKey, $expire);
if ($rows === false) {
    $res = DB::run(
        'SELECT id, username FROM users
         WHERE enabled = ? AND status = ? AND privacy != ?
         ORDER BY id DESC LIMIT 5',
        ['yes', 'confirmed', 'strong']
    );
    $rows = [];
    foreach ($res as $row) {
        $rows[] = $row;
    }
    $TTCache->Set($cacheKey, $rows, $expire);
}

if (empty($rows)) {
    echo '<p class="text-center">' . e(__('NOTHING_FOUND')) . '</p>';
} else {
    echo '<div>';
    foreach ($rows as $row) {
        echo '<a href="' . url('profile/view/' . (int) $row['id']) . '">'
           . coloredname((int) $row['id']) . '</a> ';
    }
    echo '</div>';
}

block_end();