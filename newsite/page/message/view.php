<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

$current_user = Auth::user();
$id   = (int) param(0, 0);
$type = (string) param(1, 'inbox');

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}
if ($id < 1) {
    redirect('message/mailbox/inbox', __('MISSING_ID'), 'warning');
}

$MESSAGES = MessagesLogic::getById($id, (int) $current_user->id);

theme_header(__('MESSAGE'));
render_flash();
block_begin(__('MESSAGE'));
?>
<div class="row rounded">
    <p class="text-center">
        <a href="<?= url('message/mailbox/inbox') ?>"><button type="button" class="<?= e(activelink('inbox', 'inbox')) ?>"><?= e(__('INBOX')) ?></button></a>
        <a href="<?= url('message/mailbox/outbox') ?>"><button type="button" class="<?= e(activelink('outbox', 'outbox')) ?>"><?= e(__('OUTBOX')) ?></button></a>
        <a href="<?= url('message/create') ?>"><button type="button" class="<?= e(activelink('message/create')) ?>"><?= e(__('COMPOSE')) ?></button></a>
    </p>
    <p class="text-center"><b><?= e($MESSAGES['subject'] ?? '') ?></b></p>
    <span class="text-end">
        <a href="javascript:;" class="addAttr" data-bs-toggle="modal" data-bs-target="#replyModal">
            <button type="button" class="btn ttbtn m-2"><?= e(__('REPLY')) ?></button>
        </a>
    </span>

    <div class="tt_block rounded p-3">
        <div class="row">
            <div class="col-12">
                <?= coloredname((int) ($MESSAGES['sender'] ?? 0)) ?>
                <small><?= e(__('POSTED_AT')) ?> <?= e($MESSAGES['added'] ?? '') ?></small>
            </div>
            <div class="col-md-12"><?= format_comment($MESSAGES['msg'] ?? '') ?></div>
        </div>
    </div>

    <?php if (!empty($MESSAGES['previous'])) : ?>
        <p class="text-center"><?= e(__('HISTORY')) ?></p>
        <?php foreach ($MESSAGES['previous'] as $msg) : ?>
            <div class="tt_block rounded p-3">
                <div class="row">
                    <div class="col-12">
                        <?= coloredname((int) ($msg['sender'] ?? 0)) ?>
                        <small><?= e(__('POSTED_AT')) ?> <?= e($msg['added'] ?? '') ?></small>
                    </div>
                    <div class="col-md-12 p-2"><?= format_comment($msg['msg'] ?? '') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<form name="form" action="<?= url('message/submit') ?>" method="post">
    <?= csrf_field() ?>
    <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="receiver" value="<?= (int) ($MESSAGES['replyto'] ?? 0) ?>">
                    <input type="hidden" name="Update" value="create">
                    <input type="hidden" name="subject" value="<?= e($MESSAGES['subject'] ?? '') ?>">
                    <input type="hidden" name="history" value="<?= e((string) ($MESSAGES['history'] ?? '')) ?>">
                    <div class="text-center">
                        <?= e(__('REPLY')) ?>
                        <h4><b><?= coloredname((int) ($MESSAGES['replyto'] ?? 0)) ?></b></h4>
                        <?= e(__('SUBJECT')) ?> <?= e($MESSAGES['subject'] ?? '') ?>
                    </div>
                    <?php textbbcode('form', 'body'); ?>
                    <p class="text-center">
                        <label><?= e(__('SAVE_COPY')) ?></label>
                        <input type="checkbox" name="save" value="1" checked>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn ttbtn"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<form name="form1" action="<?= url('message/update/' . (int) ($MESSAGES['id'] ?? 0)) ?>" method="post">
    <?= csrf_field() ?>
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <?php textbbcode('form1', 'msg', (string) ($MESSAGES['msg'] ?? '')); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn ttbtn m-2" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn ttbtn m-2"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
block_end();
theme_footer();