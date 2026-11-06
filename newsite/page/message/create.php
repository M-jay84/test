<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

theme_header(__('MESSAGE'));
render_flash();
block_begin(__('MESSAGE'));
?>
<p class="text-center">
    <a href="<?= url('message/mailbox/inbox') ?>"><button type="button" class="<?= e(activelink('inbox', 'inbox')) ?>"><?= e(__('INBOX')) ?></button></a>
    <a href="<?= url('message/mailbox/outbox') ?>"><button type="button" class="<?= e(activelink('outbox', 'outbox')) ?>"><?= e(__('OUTBOX')) ?></button></a>
    <a href="<?= url('message/create') ?>"><button type="button" class="<?= e(activelink('message/create')) ?>"><?= e(__('COMPOSE')) ?></button></a>
</p>

<form name="form" action="<?= url('message/submit') ?>" method="post">
    <?= csrf_field() ?>
    <div class="row tt_blockcenter rounded p-3">
        <?= e(__('RECEIVER')) ?><br>
        <?php if ($id > 0) : ?>
            <input type="hidden" name="receiver" value="<?= $id ?>">
            <?= coloredname($id) ?><br>
        <?php else : ?>
            <input type="text" id="search-box" name="receiver" class="form-control" autocomplete="off" placeholder="User Name">
            <div id="suggesstion-box"></div><br>
        <?php endif; ?>

        <?= e(__('SUBJECT')) ?>
        <input type="text" name="subject" class="form-control fullwidth" placeholder="<?= e(__('SUBJECT')) ?>" id="subject"><br><br>
        <?php textbbcode('form', 'body'); ?><br>

        <div class="text-center">
            <button type="submit" class="btn btn-sm ttbtn" name="Update" value="create"><?= e(__('SUBMIT')) ?></button>
            <label><?= e(__('SAVE_COPY')) ?></label>
            <input type="checkbox" name="save" value="1" checked>
        </div>
    </div>
</form>
<?php
block_end();
theme_footer();