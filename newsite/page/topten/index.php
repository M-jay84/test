<?php
declare(strict_types=1);

/**
 * URL: /topten/index/{limit?}  default 10
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use PDO;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorize/login', __('NO_PERMISSION'), 'warning');
}

$limit = (int) param(0, 10);
if ($limit < 1 || $limit > 100) {
    $limit = 10;
}

$V_info = [];

$V_info['active'] = DB::run(
    "SELECT t.*, (t.size * t.times_completed + COALESCE(SUM(p.downloaded),0)) AS data
     FROM torrents AS t
     LEFT JOIN peers AS p ON t.id = p.torrent AND p.seeder = 'no'
     GROUP BY t.id
     ORDER BY seeders + leechers DESC, seeders DESC, added ASC
     LIMIT {$limit}"
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title1'] = 'Top Most Active Torrents';

$V_info['snatch'] = DB::run(
    "SELECT t.*, (t.size * t.times_completed + COALESCE(SUM(p.downloaded),0)) AS data
     FROM torrents AS t
     LEFT JOIN peers AS p ON t.id = p.torrent AND p.seeder = 'no'
     GROUP BY t.id
     ORDER BY times_completed DESC
     LIMIT {$limit}"
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title2'] = 'Top Most Snatched Torrents';

$V_info['country'] = DB::run(
    "SELECT name, flagpic, COUNT(users.country) AS num
     FROM countries
     LEFT JOIN users ON users.country = countries.id
     GROUP BY countries.id, name, flagpic
     ORDER BY num DESC
     LIMIT {$limit}"
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title3'] = 'Top Countries';

$userSpeed = "id AS userid, username, added, uploaded, downloaded,
    uploaded / GREATEST(1, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS upspeed,
    downloaded / GREATEST(1, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS downspeed";

$V_info['uploaders'] = DB::run(
    "SELECT {$userSpeed} FROM users WHERE enabled = ? ORDER BY uploaded DESC LIMIT {$limit}",
    ['yes']
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title4'] = 'Top Uploaders';

$V_info['downloaders'] = DB::run(
    "SELECT {$userSpeed} FROM users WHERE enabled = ? ORDER BY downloaded DESC LIMIT {$limit}",
    ['yes']
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title5'] = 'Top Downloaders';

$V_info['fastest'] = DB::run(
    "SELECT {$userSpeed} FROM users WHERE enabled = ? ORDER BY downspeed DESC LIMIT {$limit}",
    ['yes']
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title6'] = 'Top Fastest Downloaders';

$V_info['bestsharer'] = DB::run(
    "SELECT {$userSpeed} FROM users
     WHERE enabled = ? AND downloaded > 1073741824
     ORDER BY uploaded / downloaded DESC LIMIT {$limit}",
    ['yes']
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title7'] = 'Top Best Sharers';

$V_info['worstshare'] = DB::run(
    "SELECT {$userSpeed} FROM users
     WHERE enabled = ? AND downloaded > 1073741824
     ORDER BY uploaded / downloaded ASC, downloaded DESC LIMIT {$limit}",
    ['yes']
)->fetchAll(PDO::FETCH_ASSOC);
$V_info['title8'] = 'Top Worst Sharers';

theme_header(__('TOP_10'));
render_flash();
block_begin(__('TOP_10'));
?>
<div class="row">
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title1']) ?><br><?= toptorrent($V_info['active']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title2']) ?><br><?= toptorrent($V_info['snatch']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title3']) ?><br><?= topcountry($V_info['country']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title4']) ?><br><?= topuser($V_info['uploaders']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title5']) ?><br><?= topuser($V_info['downloaders']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title6']) ?><br><?= topuser($V_info['fastest']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title7']) ?><br><?= topuser($V_info['bestsharer']) ?></div>
    </div>
    <div class="col-sm-4 m-1 post-message" style="width:32.33333333%;">
        <div class="text-center"><?= e($V_info['title8']) ?><br><?= topuser($V_info['worstshare']) ?></div>
    </div>
</div>
<?php
block_end();
theme_footer();