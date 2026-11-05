<?php
declare(strict_types=1);

/**
 * Sidebar: main navigation links for members / public site.
 */

use Bootstrap\Libs\Auth;

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    return;
}

block_begin(__('NAVIGATION'));

if (Auth::check()) {
    $user = Auth::user();
    $viewTorrents = (string) ($user->view_torrents ?? $_SESSION['view_torrents'] ?? '');
    $class        = (int) ($user->class ?? $_SESSION['class'] ?? 0);
    ?>
    <div class="p-2">
        <a href="<?= url() ?>"><b><?= e(__('HOME')) ?></b></a><br>
        <?php
        // Upload: skip if uploaders-only and user below VIP
        $canUploadLink = true;
        if (!empty(CONFIG['UPLOADERSONLY']) && $class < (int) (CONFIG['_VIP'] ?? 0)) {
            $canUploadLink = false;
        }
        if ($viewTorrents === 'yes' || ($viewTorrents === 'no' && !empty(CONFIG['MEMBERSONLY']))) {
            if ($canUploadLink) {
                echo '<a href="' . url('upload/torrent') . '"><b>' . e(__('UPLOAD')) . '</b></a><br>';
            }
            echo '<a href="' . url('torrents/browse') . '"><b>' . e(__('SEARCH_TORRENTS')) . '</b></a><br>';
        }
        ?>
        <a href="<?= url('forum/view') ?>"><b><?= e(__('FORUMS')) ?></b></a><br>
        <a href="<?= url('users/members') ?>"><b><?= e(__('MEMBERS')) ?></b></a><br>
        <a href="<?= url('rule') ?>"><b><?= e(__('RULES')) ?></b></a>
    </div>
    <?php
}

block_end();