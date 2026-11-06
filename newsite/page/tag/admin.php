<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TagsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post() && !isset($_POST['ids'])) {
    csrf_verify() || redirect('tag/admin', __('INVALID_REQUEST'), 'warning');
    TagsLogic::insertOrUpdate();
    return;
}

$TAGS = TagsLogic::getAll();

theme_admin_header(__('TAGS'));
render_flash();
block_begin(__('TAGS'));
?>
<p class="text-center">
    <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#tagModal">
        <?= e(__('ADD')) ?>
    </a>
</p>

<form id="delete" action="<?= e(url('tag/delete')) ?>" method="post">
    <?= csrf_field() ?>
    <div class="table-responsive p-2">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id)"></th>
                    <th><?= e(__('NAME')) ?></th>
                    <th><?= e(__('COLOUR')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($TAGS as $row) : ?>
                <tr>
                    <td>
                        <a href="javascript:;" class="tagAttr" data-bs-toggle="modal" data-bs-target="#tagModal"
                           data-id="<?= (int) ($row['id'] ?? 0) ?>"
                           data-name="<?= e((string) ($row['name'] ?? '')) ?>"
                           data-colour="<?= e((string) ($row['colour'] ?? '')) ?>">
                            <i class="fa fa-edit"></i>
                        </a>
                        <input type="checkbox" name="ids[]" value="<?= (int) ($row['id'] ?? 0) ?>">
                    </td>
                    <td><?= e((string) ($row['name'] ?? '')) ?></td>
                    <td><?= e((string) ($row['colour'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-center">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('DEL')) ?></button>
    </p>
</form>

<script>
document.querySelectorAll('.tagAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('name').value = this.dataset.name || '';
        document.getElementById('colour').value = this.dataset.colour || '';
    });
});
</script>

<form method="post" action="<?= e(url('tag/admin')) ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="tagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id" value="">
                    <label for="name"><?= e(__('NAME')) ?></label>
                    <input id="name" type="text" class="form-control fullwidth" name="name" minlength="3" maxlength="200">
                    <label for="colour"><?= e(__('COLOUR')) ?></label>
                    <input id="colour" type="text" class="form-control fullwidth" name="colour" minlength="3" maxlength="200">
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