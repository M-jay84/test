<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CountriesLogic;
use Bootstrap\Models\UsersLogic;

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (($current_user->view_users ?? '') !== 'yes' && !Auth::isStaff()) {
    redirect('home', __('NO_USER_VIEW'), 'warning');
}

$data   = UsersLogic::getMembers();
$groups = $data['groups'] ?? [];
$users  = $data['array'] ?? [];
$pager  = $data['pager'] ?? '';

$search        = (string) get('search', '');
$classFilter   = (string) get('class', '');
$currentLetter = strtolower((string) get('letter', ''));

theme_header(__('USERS'));
render_flash();
block_begin(__('USERS'));
?>
<div class="row tt_blockcenter rounded">
    <h3 class="text-center"><?= e(__('USERS')) ?></h3>
    <form method="get" action="<?= e(url('users/members')) ?>">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="<?= e(__('SEARCH')) ?>"
                   name="search" value="<?= e($search) ?>">
            <select name="class" class="form-select">
                <option value="-">(<?= e(__('ANY_CLASS')) ?>)</option>
                <?php foreach ($groups as $row) : ?>
                    <option value="<?= (int) $row['group_id'] ?>"
                        <?= $classFilter === (string) $row['group_id'] ? 'selected' : '' ?>>
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
        <a href="<?= e(url('users/members')) ?>"><b><?= e(__('ALL')) ?></b></a> -
        <?php foreach (range('a', 'z') as $l) :
            $L = strtoupper($l);
            if ($l === $currentLetter) {
                echo '<b>' . e($L) . '</b> ';
            } else {
                echo '<a href="' . e(url('users/members?letter=' . $l)) . '"><b>' . e($L) . '</b></a> ';
            }
        endforeach; ?>
    </p>
</div>
<br>

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
        <?php foreach ($users as $row) :
            $country = CountriesLogic::fetchRow($row['country'] ?? 0, 1);
            ?>
            <tr>
                <td>
                    <b><?= coloredname((int) $row['id']) ?></b>
                    <?= ((float) ($row['donated'] ?? 0) > 0)
                        ? "<i class='fa fa-star' style='color:orange' title='" . e(__('DONATED')) . "'></i>"
                        : '' ?>
                </td>
                <td><?= e(user_timezone_date($row['added'] ?? '')) ?></td>
                <td><?= e(user_timezone_date($row['last_access'] ?? '')) ?></td>
                <td><?= e($row['level'] ?? '') ?></td>
                <td><?= $country ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $pager ?>
<?php
block_end();
theme_footer();