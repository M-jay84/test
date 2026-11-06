<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\SnatchedLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    csrf_verify() || redirect('snatch/trade', __('INVALID_REQUEST'), 'warning');
    SnatchedLogic::submitTrade($current_user);
    return;
}

$SNATCHED = SnatchedLogic::trade((int) $current_user->id);

theme_header(__('TRADE'));
render_flash();
block_begin(__('TRADE'));
?>
<p class="text-center">
    <?= e(__('HNR_TRADE_HELP', (int) (CONFIG['HNR_SEEDTIME'] ?? 0))) ?>
</p>

<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('TORRENT_NAME')) ?></th>
                <th><i class="fa fa-upload tticon" title="<?= e(__('UPLOADED')) ?>"></i></th>
                <th><i class="fa fa-download tticon" title="<?= e(__('DOWNLOADED')) ?>"></i></th>
                <th><?= e(__('SEED_TIME')) ?></th>
                <th><?= e(__('SIZE')) ?></th>
                <th><?= e(__('REMOVE_BONUS')) ?></th>
                <th><?= e(__('REMOVE_UPLOAD')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($SNATCHED as $row) :
            $size = (int) ($row['size'] ?? 0);
            $smallname = e(CutName((string) ($row['name'] ?? ''), 40));
            ?>
            <tr>
                <td><a href="<?= e(url('torrent/view/' . (int) $row['tid'])) ?>"><b><?= $smallname ?></b></a></td>
                <td><span style="color:#27B500"><?= mksize((int) ($row['upload'] ?? $row['uploaded'] ?? 0)) ?></span></td>
                <td><span style="color:#FF2200"><?= mksize((int) ($row['download'] ?? 0)) ?></span></td>
                <td><?= !empty($row['last_time']) ? e(get_elapsed_time($row['last_time'])) : '---' ?></td>
                <td><?= mksize($size) ?></td>
                <td>
                    <form method="post" action="<?= e(url('snatch/trade')) ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="torid" value="<?= (int) $row['tid'] ?>">
                        <?php if ((int) ($row['seedbonus'] ?? 0) >= $size) : ?>
                            <button type="submit" class="btn btn-sm ttbtn" name="requestpoints" value="1"><?= e(__('DEL')) ?></button>
                            <?= e(__('COST_BONUS', $size)) ?>
                        <?php else : ?>
                            <?= e(__('NOT_ENOUGH_BONUS')) ?>
                        <?php endif; ?>
                    </form>
                </td>
                <td>
                    <form method="post" action="<?= e(url('snatch/trade')) ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="torid" value="<?= (int) $row['tid'] ?>">
                        <?php if ((int) ($row['uploaded'] ?? 0) > $size) : ?>
                            <button type="submit" class="btn btn-sm ttbtn" name="requestupload" value="1"><?= e(__('DEL')) ?></button>
                            <?= e(__('COST_UPLOAD', mksize($size))) ?>
                        <?php else : ?>
                            <?= e(__('NOT_ENOUGH_UPLOAD')) ?>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
block_end();
theme_footer();