<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\SnatchedLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$snatched  = SnatchedLogic::join('snatched.hnr = ?', 'yes');
$COUNT     = $snatched['count'] ?? 0;
$PAGER     = $snatched['pager'] ?? '';
$SNATCHED  = $snatched['stmt'] ?? [];

theme_admin_header(__('HNR'));
render_flash();
block_begin(__('HNR'));
?>
<div class="text-center">
    <?= sprintf(__('WE_HAVE_X_SNATCH'), (int) $COUNT) ?>
</div>

<form id="snatched" method="post" action="<?= url('snatch/submit') ?>">
    <?= csrf_field() ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('NAME')) ?></th>
                    <th><?= e(__('USER')) ?></th>
                    <th><?= e(__('UPLOADED')) ?></th>
                    <th><?= e(__('DOWNLOADED')) ?></th>
                    <th><?= e(__('SEED_TIME')) ?></th>
                    <th><?= e(__('STARTED')) ?></th>
                    <th><?= e(__('LAST_ACTION')) ?></th>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id)"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($SNATCHED as $row) :
                $stime = get_elapsed_time((int) ($row['last_time'] ?? 0));
                $startdate = user_timezone_date(date('Y-m-d H:i:s', (int) ($row['start_time'] ?? 0)));
                $lastaction = user_timezone_date(date('Y-m-d H:i:s', (int) ($row['upload_time'] ?? 0)));
                ?>
                <tr>
                    <td>
                        <a href="<?= url('torrent/view/' . (int) ($row['tid'] ?? 0)) ?>">
                            <?= e(CutName((string) ($row['name'] ?? ''), 35)) ?>
                        </a>
                    </td>
                    <td>
                        <?php if (!empty($row['username'])) : ?>
                            <b><?= coloredname((int) ($row['uid'] ?? 0)) ?></b>
                        <?php else : ?>
                            <?= e($row['ip'] ?? '') ?>
                        <?php endif; ?>
                    </td>
                    <td><span style="color:limegreen"><?= mksize((int) ($row['upload'] ?? 0)) ?></span></td>
                    <td><span style="color:red"><?= mksize((int) ($row['download'] ?? 0)) ?></span></td>
                    <td><?= e($stime !== null && $stime !== '' ? (string) $stime : '0') ?></td>
                    <td><?= e(date('d.M.Y H:i', strtotime($startdate) ?: 0)) ?></td>
                    <td><?= e(date('d.M.Y H:i', strtotime($lastaction) ?: 0)) ?></td>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) ($row['sid'] ?? 0) ?>"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('DEL')) ?></button>
    </div>
</form>
<?= $PAGER ?>
<?php
block_end();
theme_admin_footer();