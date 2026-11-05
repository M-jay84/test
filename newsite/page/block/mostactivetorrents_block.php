<?php
declare(strict_types=1);

/**
 * Sidebar: top 10 torrents by seeder+leecher activity (cached 10 min).
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\Cache;

if (!Auth::check()) {
    return;
}

block_begin(__('MOST_ACTIVE'));

$TTCache  = new Cache();
$expires  = 600;
$cacheKey = 'sql.mostactivetorrents_block';

$rows = $TTCache->Get($cacheKey, $expires);
if ($rows === false) {
    $res = DB::run(
        'SELECT id, name, seeders, leechers FROM torrents
         WHERE banned = ? AND visible = ?
         ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT 10',
        ['no', 'yes']
    );
    $rows = [];
    foreach ($res as $row) {
        $rows[] = $row;
    }
    $TTCache->Set($cacheKey, $rows, $expires);
}

if (!empty($rows)) {
    foreach ($rows as $row) {
        $smallname = e(CutName((string) $row['name'], 20)) . '...';
        ?>
        <a href="<?= url('torrent/view/' . (int) $row['id']) ?>" title="<?= e($row['name']) ?>">
            <?= $smallname ?>
        </a><br>
        <span class="label label-success">S: <?= number_format((int) $row['seeders']) ?></span>
        <span class="label label-warning">L: <?= number_format((int) $row['leechers']) ?></span>
        <br>
        <?php
    }
} else {
    echo '<p>' . e(__('NOTHING_FOUND')) . '</p>';
}

block_end();