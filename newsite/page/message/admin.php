<?php
declare(strict_types=1);

/**
 * Admin – message spy + mass PM
 * URL: /message/admin
 * Class check: old code was `!$current_user->class > 6` (always false). Fixed to class > 6.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

$current_user = Auth::user();

if (!Auth::check() || (int) ($current_user->class ?? 0) <= 6) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('message/admin', 'Invalid request', 'warning');
    }
    MessagesLogic::multiPM((int) $current_user->id);
    return;
}

$messages  = MessagesLogic::getAll();
$MESSAGES  = $messages['res'] ?? [];
$PAGER     = $messages['pager'] ?? '';
$GROUPS    = $messages['groups'] ?? [];

theme_admin_header(__('MESSAGE'));
render_flash();
block_begin(__('MESSAGE'));
?>
<?php if ((int) $current_user->id === 1) : ?>
<div class="text-center">
    <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal">
        <?= e(__('CREATE_MASS')) ?>
    </a>
</div>
<?php endif; ?>

<form id="delete" method="post" action="<?= url('message/admindelete') ?>">
    <?= csrf_field() ?>
    <div class="table-responsive p-1">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);"></th>
                    <th><?= e(__('SENDER')) ?></th>
                    <th><?= e(__('RECEIVER')) ?></th>
                    <th><?= e(__('ADDED')) ?></th>
                    <th><?= e(__('TEXT')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($MESSAGES as $arr) : ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) ($arr['id'] ?? 0) ?>"></td>
                    <td><?= coloredname((int) ($arr['sender'] ?? 0)) ?></td>
                    <td><?= coloredname((int) ($arr['receiver'] ?? 0)) ?></td>
                    <td><?= e(user_timezone_date($arr['added'] ?? '')) ?></td>
                    <td><?= e(strip_tags((string) ($arr['msg'] ?? ''))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-center">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('DEL')) ?></button>
    </p>
</form>

<?= $PAGER ?>

<?php if ((int) $current_user->id === 1) : ?>
<p class="text-center"><?= e(__('MESSAGE_SPY')) ?></p>

<form id="submit" method="post" action="<?= url('message/admin') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <?= e(__('GROUPS')) ?><br>
                    <?php foreach ($GROUPS as $row) : ?>
                        <label>
                            <input type="checkbox" name="classes[]" value="<?= (int) ($row['group_id'] ?? 0) ?>">
                            <?= e($row['level'] ?? '') ?>
                        </label><br>
                    <?php endforeach; ?>

                    <div class="input-group mb-3">
                        <?= e(__('SENDER')) ?>
                        <?= e((string) $current_user->username) ?>
                        <input name="sender" type="radio" value="self" checked>
                        &nbsp;<?= e(__('SYSTEM')) ?>
                        <input name="sender" type="radio" value="system">
                    </div>
                    <input type="text" class="form-control fullwidth" name="subject"
                           placeholder="<?= e(__('SUBJECT')) ?>"><br>
                    <?php textbbcode('submit', 'msg'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<?php
block_end();
theme_admin_footer();