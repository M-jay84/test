<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ForumsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$id   = (int) param(0, 0);
$move = param(1, false);
if ($move === 'up' || $move === 'down' || $move === true) {
    ForumsLogic::reorder($id, $move);
    redirect('forumcat/admin', __('ACTION_COMPLETE'), 'success');
}

$CATEGORY = ForumsLogic::getCats();

theme_admin_header(__('FORUMS'));
render_flash();
block_begin(__('FORUMS'));
?>
<div class="rounded">
    <div class="text-center">
        <b><?= e(__('FORUM_CATAGORY')) ?></b><br>
        <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#forumcatModal"><?= e(__('ADD')) ?></a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('ID')) ?></th>
                    <th><?= e(__('TITLE')) ?></th>
                    <th><?= e(__('SORT')) ?></th>
                    <th><?= e(__('MOVE')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($CATEGORY as $row) :
                $rid  = (int) $row['id'];
                $sort = (int) ($row['sort'] ?? 0);
                ?>
                <tr>
                    <td><?= $rid ?></td>
                    <td><a href="<?= url('forum/admin/' . $rid) ?>"><?= e($row['name'] ?? '') ?></a></td>
                    <td><?= $sort ?></td>
                    <td>
                        <?php if ($sort === 1) : ?>
                            <a href="<?= url("forumcat/admin/{$rid}/down") ?>"><i class="fa fa-arrow-down"></i></a>
                        <?php else : ?>
                            <a href="<?= url("forumcat/admin/{$rid}/up") ?>"><i class="fa fa-arrow-up"></i></a>
                            <a href="<?= url("forumcat/admin/{$rid}/down") ?>"><i class="fa fa-arrow-down"></i></a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="javascript:;" class="forumcatAttr" data-bs-toggle="modal" data-bs-target="#forumcatModal"
                           data-id="<?= $rid ?>" data-name="<?= e($row['name'] ?? '') ?>" data-sort="<?= $sort ?>">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="<?= url('forumcat/delete/' . $rid) ?>"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.forumcatAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('name').value = this.dataset.name || '';
        document.getElementById('sort').value = this.dataset.sort || '';
    });
});
</script>

<form method="post" action="<?= url('forumcat/submit') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="forumcatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id" value="">
                    <input class="form-control fullwidth" type="text" name="name" id="name" placeholder="<?= e(__('NAME')) ?>"><br>
                    <input class="form-control fullwidth" type="number" name="sort" id="sort" placeholder="<?= e(__('SORT')) ?>">
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