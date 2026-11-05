<?php
declare(strict_types=1);

/**
 * Sidebar: site statistics.
 * Full detail for staff (edit_users); reduced for normal members.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;

$date_time = date('Y-m-d H:i:s', (int) CONFIG['TT_TIME'] - 86400);

$registered = (int) DB::run('SELECT COUNT(id) FROM users')->fetchColumn();
$ncomments  = (int) DB::run('SELECT COUNT(id) FROM comments')->fetchColumn();
$nmessages  = (int) DB::run('SELECT COUNT(id) FROM messages')->fetchColumn();
$ntor       = (int) DB::run('SELECT COUNT(id) FROM torrents')->fetchColumn();
$totaltoday = (int) DB::run('SELECT COUNT(id) FROM users WHERE last_access >= ?', [$date_time])->fetchColumn();
$regtoday   = (int) DB::run('SELECT COUNT(id) FROM users WHERE added >= ?', [$date_time])->fetchColumn();
$todaytor   = (int) DB::run('SELECT COUNT(id) FROM torrents WHERE added >= ?', [$date_time])->fetchColumn();

$past = (int) CONFIG['TT_TIME'] - 2400;
DB::run('DELETE FROM guests WHERE time < ?', [$past]);
$guests = (int) DB::run('SELECT COUNT(ip) FROM guests')->fetchColumn();

$seeders  = (int) DB::run('SELECT COUNT(id) FROM peers WHERE seeder = ?', ['yes'])->fetchColumn();
$leechers = (int) DB::run('SELECT COUNT(id) FROM peers WHERE seeder = ?', ['no'])->fetchColumn();
$members  = (int) DB::run(
    'SELECT COUNT(id) FROM users WHERE UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(last_access) < ?',
    [CONFIG['TT_DATE'], 900]
)->fetchColumn();

$totalonline     = $members + $guests;
$totaldownloaded = (int) DB::run('SELECT COALESCE(SUM(downloaded),0) FROM users')->fetchColumn();
$totaluploaded   = (int) DB::run('SELECT COALESCE(SUM(uploaded),0) FROM users')->fetchColumn();
$localpeers      = $leechers + $seeders;

$isStaffEdit = Auth::check() && (($user = Auth::user()) && (($user->edit_users ?? '') === 'yes'));

if ($isStaffEdit) {
    block_begin(__('STATISTICS'));
    ?>
    <ul class="list-unstyled">
        <p><strong><?= e(__('TORRENTS')) ?></strong></p>
        <li><i class="fa fa-folder-open-o"></i> <?= e(__('TRACKING')) ?>: <strong><?= $ntor ?></strong></li>
        <li><i class="fa fa-calendar-o"></i> <?= e(__('TODAYS_TORRENTS')) ?>: <strong><?= $todaytor ?></strong></li>
        <li><i class="fa fa-refresh"></i> <?= e(__('SEEDERS')) ?>: <strong><?= number_format($seeders) ?></strong></li>
        <li><i class="fa fa-arrow-circle-down"></i> <?= e(__('LEECHERS')) ?>: <strong><?= number_format($leechers) ?></strong></li>
        <li><i class="fa fa-arrow-circle-up"></i> <?= e(__('PEERS')) ?>: <strong><?= $localpeers ?></strong></li>
        <li><i class="fa fa-download"></i> <?= e(__('DOWNLOADED')) ?>: <strong><?= mksize($totaldownloaded) ?></strong></li>
        <li><i class="fa fa-upload"></i> <?= e(__('UPLOADED')) ?>: <strong><?= mksize($totaluploaded) ?></strong></li>
        <hr>
        <p><strong><?= e(__('MEMBERS')) ?></strong></p>
        <li><?= e(__('WE_HAVE')) ?>: <strong><?= $registered ?></strong></li>
        <li><?= e(__('NEW_TODAY')) ?>: <strong><?= $regtoday ?></strong></li>
        <li><?= e(__('VISITORS_TODAY')) ?>: <strong><?= $totaltoday ?></strong></li>
        <hr>
        <p><strong><?= e(__('ONLINE')) ?></strong></p>
        <li><?= e(__('TOTAL_ONLINE')) ?>: <strong><?= $totalonline ?></strong></li>
        <li><?= e(__('MEMBERS')) ?>: <strong><?= $members ?></strong></li>
        <li><?= e(__('GUESTS_ONLINE')) ?>: <strong><?= $guests ?></strong></li>
        <li><?= e(__('COMMENTS_POSTED')) ?>: <strong><?= $ncomments ?></strong></li>
        <li><?= e(__('MESSAGES_SENT')) ?>: <strong><?= $nmessages ?></strong></li>
    </ul>
    <?php
    block_end();
} elseif (Auth::check()) {
    // Fixed: old `!$_SESSION['edit_users'] == 'yes'` was wrong
    block_begin(__('STATISTICS'));
    ?>
    <ul class="list-unstyled">
        <p><strong><?= e(__('TORRENTS')) ?></strong></p>
        <li><i class="fa fa-folder-open-o"></i> <?= e(__('TRACKING')) ?>: <strong><?= $ntor ?></strong></li>
        <li><i class="fa fa-calendar-o"></i> <?= e(__('NEW_TODAY')) ?>: <strong><?= $todaytor ?></strong></li>
    </ul>
    <?php
    block_end();
}