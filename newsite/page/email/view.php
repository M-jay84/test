<?php
declare(strict_types=1);

/**
 * Admin – mass email form
 * URL: /email/view  (or /adminemail)
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\GroupsLogic;

$current_user = Auth::user();

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$GROUPS = GroupsLogic::getGroups();

theme_admin_header(__('MASS_EMAIL'));
render_flash();
block_begin(__('MASS_EMAIL'));
?>
<div class="tt_blockcenter rounded p-2">
    <form name="Form" method="post" action="<?= url('email/submit') ?>">
        <?= csrf_field() ?>

        <input type="text"
               class="form-control fullwidth"
               name="subject"
               placeholder="<?= e(__('SUBJECT')) ?>"
               required><br>

        <?php foreach ($GROUPS as $row) : ?>
            <label>
                <input type="checkbox"
                       name="groups[]"
                       value="<?= (int) ($row['group_id'] ?? 0) ?>">
                <?= e($row['level'] ?? '') ?>
            </label><br>
        <?php endforeach; ?>

        <?php textbbcode('Form', 'body'); ?>

        <div class="text-center">
            <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
        </div>
    </form>
</div>
<?php
block_end();
theme_admin_footer();