<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ForumsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$id      = (int) param(0, 0);
$forumid = (int) param(1, 0);
$move    = param(2, false);
if ($move === 'up' || $move === 'down' || $move === true) {
    ForumsLogic::reorderForum($id, $forumid, $move);
    redirect('forum/admin/' . $id, __('ACTION_COMPLETE'), 'success');
}

$forum  = ForumsLogic::getForums($id);
$CATS   = $forum['forumcat'] ?? [];
$FORUM  = $forum['forumforum'] ?? [];
$STMT   = $forum['query'] ?? [];
$GROUPS = $forum['groups'] ?? [];

theme_admin_header(__('FORUMS'));
render_flash();
block_begin(__('FORUMS'));
?>
<div class="rounded">
    <div class="text-center">
        <a class="ttbtn btn-sm" href="<?= url('forumcat/admin') ?>"><?= e(__('BACK')) ?></a>
        <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#forumModal"><?= e(__('ADD')) ?></a>
    </div>
    <div class="table-responsive p-1">
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
            <?php foreach ($STMT as $row) :
                $sort = (int) ($row['sort'] ?? 0);
                $cid  = (int) ($row['category'] ?? $id);
                $rid  = (int) ($row['id'] ?? 0);
                ?>
                <tr>
                    <td><?= $rid ?></td>
                    <td>
                        <?= e($row['name'] ?? '') ?><br>
                        <small><?= format_comment($row['description'] ?? '') ?></small>
                    </td>
                    <td><?= $sort ?></td>
                    <td>
                        <?php if ($sort === 1) : ?>
                            <a href="<?= url("forum/admin/{$cid}/{$rid}/down") ?>"><i class="fa fa-arrow-down"></i></a>
                        <?php else : ?>
                            <a href="<?= url("forum/admin/{$cid}/{$rid}/up") ?>"><i class="fa fa-arrow-up"></i></a>
                            <a href="<?= url("forum/admin/{$cid}/{$rid}/down") ?>"><i class="fa fa-arrow-down"></i></a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="javascript:;" class="forumAttr" data-bs-toggle="modal" data-bs-target="#forumModal"
                           data-id="<?= $rid ?>"
                           data-name="<?= e($row['name'] ?? '') ?>"
                           data-sort="<?= $sort ?>"
                           data-description="<?= e($row['description'] ?? '') ?>">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="<?= url('forum/delete/' . $rid) ?>"><i class="fa fa-trash tticon-red"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.forumAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('name').value = this.dataset.name || '';
        document.getElementById('sort').value = this.dataset.sort || '';
        document.getElementById('description').value = this.dataset.description || '';
    });
});
</script>

<form method="post" action="<?= url('forum/submit') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="forumModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id" value="">
                    <?= e(__('NAME')) ?>
                    <input type="text" class="form-control fullwidth" name="name" id="name"><br>
                    <?= e(__('SORT')) ?>
                    <input type="text" name="sort" id="sort" class="form-control fullwidth"><br>
                    <?= e(__('DESC')) ?>
                    <textarea name="description" id="description" class="form-control"></textarea><br>
                    <?= e(__('CAT')) ?>
                    <select name="new_forum_cat" class="form-select">
                        <?php foreach ($CATS as $row) : ?>
                            <option value="<?= (int) $row['id'] ?>"><?= e($row['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <?= e(__('SUB_FORUM_HELP')) ?>
                    <select name="sub" id="sub" class="form-select">
                        <option value="0"><?= e(__('NONE')) ?></option>
                        <?php foreach ($FORUM as $row) : ?>
                            <option value="<?= (int) $row['id'] ?>"><?= e($row['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <?= e(__('MIN_CLASS_READ')) ?>
                    <select name="minclassread" id="minclassread" class="form-select">
                        <?php foreach ($GROUPS as $group) : ?>
                            <option value="<?= (int) $group['group_id'] ?>"><?= e($group['level'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <?= e(__('MIN_CLASS_POST')) ?>
                    <select name="minclasswrite" id="minclasswrite" class="form-select">
                        <option value="1">Member</option>
                        <?php foreach ($GROUPS as $group) : ?>
                            <option value="<?= (int) $group['group_id'] ?>"><?= e($group['level'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <?= e(__('FORUM_ALLOW_GUEST_READ')) ?>
                    <label><input type="radio" name="guest_read" value="yes" checked> <?= e(__('YES')) ?></label>
                    <label><input type="radio" name="guest_read" value="no"> <?= e(__('NO')) ?></label>
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