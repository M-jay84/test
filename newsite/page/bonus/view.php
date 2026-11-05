<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\BonusLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

$BONUS          = BonusLogic::getAll();
$BONUS_INTERVAL = BonusLogic::interval();
$USER           = $current_user;

theme_header(__('BONUS'));
render_flash();
block_begin(__('BONUS'));
?>
<div class="table-responsive">
    <div class="text-center">
        <?= sprintf(__('SEEDBONUS_MESSAGE'), e((string) ($USER->seedbonus ?? '0'))) ?>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('OPTION')) ?></th>
                <th><?= e(__('TYPE')) ?></th>
                <th><?= e(__('POINTS')) ?></th>
                <th><?= e(__('EXCHANGE')) ?></th>
                <?php if (Auth::isStaff()) : ?>
                    <th><?= e(__('STAFF')) ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($BONUS as $row) : ?>
            <tr>
                <td><?= e($row['title'] ?? '') ?></td>
                <td><?= e($row['descr'] ?? '') ?></td>
                <td><?= e((string) ($row['cost'] ?? '')) ?></td>
                <td>
                    <form method="post" action="<?= url('bonus/submit') ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
                    </form>
                </td>
                <?php if (Auth::isStaff()) : ?>
                    <td>
                        <a href="<?= url('bonus/delete/' . (int) $row['id']) ?>"><i class="fa fa-trash"></i></a>
                        <a href="javascript:;" class="bonusAttr"
                           data-bs-toggle="modal" data-bs-target="#bonusModal"
                           data-id="<?= (int) $row['id'] ?>"
                           data-title="<?= e($row['title'] ?? '') ?>"
                           data-cost="<?= e((string) ($row['cost'] ?? '')) ?>"
                           data-type="<?= e($row['type'] ?? '') ?>"
                           data-descr="<?= e($row['descr'] ?? '') ?>"
                           data-value="<?= e((string) ($row['value'] ?? '')) ?>"
                           title="<?= e(__('EDIT')) ?>"><i class="fa fa-pencil"></i></a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="text-center">
        <?= sprintf(
            __('SEEDBONUS_REWARD'),
            e((string) (CONFIG['BONUSPERTIME'] ?? '')),
            e((string) $BONUS_INTERVAL)
        ) ?>
    </div>
</div>
<?php
block_end();

if (Auth::isStaff()) :
?>
    <p class="text-end">
        <button type="button" class="ttbtn" data-bs-toggle="modal" data-bs-target="#bonusModal">
            <i class="fa fa-user-secret" title="Admin"></i> <?= e(__('ADD')) ?>
        </button>
    </p>

    <script>
    document.querySelectorAll('.bonusAttr').forEach(function (el) {
        el.addEventListener('click', function () {
            document.getElementById('id').value = this.dataset.id || '';
            document.getElementById('title').value = this.dataset.title || '';
            document.getElementById('cost').value = this.dataset.cost || '';
            document.getElementById('type').value = this.dataset.type || '';
            document.getElementById('descr').value = this.dataset.descr || '';
            document.getElementById('value').value = this.dataset.value || '';
        });
    });
    </script>

    <form method="post" action="<?= url('bonus/update') ?>">
        <?= csrf_field() ?>
        <div class="modal fade" id="bonusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                <div class="modal-content">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id" value="">
                        <?= e(__('TITLE')) ?>
                        <input type="text" name="title" id="title" class="form-control fullwidth"><br>
                        <?= e(__('POINTS')) ?>
                        <input type="text" name="cost" id="cost" class="form-control fullwidth"><br>
                        <?= e(__('TYPE')) ?>
                        <select name="type" id="type" class="form-select">
                            <?php foreach (['invite', 'traffic', 'VIP', 'other', 'HnR'] as $t) : ?>
                                <option value="<?= e($t) ?>"><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select><br>
                        <?= e(__('VALUE')) ?>
                        <input type="text" name="value" id="value" class="form-control fullwidth"><br>
                        <?= e(__('DESCRIPTION')) ?>
                        <textarea name="descr" id="descr" class="form-control" style="height:150px;"></textarea>
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
endif;

theme_footer();