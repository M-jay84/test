<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ClientsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$type = (string) param(0, 'peers'); // peers | client
$CLIENTS = ClientsLogic::select($type);
$TYPE = $type;

theme_admin_header(__('CLIENTS'));
render_flash();
block_begin(__('CLIENTS'));
?>
<div class="text-center">
    <a href="<?= url('client/view/peers') ?>"><?= e(__('CURRENT')) ?></a> -
    <a href="<?= url('client/view/client') ?>"><?= e(__('BANNED')) ?></a>
</div>
<?php if ($TYPE === 'client' || $TYPE === 'clients') : ?>
    <div class="text-center">
        <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#clientModal"><?= e(__('ADD')) ?></a>
    </div>
<?php endif; ?>
<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('CLIENT')) ?></th>
                <th><?= e(__('PEER_ID')) ?></th>
                <th><?= e(__('TO_BAN')) ?></th>
                <th><?= e(__('BANNED')) ?></th>
                <th><?= e(__('ACTION')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($CLIENTS as $row) :
            $peer = @hex2bin((string) ($row['peer_id'] ?? '')) ?: (string) ($row['peer_id'] ?? '');
            $code = substr($peer, 0, 8);
            $isbanned = (($row['banned'] ?? '') === 'yes')
                ? "<span style='color:green'><b>Yes</b></span>"
                : "<span style='color:red'><b>No</b></span>";
            ?>
            <tr>
                <td><?= e($row['type'] ?? '') ?></td>
                <td><?= e($peer) ?></td>
                <td><?= e($code) ?></td>
                <td><?= $isbanned ?></td>
                <td>
                    <?php if ((int) ($row['id'] ?? 0) === 0) : ?>
                        <a href="javascript:;" class="clientAttr" data-bs-toggle="modal" data-bs-target="#clientModal"
                           data-peer_id="<?= e($row['peer_id'] ?? '') ?>"
                           data-type="<?= e($row['type'] ?? '') ?>"><i class="fa fa-plus"></i></a>
                    <?php else : ?>
                        <a href="<?= url('client/delete/' . (int) $row['id']) ?>"><i class="fa fa-trash"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
document.querySelectorAll('.clientAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('type').value = this.dataset.type || '';
        document.getElementById('peer_id').value = this.dataset.peer_id || '';
    });
});
</script>
<form method="post" action="<?= url('client/submit') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="type" id="type">
                    <input type="text" name="peer_id" id="peer_id" class="form-control fullwidth" placeholder="<?= e(__('ENTER_CODE')) ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
block_end();
theme_admin_footer();