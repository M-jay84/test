<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BansLogic;

$current_user = Auth::user();
$type = (string) param(0, 'email');
if (!in_array($type, ['email', 'ip', 'domain'], true)) {
    $type = 'email';
}

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('ban/admin/' . $type, 'Invalid request', 'warning');
    }
    BansLogic::insert($current_user);
    return;
}

$bans  = BansLogic::getAll($type);
$BANS  = $bans['bans'] ?? [];
$PAGER = $bans['pager'] ?? '';

theme_admin_header(__('BANNED'));
render_flash();
block_begin(__('BANNED'));
?>
<div class="text-center">
    <a href="<?= url('ban/admin/email') ?>"><?= e(__('EMAIL')) ?></a>&nbsp;
    <a href="<?= url('ban/admin/ip') ?>"><?= e(__('IP')) ?></a>&nbsp;
    <a href="<?= url('ban/admin/domain') ?>"><?= e(__('DOMAIN')) ?></a>
</div>
<p class="text-end">
    <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#banModal"><?= e(__('ADD')) ?></a>
</p>

<form id="delete" action="<?= url('ban/delete') ?>" method="post">
    <?= csrf_field() ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('DATE_ADDED')) ?></th>
                    <th><?= e(__('FIRST')) ?></th>
                    <th><?= e(__('ADDED_BY')) ?></th>
                    <th><?= e(__('COMMENT')) ?></th>
                    <th><?= e(__('TYPE')) ?></th>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($BANS as $row) : ?>
                <tr>
                    <td><?= e(user_timezone_date($row['added'] ?? '')) ?></td>
                    <td><?= e($row['first'] ?? '') ?></td>
                    <td><?= coloredname((int) ($row['addedby'] ?? 0)) ?></td>
                    <td><?= e($row['comment'] ?? '') ?></td>
                    <td><?= e($row['type'] ?? '') ?></td>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-center"><button type="submit" class="btn btn-sm ttbtn"><?= e(__('DEL')) ?></button></p>
</form>

<form method="post" action="<?= url('ban/admin/' . $type) ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="banModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body p-2">
                    <div class="text-center">
                        <b><?= e(__('ADD_BAN')) ?></b><br>
                        <input type="text" class="form-control fullwidth" name="first" placeholder="<?= e(__('BAN_EMAIL_IP')) ?>"><br>
                        <select name="type" class="form-select">
                            <option value="email"><?= e(__('EMAIL')) ?></option>
                            <option value="ip"><?= e(__('IP')) ?></option>
                            <option value="domain"><?= e(__('DOMAIN')) ?></option>
                        </select><br>
                        <input type="text" class="form-control fullwidth" name="comment" placeholder="<?= e(__('COMMENT')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $PAGER ?>
<?php
block_end();
theme_admin_footer();