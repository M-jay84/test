<?php
declare(strict_types=1);

/**
 * URL: /peers/view/{type}/{id}
 * type: torrent | seeding | leeching | admin
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\PeersLogic;

$current_user = Auth::user();
$type = router_sanitize((string) param(0, '')); // or plain string if you prefer
$id   = (int) param(1, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1 && $type !== 'admin') {
    redirect('home', __('MISSING_ID'), 'warning');
}

$peers = PeersLogic::getPeers($type, $id, $current_user);

$USER  = $peers['user'] ?? [];
$PEERS = $peers['stmt'] ?? [];
$PAGER = $peers['pager'] ?? '';
$TITLE = $peers['title'] ?? __('PEERS');
$ID    = $id;

theme_header(is_string($TITLE) ? strip_tags($TITLE) : __('PEERS'));
render_flash();
block_begin($TITLE);

// User menu when this is a user-centric peer list
if (in_array($type, ['seeding', 'leeching'], true) && $id > 0) {
    usermenu($ID);
}

$canSee = true;
if (!empty($USER)) {
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
            $userratio = ($arr['downloaded'] ?? 0) > 0
                ? number_format(($arr['uploaded'] ?? 0) / $arr['downloaded'], 1)
                : '---';
            ?>
            <tr>
                <td>
                    <a href="<?= url('torrent/view/' . (int) $arr['torrent'] . '/hit') ?>">
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
    <?= $PAGER /* HTML from pagination() */ ?>
</div>
<?php
else :
    echo '<p>' . e(__('USER_PRIVATE')) . '</p>';
endif;

block_end();
theme_footer();