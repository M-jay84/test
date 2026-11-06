<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\MessagesLogic;

$current_user = Auth::user();
$type = (string) param(0, 'inbox');
if (!in_array($type, ['inbox', 'outbox'], true)) {
    $type = 'inbox';
}

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$messages  = MessagesLogic::msgPagination($type, (int) $current_user->id);
$MESSAGES  = $messages['messages'] ?? [];
$PAGER     = $messages['pager'] ?? '';
$TYPE      = $type;

theme_header(ucfirst($type));
render_flash();
block_begin(ucfirst($type));
?>
<form id="messages" method="post" action="<?= url('message/delete/' . $TYPE) ?>">
    <?= csrf_field() ?>
    <div class="table-responsive rounded">
        <p class="text-center">
            <a href="<?= url('message/mailbox/inbox') ?>"><button type="button" class="<?= e(activelink('inbox', 'inbox')) ?>"><?= e(__('INBOX')) ?></button></a>
            <a href="<?= url('message/mailbox/outbox') ?>"><button type="button" class="<?= e(activelink('outbox', 'outbox')) ?>"><?= e(__('OUTBOX')) ?></button></a>
            <a href="<?= url('message/create') ?>"><button type="button" class="<?= e(activelink('message/create')) ?>"><?= e(__('COMPOSE')) ?></button></a>
        </p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);"></th>
                    <th><?= e(__('READ')) ?></th>
                    <th><?= e(__('SENDER')) ?></th>
                    <th><?= e(__('RECEIVER')) ?></th>
                    <th><?= e(__('SUBJECT')) ?></th>
                    <th><?= e(__('DATE')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($MESSAGES as $arr) :
                $msgdetails = msgdetails($TYPE, $arr);
                ?>
                <tr>
                    <td><input type="checkbox" name="del[]" value="<?= (int) $arr['id'] ?>"></td>
                    <td><?= $msgdetails['unread'] ?></td>
                    <td><?= $msgdetails['sender'] ?></td>
                    <td><?= $msgdetails['receiver'] ?></td>
                    <td><?= $msgdetails['subject'] ?></td>
                    <td><?= e($msgdetails['added']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="float:left;">
        <?= e(__('READ')) ?> <i class="fa fa-file-text"></i>
        <?= e(__('UNREAD')) ?> <i class="fa fa-file-text tticon-red"></i>
    </div>
    <div class="text-center">
        <button type="submit" class="btn ttbtn" name="delete" value="1"><?= e(__('DEL')) ?></button>
        <?php if ($TYPE === 'inbox') : ?>
            <button type="submit" class="btn ttbtn" name="read" value="1"><?= e(__('READ')) ?></button>
        <?php endif; ?>
    </div>
</form>
<?= $PAGER ?>
<?php
block_end();
theme_footer();