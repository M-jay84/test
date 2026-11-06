<?php
declare(strict_types=1);

use Bootstrap\Libs\DB;

// TODO: move to AdminLogic::dashboardStats() — queries belong in model
$lastTs = DB::run(
    'SELECT last_time FROM tasks WHERE task = ?',
    ['cleanup']
)->fetchColumn();

$lastclean = $lastTs ? get_elapsed_time($lastTs) : __('NEVER');

$mysqlver = (string) DB::run('SELECT VERSION()')->fetchColumn();

$pending = (int) DB::run(
    'SELECT COUNT(id) FROM users WHERE status = ? AND invited_by = ?',
    ['pending', 0]
)->fetchColumn();
?>
<div class="text-center">
    <?= e(__('LAST_CLEANUP_PERFORMED')) ?>:
    <?= e($lastclean) ?>
    [<a href="<?= e(url('task/cleanup')) ?>"><b><?= e(__('FORCE_CLEAN')) ?></b></a>]<br>

    <b><?= e(__('USERS_AWAITING_VALIDATION')) ?>:</b>
    <a href="<?= e(url('confirmreg/view')) ?>"><b>(<?= $pending ?>)</b></a><br>

    <b><?= e(function_exists('apache_get_version') ? (string) apache_get_version() : 'Apache') ?></b>
    &nbsp;-&nbsp;<b><?= e($mysqlver) ?></b><br>

    <a href="<?= e(url('task/cache')) ?>"><b><?= e(__('PURGE_CACHE')) ?></b></a>
</div>