<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CountriesLogic;
use Bootstrap\Models\UsersLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if (($current_user->view_users ?? '') !== 'yes' && !Auth::isStaff()) {
    redirect('home', __('NO_USER_VIEW'), 'warning');
}

$users  = UsersLogic::getMembers();
$GROUPS = $users['groups'] ?? [];
$USERS  = $users['array'] ?? [];
$PAGER  = $users['pager'] ?? '';

theme_header(__('USERS'));
render_flash();
block_begin(__('USERS'));
?>
<div class="row tt_blockcenter rounded">
    <h3 class="text-center"><?= e(__('USERS')) ?></h3>
    <form method="get" action="<?= url('users/members') ?>">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Search" name="search"
                   value="<?= e((string) get('search', '')) ?>">
            <select name="class" class="form-select">
                <option value="-">(<?= e(__('ANY_CLASS')) ?>)</option>
                <?php foreach ($GROUPS as $row) : ?>
                    <option value="<?= (int) $row['group_id'] ?>"
                        <?= ((string) get('class', '') === (string) $row['group_id']) ? 'selected' : '' ?>>
                        <?= e($row['level'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <p class="text-center">
            <button type="submit" class="btn ttbtn btn-sm"><?= e(__('SUBMIT')) ?></button>
        </p>
    </form>
    <p class="text-center">
        <a href="<?= url('users/members') ?>"><b><?= e(__('ALL')) ?></b></a> -
        <?php
        $currentLetter = strtolower((string) get('letter', ''));
        foreach (range('a', 'z') as $l) {
            $L = strtoupper($l);
            if ($l === $currentLetter) {
                echo '<b>' . $L . '</b> ';
            } else {
                echo '<a href="' . url('users/members?letter=' . $l) . '"><b>' . $L . '</b></a> ';
            }
        }
        ?>
    </p>
</div><br>

<div class="table-responsive rounded">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('USERNAME')) ?></th>
                <th><?= e(__('REGISTERED')) ?></th>
                <th><?= e(__('LAST_ACCESS')) ?></th>
                <th><?= e(__('CLASS')) ?></th>
                <th><?= e(__('COUNTRY')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($USERS as $row) :
            $country = CountriesLogic::fetchRow($row['country'] ?? 0, 1);
            ?>
            <tr>
                <td>
                    <b><?= coloredname((int) $row['id']) ?></b>
                    <?= ((float) ($row['donated'] ?? 0) > 0)
                        ? "<i class='fa fa-star' style='color:orange' title='Donated'></i>"
                        : '' ?>
                </td>
                <td><?= e(user_timezone_date($row['added'] ?? '')) ?></td>
                <td><?= e(user_timezone_date($row['last_access'] ?? '')) ?></td>
                <td><?= e($row['level'] ?? '') ?></td>
                <td><?= $country /* HTML from model */ ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $PAGER ?>
<?php
block_end();
theme_footer();