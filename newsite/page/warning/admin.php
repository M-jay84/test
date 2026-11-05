<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Models\WarningsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$warnings  = WarningsLogic::getAll();
$PAGER     = $warnings['pager'] ?? '';
$COUNT     = $warnings['count'] ?? 0;
$WARNINGS  = $warnings['stmt'] ?? [];

theme_admin_header(__('WARNINGS'));
render_flash();
block_begin(__('WARNINGS'));
?>
<p class="text-center"><?= sprintf(__('X_USERS_WARNED'), (int) $COUNT) ?></p>

<form id="deleteall" method="post" action="<?= url('warning/adminsubmit') ?>">
    <?= csrf_field() ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('NAME')) ?></th>
                    <th><?= e(__('CLASS')) ?></th>
                    <th><?= e(__('ADDED')) ?></th>
                    <th><?= e(__('LAST_ACCESS')) ?></th>
                    <th><?= e(__('WARNINGS')) ?></th>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($WARNINGS as $row) :
                $countUserWarn = (int) DB::run(
                    'SELECT COUNT(id) FROM warnings WHERE userid = ? AND active = ?',
                    [(int) $row['id'], 'yes']
                )->fetchColumn();
                ?>
                <tr>
                    <td><?= coloredname((int) $row['id']) ?></td>
                    <td><?= e(get_user_class_name((int) ($row['class'] ?? 0))) ?></td>
                    <td><?= e(user_timezone_date($row['added'] ?? '')) ?></td>
                    <td><?= e(user_timezone_date($row['last_access'] ?? '')) ?></td>
                    <td>
                        <a href="<?= url('warning/index/' . (int) $row['id']) ?>">
                            <?= number_format($countUserWarn) ?>
                        </a>
                    </td>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>"></td>
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