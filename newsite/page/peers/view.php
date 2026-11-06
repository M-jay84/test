<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\PeersLogic;

$current_user = Auth::user();
$type = (string) param(0, '');
$id   = (int) param(1, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}
if ($id < 1 && $type !== 'admin') {
    redirect('home', __('MISSING_ID'), 'warning');
}

$peers = PeersLogic::getPeers($type, $id, $current_user, "peers/view/{$type}/{$id}?");
$USER  = $peers['user'] ?? [];
$PEERS = $peers['stmt'] ?? [];
$PAGER = $peers['pager'] ?? '';
$TITLE = $peers['title'] ?? __('PEERS');

theme_header(is_string($TITLE) ? strip_tags($TITLE) : __('PEERS'));
render_flash();
block_begin(is_string($TITLE) ? strip_tags($TITLE) : __('PEERS'));

if (in_array($type, ['seeding', 'leeching'], true) && $id > 0) {
    usermenu($id);
}

$canSee = true;
if ($USER !== []) {
    $canSee = ($USER['privacy'] ?? '') !== 'strong'
        || ($current_user->control_panel ?? '') === 'yes'
        || (int) $current_user->id === (int) ($USER['id'] ?? 0)
        || Auth::isStaff();
}

if ($canSee) :
?>
<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('NAME')) ?></th>
                <th><?= e(__('SIZE')) ?></th>
                <th><?= e(__('UPLOADED')) ?></th>
                <th><?= e(__('DOWNLOADED')) ?></th>
                <th><?= e(__('RATIO')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($PEERS as $arr) :
            $userratio = ((int) ($arr['downloaded'] ?? 0) > 0)
                ? number_format((int) ($arr['uploaded'] ?? 0) / (int) $arr['downloaded'], 1)
                : '---';
            ?>
            <tr>
                <td>
                    <a href="<?= e(url('torrent/view/' . (int) ($arr['torrent'] ?? 0) . '/hit')) ?>">
                        <b><?= e($arr['name'] ?? '') ?></b>
                    </a>
                </td>
                <td><?= mksize($arr['size'] ?? 0) ?></td>
                <td><?= mksize($arr['uploaded'] ?? 0) ?></td>
                <td><?= mksize($arr['downloaded'] ?? 0) ?></td>
                <td><?= get_ratio_color($userratio) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $PAGER ?>
</div>
<?php
else :
    echo '<p>' . e(__('USER_PRIVATE')) . '</p>';
endif;

block_end();
theme_footer();