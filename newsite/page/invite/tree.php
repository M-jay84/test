<?php
declare(strict_types=1);

/**
 * URL: /invite/tree/{id}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\InvitesLogic;
use PDO;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

if (empty(CONFIG['INVITEONLY']) && empty(CONFIG['ENABLEINVITES'])) {
    redirect('home', __('INVITES_DISABLED_MSG'), 'warning');
}

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

$invites = InvitesLogic::InviteTree($id);

$TITLE   = $invites['title'] ?? __('INVITES');
$INVITES = $invites['res'];      // PDOStatement
$COUNT   = (int) ($invites['num'] ?? 0);

theme_header(strip_tags((string) $TITLE));
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
        <?php
        // Prefer foreach if model returns fetchAll; keep loop if still a statement
        while ($arr = $INVITES->fetch(PDO::FETCH_ASSOC)) {
            $userratio = ($arr['downloaded'] ?? 0) > 0
                ? number_format(($arr['uploaded'] ?? 0) / $arr['downloaded'], 1)
                : '---';
            $warned = (($arr['warned'] ?? '') === 'yes')
                ? '<span style="color:red"><b>' . e(__('YES')) . '</b></span>'
                : '<span style="color:limegreen"><b>' . e(__('NO')) . '</b></span>';
            $added = substr((string) $arr['added'], 0, 10);
            $last  = substr((string) $arr['last_access'], 0, 10);
            if (($arr['added'] ?? '') === '0000-00-00 00:00:00') {
                $added = '---';
            }
            if (($arr['last_access'] ?? '') === '0000-00-00 00:00:00') {
                $last = '---';
            }
            ?>
            <tr>
                <td><b><?= coloredname((int) $arr['id']) ?></b></td>
                <td><?= e(get_user_class_name($arr['class'])) ?></td>
                <td><?= e($added) ?></td>
                <td><?= e($last) ?></td>
                <td><?= mksize($arr['downloaded'] ?? 0) ?></td>
                <td><?= mksize($arr['uploaded'] ?? 0) ?></td>
                <td><?= get_ratio_color($userratio) ?></td>
                <td><?= $warned ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<?php
block_end();
theme_footer();