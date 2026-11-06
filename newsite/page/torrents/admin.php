<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TorrentsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$type   = (string) param(0, '');
$search = trim((string) get('search', ''));

// Prefer TorrentsLogic::adminList($type, $search) that binds LIKE params
if ($type === 'dead') {
    $torrents = TorrentsLogic::getAll(
        'WHERE banned = ? AND seeders < 1 ORDER BY torrents.added DESC',
        ['no'],
        'torrents/admin/dead'
    );
} elseif ($type === 'free') {
    $torrents = TorrentsLogic::getAll(
        'WHERE freeleech = ? ORDER BY name',
        [1],
        'torrents/admin/free'
    );
} elseif ($type === 'banned') {
    $torrents = TorrentsLogic::getAll(
        'WHERE banned = ? ORDER BY name',
        ['yes'],
        'torrents/admin/banned'
    );
} else {
    $torrents = TorrentsLogic::getAll(
        'WHERE torrents.id > ? ORDER BY name',
        [0],
        'torrents/admin'
    );
}

$COUNT    = (int) ($torrents['count'] ?? 0);
$PAGER    = $torrents['pager'] ?? '';
$TORRENTS = $torrents['stmt'] ?? [];

theme_admin_header(__('TORRENT'));
render_flash();
block_begin(__('TORRENT'));
?>
<form method="get" action="<?= e(url('torrents/admin' . ($type !== '' ? '/' . $type : ''))) ?>">
    <p class="tt_blockcenter rounded text-center">
        <?= e(__('SEARCH')) ?>:
        <input type="text" name="search" value="<?= e($search) ?>" size="30">
        <button type="submit"><?= e(__('SEARCH')) ?></button>
    </p>
</form>

<p class="text-center">
    <a href="<?= e(url('torrents/admin')) ?>"><?= e(__('ALL')) ?></a> -
    <a href="<?= e(url('torrents/admin/dead')) ?>"><?= e(__('DEAD')) ?></a> -
    <a href="<?= e(url('torrents/admin/banned')) ?>"><?= e(__('BANNED')) ?></a> -
    <a href="<?= e(url('torrents/admin/free')) ?>"><?= e(__('FREELEECH')) ?></a><br>
    <?= e(__('TORRENT')) ?> - <?= e(__('TOTAL')) ?> <?= $COUNT ?>
    <?php if ($type !== '') : ?> - <?= e($type) ?><?php endif; ?>
</p>

<form id="delete" method="post" action="<?= e(url('torrents/admindelete')) ?>">
    <?= csrf_field() ?>
    <?php torrenttable($TORRENTS); ?>
    <p class="text-center">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('DEL')) ?></button>
    </p>
</form>
<?= $PAGER ?>
<?php
block_end();
theme_admin_footer();