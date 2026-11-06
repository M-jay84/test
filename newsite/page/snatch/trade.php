<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\SnatchedLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('snatch/trade', 'Invalid request', 'warning');
    }
    SnatchedLogic::submitTrade($current_user);
    return;
}

$SNATCHED = SnatchedLogic::trade((int) $current_user->id);

theme_header(__('TRADE'));
render_flash();
block_begin(__('TRADE'));
?>
<p class="text-center">
    To solve this problem you must keep seeding these torrents for <?= (int) (CONFIG['HNR_SEEDTIME'] ?? 0) ?> hours
    or until ratio becomes 1:1.<br>
    Or trade to clear them with Bonus or Upload.
</p>

<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('TORRENT_NAME')) ?></th>
                <th><i class="fa fa-upload tticon" title="Uploaded"></i></th>
                <th><i class="fa fa-download tticon" title="Downloaded"></i></th>
                <th><?= e(__('SEED_TIME')) ?></th>
                <th><?= e(__('SIZE')) ?></th>
                <th>Remove Bonus</th>
                <th>Remove Upload</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($SNATCHED as $row) :
            $smallname = htmlspecialchars(CutName($row['name'] ?? '', 40));
            ?>
            <tr>
                <td><a href="<?= url('torrent/view/' . (int) $row['tid']) ?>"><b><?= $smallname ?></b></a></td>
                <td><span style="color:#27B500"><?= mksize($row['uploaded'] ?? 0) ?></span></td>
                <td><span style="color:#FF2200"><?= mksize($row['download'] ?? 0) ?></span></td>
                <td><?= !empty($row['last_time']) ? e(get_elapsed_time($row['last_time'])) : '---' ?></td>
                <td><?= mksize($row['size'] ?? 0) ?></td>
                <td>
                    <form method="post" action="<?= url('snatch/trade') ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="torid" value="<?= (int) $row['tid'] ?>">
                        <?php if (($row['seedbonus'] ?? 0) >= ($row['size'] ?? 0)) : ?>
                            <button type="submit" class="btn btn-sm ttbtn" name="requestpoints" value="1">Delete</button>
                            Cost <b><?= (int) $row['size'] ?></b> Bonus
                        <?php else : ?>
                            Not enough Seed Bonus
                        <?php endif; ?>
                    </form>
                </td>
                <td>
                    <form method="post" action="<?= url('snatch/trade') ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="torid" value="<?= (int) $row['tid'] ?>">
                        <?php if (($row['uploaded'] ?? 0) > ($row['size'] ?? 0)) : ?>
                            <button type="submit" class="btn btn-sm ttbtn" name="requestupload" value="1">Delete</button>
                            Cost <b><?= mksize($row['size'] ?? 0) ?></b> Upload
                        <?php else : ?>
                            Not enough Upload
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