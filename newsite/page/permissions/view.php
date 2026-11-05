<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\PermissionsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('permissions/view', 'Invalid request', 'warning');
    }
    PermissionsLogic::insertOrUpdate();
    return;
}

$PERMISSIONS = PermissionsLogic::getAll();

theme_admin_header(__('GROUPS'));
render_flash();
block_begin(__('GROUPS'));
?>
<p class="text-center">
    <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#groupModal"><?= e(__('ADD')) ?></a>
</p>
<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('PERMISSION_ID')) ?></th>
                <th><?= e(__('NAME')) ?></th>
                <th><?= e(__('TORRENTS')) ?><br><?= e(__('VIEW_EDIT_DEL')) ?></th>
                <th><?= e(__('MEMBERS')) ?><br><?= e(__('VIEW_EDIT_DEL')) ?></th>
                <th><?= e(__('NEWS')) ?><br><?= e(__('VIEW_EDIT_DEL')) ?></th>
                <th><?= e(__('FORUM')) ?><br><?= e(__('VIEW_EDIT_DEL')) ?></th>
                <th><?= e(__('UPLOAD')) ?></th>
                <th><?= e(__('DOWNLOAD')) ?></th>
                <th><?= e(__('DEL')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($PERMISSIONS as $row) : ?>
            <tr>
                <td><?= (int) ($row['permission_id'] ?? 0) ?></td>
                <td><?= e($row['permission_name'] ?? '') ?></td>
                <td><?= e($row['view_torrents'] ?? '') ?>/<?= e($row['edit_torrents'] ?? '') ?>/<?= e($row['delete_torrents'] ?? '') ?></td>
                <td><?= e($row['view_users'] ?? '') ?>/<?= e($row['edit_users'] ?? '') ?>/<?= e($row['delete_users'] ?? '') ?></td>
                <td><?= e($row['view_news'] ?? '') ?>/<?= e($row['edit_news'] ?? '') ?>/<?= e($row['delete_news'] ?? '') ?></td>
                <td><?= e($row['view_forum'] ?? '') ?>/<?= e($row['edit_forum'] ?? '') ?>/<?= e($row['delete_forum'] ?? '') ?></td>
                <td><?= e($row['can_upload'] ?? '') ?></td>
                <td><?= e($row['can_download'] ?? '') ?></td>
                <td>
                    <a href="<?= url('permissions/delete/' . (int) ($row['permission_id'] ?? 0)) ?>"><i class="fa fa-trash"></i></a>
                    <a href="javascript:;" class="groupAttr" data-bs-toggle="modal" data-bs-target="#groupModal"
                       <?php
                       foreach ([
                           'permission_id', 'permission_name', 'view_torrents', 'edit_torrents', 'delete_torrents',
                           'view_users', 'edit_users', 'delete_users', 'view_news', 'edit_news', 'delete_news',
                           'view_forum', 'edit_forum', 'delete_forum', 'can_upload', 'can_download',
                       ] as $k) {
                           echo ' data-' . $k . '="' . e((string) ($row[$k] ?? '')) . '"';
                       }
                       ?>
                    ><i class="fa fa-edit"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.groupAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        ['permission_id','permission_name','view_torrents','edit_torrents','delete_torrents',
         'view_users','edit_users','delete_users','view_news','edit_news','delete_news',
         'view_forum','edit_forum','delete_forum','can_upload','can_download'].forEach(function (k) {
            var node = document.getElementById(k);
            if (node) node.value = el.dataset[k] || '';
        });
    });
});
</script>

<form method="post" action="<?= url('permissions/view') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="permission_id" id="permission_id" value="">
                    <?= e(__('NAME')) ?>
                    <input type="text" name="permission_name" id="permission_name" class="form-control fullwidth"><br>
                    <?php
                    $yesNo = [
                        'view_torrents' => 'VIEW_TORRENTS', 'edit_torrents' => 'EDIT_TORRENTS', 'delete_torrents' => 'DELETE_TORRENTS',
                        'view_users' => 'VIEW_USERS', 'edit_users' => 'EDIT_USERS', 'delete_users' => 'DELETE_USERS',
                        'view_news' => 'VIEW_NEWS', 'edit_news' => 'EDIT_NEWS', 'delete_news' => 'DELETE_NEWS',
                        'view_forum' => 'VIEW_FORUM', 'edit_forum' => 'EDIT_FORUM', 'delete_forum' => 'DELETE_FORUM',
                        'can_upload' => 'CAN_UPLOAD', 'can_download' => 'CAN_DOWNLOAD',
                    ];
                    foreach ($yesNo as $field => $langKey) :
                        ?>
                        <?= e(__($langKey)) ?>
                        <select name="<?= $field ?>" id="<?= $field ?>" class="form-select">
                            <option value="yes"><?= e(__('YES')) ?></option>
                            <option value="no"><?= e(__('NO')) ?></option>
                        </select><br>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SAVE')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
block_end();
theme_admin_footer();