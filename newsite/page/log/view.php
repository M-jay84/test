<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\LogsLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$data  = LogsLogic::getAll();
$LOGS  = $data['logs'] ?? [];
$PAGER = $data['pager'] ?? '';
$search = (string) ($data['search'] ?? '');

theme_admin_header(__('SITE_LOG'));
render_flash();
block_begin(__('SITE_LOG'));

form_start(url('log/view'), 'post');
echo csrf_field();
echo '<p class="tt_blockcenter p-2 rounded text-center">';
echo e(__('SEARCH')) . ': ';
echo '<input type="text" size="30" name="search" value="' . e($search) . '"> ';
button(__('SEARCH'), 'submit', 'btn btn-sm ttbtn');
echo '</p>';
form_end();
?>
<form id="sitelog" action="<?= e(url('log/submit')) ?>" method="post">
    <?= csrf_field() ?>
    <div class="table-responsive p-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('EVENT')) ?></th>
                    <th><?= e(__('DATE')) ?></th>
                    <th><?= e(__('TIME')) ?></th>
                    <th><input type="checkbox" name="checkall" onclick="checkAll(this.form.id)"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($LOGS as $row) :
                $added = user_timezone_date($row['added'] ?? '');
                $date = $added;
                $time = '';
                if (str_contains($added, ' ')) {
                    [$date, $time] = explode(' ', $added, 2);
                }
                ?>
                <tr>
                    <td><?= format_comment((string) ($row['txt'] ?? '')) ?></td>
                    <td><?= e($date) ?></td>
                    <td><?= e($time) ?></td>
                    <td>
                        <input type="checkbox" class="ttbtn" name="ids[]" value="<?= (int) ($row['id'] ?? 0) ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
    </div>
</form>
<?= $PAGER ?>
<?php
block_end();
theme_admin_footer();