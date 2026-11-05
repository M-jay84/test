<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\PeersLogic;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

$current_user = Auth::user();
$peers  = PeersLogic::getPeers('admin', 0, $current_user, 'peers/admin?');
$COUNT  = $peers['count'] ?? 0;
$PAGER  = $peers['pager'] ?? '';
$PEERS  = $peers['stmt'] ?? [];

theme_admin_header(__('_PEERS') !== '_PEERS' ? __('_PEERS') : __('PEERS'));
render_flash();
block_begin(__('_PEERS') !== '_PEERS' ? __('_PEERS') : 'Peers');
?>
<p class="text-center"><?= sprintf(__('MANY_PEERS'), (int) $COUNT) ?></p>
<?= $PAGER ?>
<div class="table-responsive p-3">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('USER_ID')) ?></th>
                <th><?= e(__('NAME')) ?></th>
                <th><?= e(__('IP')) ?></th>
                <th><?= e(__('PORT')) ?></th>
                <th><?= e(__('UPLOADED')) ?></th>
                <th><?= e(__('DOWNLOADED')) ?></th>
                <th><?= e(__('PEER_ID')) ?></th>
                <th><?= e(__('CONNECTED')) ?></th>
                <th><?= e(__('SEEDING')) ?></th>
                <th><?= e(__('STARTED')) ?></th>
                <th><?= e(__('LAST_ACTION')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($PEERS as $row) :
            $username = (int) ($row['userid'] ?? 0) !== 0
                ? coloredname((int) $row['userid'])
                : e($row['ip'] ?? '');
            $smallname = CutName(htmlspecialchars((string) ($row['name'] ?? '')), 40);
            $seedColor = (($row['seeder'] ?? '') === 'yes') ? 'green' : 'red';
            $connColor = (($row['connectable'] ?? '') === 'yes') ? 'green' : 'red';
            $up = (int) ($row['uploaded'] ?? 0);
            $down = (int) ($row['downloaded'] ?? 0);
            $upColor = $up < $down ? 'red' : ($up === 0 ? '' : 'green');
            ?>
            <tr>
                <td><?= $username ?></td>
                <td><a href="<?= url('torrent/view/' . (int) ($row['torrent'] ?? 0)) ?>"><?= $smallname ?></a></td>
                <td><?= e($row['ip'] ?? '') ?></td>
                <td><?= e((string) ($row['port'] ?? '')) ?></td>
                <td><?php if ($upColor) : ?><span style="color:<?= $upColor ?>"><?php endif; ?>
                    <?= mksize($up) ?>
                    <?php if ($upColor) : ?></span><?php endif; ?></td>
                <td><?= mksize($down) ?></td>
                <td><?= e(substr((string) ($row['peer_id'] ?? ''), 0, 8)) ?></td>
                <td><span style="color:<?= $connColor ?>"><?= e($row['connectable'] ?? '') ?></span></td>
                <td><span style="color:<?= $seedColor ?>"><?= e($row['seeder'] ?? '') ?></span></td>
                <td><?= e(user_timezone_date($row['started'] ?? '')) ?></td>
                <td><?= e(user_timezone_date($row['last_action'] ?? '')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $PAGER ?>
<?php
block_end();
theme_admin_footer();