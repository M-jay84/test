<?php
declare(strict_types=1);

/**
 * Sidebar: last 5 visible torrents (cached ~15 minutes).
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\Cache;

if (Auth::check() || empty(CONFIG['MEMBERSONLY'])) {
    block_begin(__('LATEST_TORRENTS'));

    $TTCache = new Cache();
    $expire  = 900;
    $cacheKey = 'sql.latestuploadsblock';

    $latestuploadsrecords = $TTCache->Get($cacheKey, $expire);
    if ($latestuploadsrecords === false) {
        $q = DB::run(
            'SELECT id, name, size, seeders, leechers FROM torrents
             WHERE banned = ? AND visible = ? ORDER BY id DESC LIMIT 5',
            ['no', 'yes']
        );
        $latestuploadsrecords = [];
        foreach ($q as $row) {
            $latestuploadsrecords[] = $row;
        }
        $TTCache->Set($cacheKey, $latestuploadsrecords, $expire);
    }

    if (!empty($latestuploadsrecords)) {
        foreach ($latestuploadsrecords as $row) {
            $smallname = e(CutName((string) $row['name'], 20)) . '...';
            ?>
            <a href="<?= url('torrent/view/' . (int) $row['id']) ?>" title="<?= e($row['name']) ?>">
                <?= $smallname ?>
            </a><br>
            <span><?= e(__('SIZE')) ?>: <?= mksize((int) $row['size']) ?></span><br>
            <?php
        }
    } else {
        echo '<p class="text-center">' . e(__('NOTHING_FOUND')) . '</p>';
    }

    block_end();
}