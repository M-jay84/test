<?php
declare(strict_types=1);

/**
 * Admin – user groups list + add/edit modal
 * URL: /group/view
 * POST same URL → GroupsLogic::insertOrUpdate()
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\GroupsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('group/view', __('INVALID_REQUEST'), 'warning');
    }
    GroupsLogic::insertOrUpdate(); // model redirects
    return;
}

$GROUPS = GroupsLogic::getGroups();

theme_admin_header(__('GROUPS'));
render_flash();
block_begin(__('GROUPS'));
?>
<p class="text-center">
    <a type="button"
       class="model_form ttbtn btn-sm"
       data-bs-toggle="modal"
       data-bs-target="#groupModal"><?= e(__('ADD')) ?></a>
</p>

<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('ID')) ?></th>
                <th><?= e(__('NAME')) ?></th>
                <th><?= e(__('PERMISSION_ID')) ?></th>
                <th><?= e(__('PERMISSION_NAME')) ?></th>
                <th><?= e(__('SORT')) ?></th>
                <th><?= e(__('DEL')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($GROUPS as $row) :
            $gid   = (int) ($row['group_id'] ?? 0);
            $color = ltrim((string) ($row['Color'] ?? '000000'), '#');
            ?>
            <tr>
                <td><?= $gid ?></td>
                <td>
                    <span style="color:#<?= e($color) ?>">
                        <?= e((string) ($row['level'] ?? '')) ?>
                    </span>
                </td>
                <td>
                    <a href="<?= e(url('permission/view')) ?>">
                        <?= e((string) ($row['group_role_id'] ?? '')) ?>
                    </a>
                </td>
                <td><?= e((string) ($row['permission_name'] ?? '')) ?></td>
                <td><?= e((string) ($row['staff_sort'] ?? '')) ?></td>
                <td>
                    <a href="<?= e(url('group/delete/' . $gid)) ?>">
                        <i class="fa fa-trash"></i>
                    </a>
                    <a href="javascript:;"
                       class="groupAttr"
                       data-bs-toggle="modal"
                       data-bs-target="#groupModal"
                       data-group_id="<?= $gid ?>"
                       data-group_color="<?= e((string) ($row['Color'] ?? '')) ?>"
                       data-level="<?= e((string) ($row['level'] ?? '')) ?>"
                       data-group_role_id="<?= e((string) ($row['group_role_id'] ?? '')) ?>"
                       data-maxslots="<?= e((string) ($row['maxslots'] ?? '')) ?>"
                       data-control_panel="<?= e((string) ($row['control_panel'] ?? 'no')) ?>"
                       data-staff_page="<?= e((string) ($row['staff_page'] ?? 'no')) ?>"
                       data-staff_public="<?= e((string) ($row['staff_public'] ?? 'no')) ?>"
                       data-staff_sort="<?= e((string) ($row['staff_sort'] ?? '')) ?>">
                        <i class="fa fa-edit"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.groupAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('group_id').value = this.dataset.group_id || '';
        document.getElementById('group_color').value = this.dataset.group_color || '';
        document.getElementById('level').value = this.dataset.level || '';
        document.getElementById('group_role_id').value = this.dataset.group_role_id || '';
        document.getElementById('maxslots').value = this.dataset.maxslots || '';
        document.getElementById('control_panel').value = this.dataset.control_panel || 'no';
        document.getElementById('staff_page').value = this.dataset.staff_page || 'no';
        document.getElementById('staff_public').value = this.dataset.staff_public || 'no';
        document.getElementById('staff_sort').value = this.dataset.staff_sort || '';
    });
});
document.querySelector('[data-bs-target="#groupModal"].model_form')?.addEventListener('click', function () {
    ['group_id', 'group_color', 'level', 'group_role_id', 'maxslots', 'staff_sort'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('control_panel').value = 'no';
    document.getElementById('staff_page').value = 'no';
    document.getElementById('staff_public').value = 'no';
});
</script>

<form id="submit" method="post" action="<?= e(url('group/view')) ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="group_id" id="group_id" value="">

                    <?= e(__('NAME')) ?>
                    <input type="text" name="level" id="level" class="form-control fullwidth"><br>

                    <?= e(__('COLOUR')) ?>
                    <input type="text" name="group_color" id="group_color" class="form-control fullwidth"
                           placeholder="RRGGBB"><br>

                    <?= e(__('ROLE_ID')) ?>
                    <input type="text" name="group_role_id" id="group_role_id" class="form-control fullwidth"
                           placeholder="1"><br>

                    <?= e(__('SLOTS')) ?>
                    <input type="number" name="maxslots" id="maxslots" class="form-control fullwidth"><br>

                    <?= e(__('CAN_VIEW_CP')) ?>
                    <select name="control_panel" id="control_panel" class="form-select">
                        <option value="no"><?= e(__('NO')) ?></option>
                        <option value="yes"><?= e(__('YES')) ?></option>
                    </select><br>

                    <?= e(__('STAFF_PAGE')) ?>
                    <select name="staff_page" id="staff_page" class="form-select">
                        <option value="no"><?= e(__('NO')) ?></option>
                        <option value="yes"><?= e(__('YES')) ?></option>
                    </select><br>

                    <?= e(__('STAFF_PUBLIC')) ?>
                    <select name="staff_public" id="staff_public" class="form-select">
                        <option value="no"><?= e(__('NO')) ?></option>
                        <option value="yes"><?= e(__('YES')) ?></option>
                    </select><br>

                    <?= e(__('STAFF_SORT')) ?>
                    <input type="text" name="staff_sort" id="staff_sort" class="form-control fullwidth">
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