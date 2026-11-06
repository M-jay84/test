<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ReportsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$reports   = ReportsLogic::pagination();
$REPORTS   = $reports['stmt'] ?? [];
$PAGER     = $reports['pager'] ?? '';
$type      = (string) get('type', '');
$completed = (string) get('completed', '0');
$base      = url('report/view');

theme_admin_header(__('REPORT'));
render_flash();
block_begin(__('REPORT'));
?>
<form id="sort" action="">
<p class="text-center">
    <b><?= e(__('TYPE')) ?>:</b>
    <select name="type" onchange="window.location='<?= e($base) ?>?type='+this.value+'&completed='+document.forms['sort'].completed.value">
        <option value=""><?= e(__('ALL')) ?></option>
        <?php foreach (['user' => 'USERS', 'torrent' => 'TORRENT', 'comment' => 'COMMENT', 'forum' => 'FORUM'] as $val => $lk) : ?>
            <option value="<?= e($val) ?>" <?= $type === $val ? 'selected' : '' ?>><?= e(__($lk)) ?></option>
        <?php endforeach; ?>
    </select>
    <b><?= e(__('COMPLETED')) ?>:</b>
    <select name="completed" onchange="window.location='<?= e($base) ?>?completed='+this.value+'&type='+document.forms['sort'].type.value">
        <option value="0" <?= $completed === '0' ? 'selected' : '' ?>><?= e(__('NO')) ?></option>
        <option value="1" <?= $completed === '1' ? 'selected' : '' ?>><?= e(__('YES')) ?></option>
    </select>
</p>
</form>

<form method="post" action="<?= e(url('report/submit')) ?>">
    <?= csrf_field() ?>
    <div class="table-responsive p-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('REPORTED_BY')) ?></th>
                    <th><?= e(__('TYPE')) ?></th>
                    <th><?= e(__('REASON')) ?></th>
                    <th><?= e(__('DEALT_WITH')) ?></th>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($REPORTS as $row) :
                $dealtwith = (int) ($row['dealtby'] ?? 0) > 0
                    ? coloredname((int) $row['dealtby'])
                    : e(__('NO'));
                ?>
                <tr>
                    <td><?= coloredname((int) ($row['addedby'] ?? 0)) ?></td>
                    <td><?= e($row['type'] ?? '') ?></td>
                    <td><?= e($row['reason'] ?? '') ?></td>
                    <td><?= $dealtwith ?></td>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) ($row['id'] ?? 0) ?>"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="text-center">
        <?php if ($completed !== '1') : ?>
            <button type="submit" class="btn btn-sm ttbtn" name="mark" value="1"><?= e(__('MARK_COMPLETE')) ?></button>
        <?php endif; ?>
        <button type="submit" class="btn btn-sm ttbtn" name="delete" value="1"><?= e(__('DEL')) ?></button>
    </div>
</form>
<?= $PAGER ?>
<?php
block_end();
theme_admin_footer();