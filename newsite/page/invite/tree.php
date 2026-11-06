<?php
declare(strict_types=1);

/**
 * URL: /invite/tree/{id}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\InvitesLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (empty(CONFIG['INVITEONLY']) && empty(CONFIG['ENABLEINVITES'])) {
    redirect('home', __('INVITES_DISABLED_MSG'), 'warning');
}

$id = (int) param(0, 0);
if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

$invites = InvitesLogic::InviteTree($id);

// Prefer model returning rows array; fall back if still a statement
$TITLE = strip_tags((string) ($invites['title'] ?? __('INVITES')));
$ROWS  = $invites['rows'] ?? [];

if ($ROWS === [] && isset($invites['res']) && $invites['res'] instanceof \PDOStatement) {
    $ROWS = $invites['res']->fetchAll(\PDO::FETCH_ASSOC);
}

theme_header($TITLE);
render_flash();
block_begin($TITLE);
?>
<div class="table-responsive p-2">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('USER')) ?></th>
                <th><?= e(__('CLASS')) ?></th>
                <th><?= e(__('REGISTERED')) ?></th>
                <th><?= e(__('LAST_ACCESS')) ?></th>
                <th><?= e(__('DOWNLOADED')) ?></th>
                <th><?= e(__('UPLOADED')) ?></th>
                <th><?= e(__('RATIO')) ?></th>
                <th><?= e(__('WARNED')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ROWS as $arr) :
            $downloaded = (float) ($arr['downloaded'] ?? 0);
            $uploaded   = (float) ($arr['uploaded'] ?? 0);
            $userratio  = $downloaded > 0
                ? number_format($uploaded / $downloaded, 1)
                : '---';
            $warned = (($arr['warned'] ?? '') === 'yes')
                ? '<span style="color:red"><b>' . e(__('YES')) . '</b></span>'
                : '<span style="color:limegreen"><b>' . e(__('NO')) . '</b></span>';
            $added = substr((string) ($arr['added'] ?? ''), 0, 10);
            $last  = substr((string) ($arr['last_access'] ?? ''), 0, 10);
            if (($arr['added'] ?? '') === '0000-00-00 00:00:00' || $added === '') {
                $added = '---';
            }
            if (($arr['last_access'] ?? '') === '0000-00-00 00:00:00' || $last === '') {
                $last = '---';
            }
            ?>
            <tr>
                <td><b><?= coloredname((int) ($arr['id'] ?? 0)) ?></b></td>
                <td><?= e(get_user_class_name($arr['class'] ?? 0)) ?></td>
                <td><?= e($added) ?></td>
                <td><?= e($last) ?></td>
                <td><?= mksize((int) $downloaded) ?></td>
                <td><?= mksize((int) $uploaded) ?></td>
                <td><?= get_ratio_color($userratio) ?></td>
                <td><?= $warned ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
block_end();
theme_footer();