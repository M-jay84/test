<?php
declare(strict_types=1);

/**
 * Admin – inactive users list
 * URL: /inactive/view/{days?}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

// Optional: change day threshold via POST
if (is_post() && (string) post('action', '') === 'cday') {
    if (!csrf_verify()) {
        redirect('inactive/view', __('INVALID_REQUEST'), 'warning');
    }
    // Model reads cday from POST when listing
}

$users = UsersLogic::inactiveUsers();
$DAYS  = (int) ($users['days'] ?? 30);
$USERS = $users['users'] ?? [];
$COUNT = (int) ($users['count'] ?? count($USERS));

theme_admin_header(__('INACTIVE'));
render_flash();
block_begin(__('INACTIVE'));
?>
<div class="tt_blockcenter rounded p-2 text-center">
    <form action="<?= e(url('inactive/view')) ?>" method="post">
        <?= csrf_field() ?>
        <?= e(__('DAYS')) ?>
        <input type="text" name="cday" size="10" value="<?= e((string) $DAYS) ?>" maxlength="3">
        <input type="hidden" name="action" value="cday">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
    </form>
</div>

<h5 class="text-center">
    <?= $COUNT ?> <?= e(__('INACTIVE_FOR')) ?> <?= $DAYS ?> <?= e(__('DAYS')) ?>
</h5>

<form id="inactive" action="<?= e(url('inactive/submit')) ?>" method="post">
    <?= csrf_field() ?>
    <div class="table-responsive p-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('USERNAME')) ?></th>
                    <th><?= e(__('CLASS')) ?></th>
                    <th><?= e(__('IP')) ?></th>
                    <th><?= e(__('RATIO')) ?></th>
                    <th><?= e(__('JOINED')) ?></th>
                    <th><?= e(__('LAST_VISIT')) ?></th>
                    <th>
                        <input type="checkbox" name="checkall" onclick="checkAll(this.form.id);">
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($USERS as $row) :
                $downloaded = (float) ($row['downloaded'] ?? 0);
                $uploaded   = (float) ($row['uploaded'] ?? 0);
                $userratio  = $downloaded > 0
                    ? number_format($uploaded / $downloaded, 1)
                    : '---';
                $lastSeen = empty($row['last_access'])
                    ? __('NEVER')
                    : get_elapsed_time(strtotime((string) $row['last_access'])) . ' ' . __('AGO');
                $joined = (string) ($row['added'] ?? '');
                if (str_contains($joined, ' ')) {
                    $joined = substr($joined, 0, (int) strpos($joined, ' '));
                }
                $ip = ($row['ip'] !== '' && $row['ip'] !== null) ? (string) $row['ip'] : '----';
                ?>
                <tr>
                    <td><?= coloredname((int) ($row['id'] ?? 0)) ?></td>
                    <td><?= e(get_user_class_name((int) ($row['class'] ?? 0))) ?></td>
                    <td><?= e($ip) ?></td>
                    <td>
                        <b><?= get_ratio_color($userratio) ?></b>
                        <b>DL: <?= mksize((int) $downloaded) ?></b> |
                        <b>Up: <?= mksize((int) $uploaded) ?></b>
                    </td>
                    <td><?= e($joined) ?></td>
                    <td><?= e($lastSeen) ?></td>
                    <td>
                        <input type="checkbox" name="users[]" value="<?= (int) ($row['id'] ?? 0) ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-center p-2">
            <select name="action" class="form-select" style="max-width:220px;display:inline-block;">
                <option value="mail"><?= e(__('PM')) ?></option>
                <option value="deluser"><?= e(__('DELETEUSERS')) ?></option>
                <option value="disable"><?= e(__('DISABLED_ACCOUNTS')) ?></option>
            </select>
            <button type="submit" class="btn btn-sm ttbtn" name="submit" value="1"><?= e(__('SUBMIT')) ?></button>
        </div>
    </div>
</form>
<?php
block_end();
theme_admin_footer();