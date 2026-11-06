<?php
declare(strict_types=1);

/**
 * URL: /warning/index/{user_id}  or  /warning/{user_id}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\WarningsLogic;

$current_user = Auth::user();
$userId = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($userId < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (!Auth::isStaff() && (int) $current_user->id !== $userId) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$WARNINGS = WarningsLogic::getByUserId($userId);
$USER_ID  = $userId;
$TITLE    = (__('USER_DETAILS_FOR') !== 'USER_DETAILS_FOR' ? __('USER_DETAILS_FOR') : '')
          . strip_tags(coloredname($userId));

theme_header($TITLE);
render_flash();
block_begin($TITLE);
usermenu($USER_ID, 'warning');

if (Auth::isStaff()) :
?>
    <p class="text-center">
        <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><?= e(__('ADD')) ?></a>
    </p>

    <form id="deleteall" method="post" action="<?= url('warning/delete/' . $USER_ID) ?>">
        <?= csrf_field() ?>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('ADDED')) ?></th>
                <th><?= e(__('EXPIRE')) ?></th>
                <th><?= e(__('REASON')) ?></th>
                <th><?= e(__('WARNED_BY')) ?></th>
                <th><?= e(__('TYPE')) ?></th>
                <?php if (Auth::isStaff()) : ?>
                    <th><?= e(__('ACTION')) ?>
                        <input type="checkbox" name="checkall" onclick="checkAll(this.form.id);">
                    </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($WARNINGS as $row) :
            $added  = user_timezone_date($row['added'] ?? '');
            $expiry = user_timezone_date($row['expiry'] ?? '');
            $addedDate  = strpos($added, ' ') !== false ? substr($added, 0, strpos($added, ' ')) : $added;
            $expiryDate = strpos($expiry, ' ') !== false ? substr($expiry, 0, strpos($expiry, ' ')) : $expiry;
            ?>
            <tr>
                <td><?= e($addedDate) ?></td>
                <td><?= e($expiryDate) ?></td>
                <td><?= format_comment($row['reason'] ?? '') ?></td>
                <td><?= coloredname((int) ($row['warnedby'] ?? 0)) ?></td>
                <td><?= e((string) ($row['type'] ?? '')) ?></td>
                <?php if (Auth::isStaff()) : ?>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>"></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (Auth::isStaff()) : ?>
    <p class="text-center">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('DEL')) ?></button>
    </p>
    </form>
<?php endif; ?>

<?php if (($current_user->delete_users ?? '') === 'yes' && ($current_user->control_panel ?? '') === 'yes') : ?>
    <div class="tt_blockcenter rounded p-3">
        <form method="post" action="<?= url('profile/delete/' . $USER_ID) ?>">
            <?= csrf_field() ?>
            <div class="text-center">
                <p><b><?= e(__('DELETE_USER')) ?></b></p>
                <div class="form-floating mb-4">
                    <input class="form-control fullwidth" type="text" name="delreason" id="delreason">
                    <label for="delreason"><?= e(__('REASON')) ?>:</label>
                </div>
                <button type="submit" class="btn btn-sm ttbtn"><b><?= e(__('DELETE_ACCOUNT')) ?></b></button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (($current_user->edit_users ?? '') === 'yes' && ($current_user->control_panel ?? '') === 'yes') : ?>
    <form id="submit" method="post" action="<?= url('warning/submit/' . $USER_ID) ?>">
        <?= csrf_field() ?>
        <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="text-center">
                            <textarea class="form-control" cols="40" rows="5" name="reason" placeholder="<?= e(__('REASON')) ?>"></textarea>
                        </div>
                        <div class="input-group mb-3">
                            <input class="form-control" type="number" name="expiry" placeholder="Days">
                            <input class="form-control" type="text" name="type" placeholder="Type">
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
<?php endif;

block_end();
theme_footer();