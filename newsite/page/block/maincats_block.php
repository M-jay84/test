<?php
declare(strict_types=1);

/**
 * Sidebar: parent categories → browse links.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;

if (Auth::check() || empty(CONFIG['MEMBERSONLY'])) {
    block_begin(__('BROWSE_TORRENTS'));
    $catsquery = DB::run(
        'SELECT DISTINCT parent_cat, id, image FROM categories ORDER BY parent_cat'
    );
    ?>
    <div class="list-group">
        <a href="<?= url('browse') ?>" class="list-group-item">
            <i class="fa fa-list tticon"></i> <?= e(__('SHOW_ALL')) ?>
        </a>
        <?php foreach ($catsquery as $catsrow) : ?>
            <a href="<?= url('browse?cat=' . (int) $catsrow['id']) ?>" class="list-group-item">
                <i class="fa <?= e($catsrow['image'] ?? '') ?> tticon"></i>
                <?= e($catsrow['parent_cat'] ?? '') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    block_end();
}