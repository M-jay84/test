<?php

use Bootstrap\Libs\DB;

//Get Last Cleanup
$task_query = "SELECT last_time FROM `tasks` WHERE task=? ";
$row = DB::run($task_query, ['cleanup'])->fetchColumn();
if (!$row) {
    $lastclean = "never done...";
} else {
    $lastclean = get_elapsed_time($row);
}
$_query = "SELECT VERSION() AS version";
$row = DB::run($_query)->fetch();
$mysqlver = $row['version'];
$peer_query = "SELECT count(id) FROM `users` WHERE status = ? AND invited_by =?";
$pending = DB::run($peer_query, ['pending',0])->fetchColumn();

?>
<div class="text-center">
    <?= LANG["LAST_CLEANUP_PERFORMED"] ?>: <?= $lastclean ?> ago [<a href='<?=url('task/cleanup') ?>'><b><?= LANG["FORCE_CLEAN"] ?></b></a>]<br>
    <b><?= LANG["USERS_AWAITING_VALIDATION"] ?>:</b> <a href='<?=url('confirmreg/view') ?>'><b>(<?= $pending ?>)</b></a><br>
    <b><?= apache_get_version() ?></b>&nbsp;-&nbsp;<b><?= $mysqlver ?></b><br>
    <a href="<?=url('task/cache') ?>"><b><?= LANG["PURGE_CACHE"] ?></b></a><br>
</div>