<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$users = UsersLogic::usersToConfirm();
$COUNT = (int) ($users['count'] ?? 0);
$PAGER = (string) ($users['pager'] ?? '');
$USERS = $users['users'] ?? [];

theme_admin_header(__('REG_CONFIRM'));
render_flash();
block_begin(__('REG_CONFIRM'));
?>
<form method="post" action="<?= e(url('confirmreg/submit')) ?>" id="confirmreg">
    <?= csrf_field() ?>
    <div class="table-responsive p-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('USERNAME')) ?></th>
                    <th><?= e(__('EMAIL')) ?></th>
                    <th><?= e(__('ADDED')) ?></th>
                    <th><?= e(__('IP')) ?></th>
                    <th>
                        <input type="checkbox" name="checkall" onclick="checkAll(this.form.id);">
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($USERS as $row) : ?>
                <tr>
                    <td><?= e((string) ($row['username'] ?? '')) ?></td>
                    <td><?= e((string) ($row['email'] ?? '')) ?></td>
                    <td><?= e((string) ($row['added'] ?? '')) ?></td>
                    <td><?= e((string) ($row['ip'] ?? '----')) ?></td>
                    <td>
                        <input type="checkbox" name="users[]" value="<?= (int) ($row['id'] ?? 0) ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center p-2">
            <select name="action" class="form-select" style="max-width:220px;display:inline-block;">
                <option value="confirm"><?= e(__('CONFIRM')) ?></option>
                <option value="delete"><?= e(__('DELETE')) ?></option>
            </select>
            <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
        </div>
    </div>
</form>
<?= $PAGER ?>
<p class="text-center"><b><?= $COUNT ?></b> <?= e(__('USERS')) ?></p>
<?php
block_end();
theme_admin_footer();