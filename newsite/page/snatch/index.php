<?php
declare(strict_types=1);

/**
 * URL: /snatch/index/{id}/{type}
 * type: complete | torrent | user
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\SnatchedLogic;

$current_user = Auth::user();
$id   = (int) param(0, 0);
$type = (string) param(1, 'user');

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

// Original condition was inverted; tighten as needed
if (
    ($current_user->view_torrents ?? '') !== 'yes'
    && $id !== (int) $current_user->id
    && !Auth::isStaff()
) {
    redirect('home', __('NO_TORRENT_VIEW'), 'warning');
}

if ($type === 'complete') {
    $snatched = SnatchedLogic::join('snatched.tid = ? AND snatched.completed != 0', $id);
    $cols = 'torrent';
} elseif ($type === 'torrent') {
    $snatched = SnatchedLogic::join('snatched.tid = ?', $id);
    $cols = 'torrent';
} else {
    $snatched = SnatchedLogic::join('snatched.uid = ?', $id);
    $cols = 'user';
}

$SNATCHED = $snatched['stmt'] ?? [];
$PAGER    = $snatched['pager'] ?? '';
$COLS     = $cols;

theme_header(__('SNATCH_RESULT'));
render_flash();
block_begin(__('SNATCH_RESULT'));
?>
<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('NAME')) ?></th>
                <th><?= e(__('UPLOADED')) ?></th>
                <th><?= e(__('DOWNLOADED')) ?></th>
                <th><?= e(__('RATIO')) ?></th>
                <th><?= e(__('ADDED')) ?></th>
                <th><?= e(__('LAST_ACTION')) ?></th>
                <th><?= e(__('SEED_TIME')) ?></th>
                <th><?= e(__('COMPLETED')) ?></th>
                <th><?= e(__('SEEDING')) ?></th>
                <th><?= e(__('HNR')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($SNATCHED as $row) :
            $userratio = ($row['download'] ?? 0) > 0
                ? number_format(($row['upload'] ?? 0) / $row['download'], 1)
                : '---';
            // Keep your original yes/no colour logic (it looked inverted — verify against DB values)
            $complete = (($row['completed'] ?? '') === 'yes')
                ? '<span style="color:#27B500"><b>' . e(__('YES')) . '</b></span>'
                : '<span style="color:#FF1200"><b>' . e(__('NO')) . '</b></span>';
            $seeding = (($row['seeding'] ?? '') === 'yes')
                ? '<span style="color:#27B500"><b>' . e(__('YES')) . '</b></span>'
                : '<span style="color:#FF1200"><b>' . e(__('NO')) . '</b></span>';
            $hnr = (($row['hnr'] ?? '') === 'yes')
                ? '<span style="color:#FF1200"><b>' . e(__('YES')) . '</b></span>'
                : '<span style="color:#27B500"><b>' . e(__('NO')) . '</b></span>';
            $seedtime = !empty($row['last_time']) ? get_elapsed_time($row['last_time']) : '---';
            $name = ($COLS === 'user')
                ? htmlspecialchars(CutName($row['name'] ?? '', 35))
                : coloredname((int) ($row['id'] ?? 0));
            ?>
            <tr>
                <td><b><?= $name ?></b></td>
                <td><span style="color:#27B500"><?= mksize($row['upload'] ?? 0) ?></span></td>
                <td><span style="color:#FF1200"><?= mksize($row['download'] ?? 0) ?></span></td>
                <td><?= get_ratio_color($userratio) ?></td>
                <td><?= e(user_timezone_date(date('Y-m-d H:i:s', (int) ($row['start_time'] ?? 0)))) ?></td>
                <td><?= e(user_timezone_date(date('Y-m-d H:i:s', (int) ($row['upload_time'] ?? 0)))) ?></td>
                <td><?= e($seedtime) ?></td>
                <td><?= $complete ?></td>
                <td><?= $seeding ?></td>
                <td><?= $hnr ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $PAGER ?>
<?php
block_end();
theme_footer();