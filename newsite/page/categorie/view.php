<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CatagoriesLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('categorie/view', 'Invalid request', 'warning');
    }
    CatagoriesLogic::insertOrUpdate();
    return;
}

$CATAGORIES = CatagoriesLogic::getAll();

theme_admin_header(__('CATEGORIES'));
render_flash();
block_begin(__('CATEGORIES'));
?>
<p class="text-center">
    <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><?= e(__('ADD')) ?></a>
</p>
<div class="table-responsive p-2">
    <p class="text-center"><i><?= e(__('CAT_IMAGE_HELP')) ?></i></p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="10"><?= e(__('SORT')) ?></th>
                <th><?= e(__('PARENT_CAT')) ?></th>
                <th><?= e(__('SUB_CAT')) ?></th>
                <th><?= e(__('ICON')) ?></th>
                <th><?= e(__('ACTION')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($CATAGORIES as $row) : ?>
            <tr>
                <td><?= e((string) ($row['sort_index'] ?? '')) ?></td>
                <td><?= e($row['parent_cat'] ?? '') ?></td>
                <td><?= e($row['name'] ?? '') ?></td>
                <td>
                    <?php if (!empty($row['image'])) : ?>
                        <i class="fa <?= e($row['image']) ?>" title="<?= e($row['name'] ?? '') ?>"></i>
                    <?php else : ?>-<?php endif; ?>
                </td>
                <td>
                    <a href="javascript:;" class="addAttr" data-bs-toggle="modal" data-bs-target="#addModal"
                       data-id="<?= (int) $row['id'] ?>"
                       data-sort_index="<?= e((string) ($row['sort_index'] ?? '')) ?>"
                       data-parent_cat="<?= e($row['parent_cat'] ?? '') ?>"
                       data-name="<?= e($row['name'] ?? '') ?>"
                       data-image="<?= e($row['image'] ?? '') ?>"><i class="fa fa-edit"></i></a>
                    <a href="<?= url('categorie/delete/' . (int) $row['id']) ?>"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
document.querySelectorAll('.addAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('sort_index').value = this.dataset.sort_index || '';
        document.getElementById('parent_cat').value = this.dataset.parent_cat || '';
        document.getElementById('name').value = this.dataset.name || '';
        document.getElementById('image').value = this.dataset.image || '';
    });
});
</script>
<form method="post" action="<?= url('categorie/view') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id" value="">
                    <input type="text" name="parent_cat" id="parent_cat" class="form-control fullwidth" placeholder="<?= e(__('PARENT_CAT')) ?>"><br>
                    <input type="text" name="name" id="name" class="form-control fullwidth" placeholder="<?= e(__('SUB_CAT')) ?>"><br>
                    <input type="text" name="sort_index" id="sort_index" class="form-control fullwidth" placeholder="<?= e(__('SORT')) ?>"><br>
                    <input type="text" name="image" id="image" class="form-control fullwidth" placeholder="eg fa-book">
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