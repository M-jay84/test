<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersearchLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$current_user = Auth::user();

if (is_post() && (string) post('action', '0') === '1') {
    if (!csrf_verify()) {
        redirect('users/search', __('INVALID_REQUEST'), 'warning');
    }
    if (empty($_POST['ids'])) {
        redirect('users/search', __('MISSING_ID'), 'warning');
    }
    UsersearchLogic::submitUser($current_user);
    redirect('users/search', __('SUCCESS'), 'success');
}

$results = UsersearchLogic::searchUsers();
$rows    = $results['results'] ?? [];
$pager   = $results['pager'] ?? '';
$input   = $results['inputs'] ?? [];
$groups  = $results['groups'] ?? [];

theme_admin_header(__('USER_SEARCH'));
render_flash();
block_begin(__('USER_SEARCH'));
?>
<div class="rounded p-2">
    <form id="searchall" method="post" action="<?= e(url('users/search')) ?>">
        <?= csrf_field() ?>
        <div class="form-group row">
            <label class="col-sm-1 col-form-label"><?= e(__('NAME')) ?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="name" value="<?= e((string) ($input['name'] ?? '')) ?>">
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('RATIO')) ?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="ratio" value="<?= e((string) ($input['ratio'] ?? '')) ?>">
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('STATUS')) ?></label>
            <div class="col-sm-3">
                <select name="member" class="form-select">
                    <option value="">--</option>
                    <option value="confirmed" <?= (($input['member'] ?? '') === 'confirmed') ? 'selected' : '' ?>><?= e(__('CONFIRMED')) ?></option>
                    <option value="pending" <?= (($input['member'] ?? '') === 'pending') ? 'selected' : '' ?>><?= e(__('PENDING')) ?></option>
                </select>
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('EMAIL')) ?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="email" value="<?= e((string) ($input['email'] ?? '')) ?>">
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('UPLOADED')) ?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="uploaded" value="<?= e((string) ($input['uploaded'] ?? '')) ?>">
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('ENABLED')) ?></label>
            <div class="col-sm-3">
                <select name="account" class="form-select">
                    <option value="">--</option>
                    <option value="yes" <?= (($input['account'] ?? '') === 'yes') ? 'selected' : '' ?>><?= e(__('YES')) ?></option>
                    <option value="no" <?= (($input['account'] ?? '') === 'no') ? 'selected' : '' ?>><?= e(__('NO')) ?></option>
                </select>
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('IP')) ?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="ip" value="<?= e((string) ($input['ip'] ?? '')) ?>">
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('DOWNLOADED')) ?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" name="downloaded" value="<?= e((string) ($input['downloaded'] ?? '')) ?>">
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('CLASS')) ?></label>
            <div class="col-sm-3">
                <select name="class" class="form-select">
                    <option value=""><?= e(__('ANY')) ?></option>
                    <?php foreach ($groups as $row) : ?>
                        <option value="<?= (int) $row['group_id'] ?>"
                            <?= ((string) ($input['class'] ?? '') === (string) $row['group_id']) ? 'selected' : '' ?>>
                            <?= e($row['level'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <label class="col-sm-1 col-form-label"><?= e(__('WARNED')) ?></label>
            <div class="col-sm-3">
                <select name="warned" class="form-select">
                    <option value="">--</option>
                    <option value="yes" <?= (($input['warned'] ?? '') === 'yes') ? 'selected' : '' ?>><?= e(__('YES')) ?></option>
                    <option value="no" <?= (($input['warned'] ?? '') === 'no') ? 'selected' : '' ?>><?= e(__('NO')) ?></option>
                </select>
            </div>
        </div>
        <br>
        <p class="text-center">
            <a href="<?= e(url('users/search')) ?>" class="btn ttbtn"><?= e(__('RESET')) ?></a>
            <button type="submit" class="btn ttbtn"><?= e(__('SUBMIT')) ?></button>
        </p>
    </form>
</div>
<br>

<form id="searchresult" method="post" action="<?= e(url('users/search')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="1">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('NAME')) ?></th>
                    <th><?= e(__('IP')) ?></th>
                    <th><?= e(__('EMAIL')) ?></th>
                    <th><?= e(__('JOINED')) ?></th>
                    <th><?= e(__('LAST_SEEN')) ?></th>
                    <th><?= e(__('STATUS')) ?></th>
                    <th><?= e(__('ENABLED')) ?></th>
                    <th><?= e(__('RATIO')) ?></th>
                    <th><?= e(__('UPLOADED')) ?></th>
                    <th><?= e(__('DOWNLOADED')) ?></th>
                    <th><?= e(__('CLASS')) ?></th>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id);"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row) :
                $down = (float) ($row['downloaded'] ?? 0);
                $up   = (float) ($row['uploaded'] ?? 0);
                $ratio = $down > 0 ? number_format($up / $down, 1) : '---';
                ?>
                <tr>
                    <td><b><?= coloredname((int) $row['id']) ?></b></td>
                    <td><?= e($row['ip'] ?? '') ?></td>
                    <td><?= e($row['email'] ?? '') ?></td>
                    <td><?= e(user_timezone_date($row['added'] ?? '')) ?></td>
                    <td><?= e(user_timezone_date($row['last_access'] ?? '')) ?></td>
                    <td><?= e($row['status'] ?? '') ?></td>
                    <td><?= e($row['enabled'] ?? '') ?></td>
                    <td><?= get_ratio_color($ratio) ?></td>
                    <td><?= e(mksize((int) $up)) ?></td>
                    <td><?= e(mksize((int) $down)) ?></td>
                    <td><?= e((string) ($row['class'] ?? '')) ?></td>
                    <td><input type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $pager ?>
    <div class="tt_blockcenter rounded p-1 text-center">
        <button type="submit" name="enable" value="1" class="btn btn-success btn-sm"><?= e(__('ENABLE')) ?></button>
        <button type="submit" name="disable" value="1" class="btn btn-danger btn-sm"><?= e(__('DISABLE')) ?></button>
        <button type="submit" name="warn" value="1" class="btn btn-danger btn-sm"><?= e(__('WARN')) ?></button>
        <button type="submit" name="unwarn" value="1" class="btn btn-success btn-sm"><?= e(__('UNWARN')) ?></button><br>
        <?= e(__('MOD_COMMENT')) ?><br>
        <input type="text" name="add_comment">
    </div>
</form>
<?php
block_end();
theme_admin_footer();