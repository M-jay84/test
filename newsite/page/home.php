<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Models\ShoutboxLogic;
use Bootstrap\Models\TmdbLogic;
use Bootstrap\Models\TorrentsLogic;
use PDO;

$current_user = Auth::user();

theme_header(CONFIG['SITENAME'] ?? 'Home');
render_flash();

block_begin('Welcome');
echo '<p>These links exercise the router, helpers, theme UI, flash colours, and Auth.</p>';

if (Auth::check()) {
    echo '<p>Status: <strong>Logged in</strong> as '
       . e(Auth::user()->username ?? ('#' . Auth::id()))
       . '</p>';
} else {
    echo '<p>Status: <strong>Guest</strong></p>';
}
block_end();

// ----- Auth -----
block_begin('Auth');
echo '<ul>';
echo '<li><a class="ttbtn" href="' . url('authorise/login') . '">Login</a> — form, CSRF, remember me, green/yellow flash</li>';
echo '<li><a class="ttbtn" href="' . url('authorise/logout') . '">Logout</a> — clear session + flash</li>';
echo '<li><a class="ttbtn" href="' . url('authorise/signup') . '">Sign Up</a> — form, CSRF, green/yellow flash</li>';
echo '</ul>';
block_end();

// ----- Messages / colours -----
block_begin('Messages & colours');
echo '<ul>';
echo '<li><a href="' . url('demo/flash') . '">Flash + redirect (green)</a> — success on next page</li>';
echo '<li><a href="' . url('demo/warning') . '">Flash warning (yellow)</a> — validation-style message</li>';
echo '<li><a href="' . url('demo/info') . '">Flash info (blue)</a> — soft notice</li>';
echo '<li><a href="' . url('demo/message') . '">Message page then redirect</a> — themed intermediate page</li>';
echo '<li><a href="' . url('demo/error') . '">Error page (red)</a> — error() helper</li>';
echo '</ul>';
block_end();

// ----- UI helpers -----
block_begin('UI helpers');
echo '<ul>';
echo '<li><a href="' . url('demo/forms') . '">Forms</a> — form_*, csrf_field, button</li>';
echo '<li><a href="' . url('demo/ui') . '">UI kit</a> — blocks, alerts, table, badge, progress</li>';
echo '</ul>';
block_end();

// ----- Router / params -----
block_begin('Router & params');
echo '<ul>';
echo '<li><a href="' . url('demo/view/42') . '">demo/view/42</a> — param(0) = 42</li>';
echo '<li><a href="' . url('demo/params/one/two/three') . '">demo/params/one/two/three</a> — multiple params</li>';
echo '<li><a href="' . url('does-not-exist') . '">Missing page</a> — 404 via router</li>';
echo '</ul>';
block_end();

// ----- Hit & Run notice -----
if (!empty(CONFIG['HNR_ON']) && Auth::check()) {
    $count = (int) DB::run(
        'SELECT COUNT(hnr) FROM snatched WHERE uid = ? AND hnr = ?',
        [(int) $current_user->id, 'yes']
    )->fetchColumn();

    if ($count > 0) {
        block_begin(__('HIT&RUN') !== 'HIT&RUN' ? __('HIT&RUN') : 'Hit & Run');
        echo '<div class="text-center">'
           . sprintf(
               __('HIT&RUNNOTICE'),
               coloredname((int) $current_user->id),
               $count,
               (int) $current_user->id
           )
           . '</div>';
        block_end();
    }
}

// ----- News -----
if (Auth::check() || empty(CONFIG['MEMBERSONLY'])) {
    block_begin(__('NEWS'));
    $res = DB::run(
        'SELECT news.id, news.title, news.added, news.body, users.username, users.id AS userid
         FROM news
         LEFT JOIN users ON news.userid = users.id
         ORDER BY added DESC LIMIT 10'
    );
    if ($res->rowCount() > 0) {
        echo '<div class="text-center">';
        $news_flag = 0;
        while ($array = $res->fetch(PDO::FETCH_ASSOC)) {
            $disp = $news_flag < 2 ? 'show' : '';
            $numcomm = (int) DB::run(
                'SELECT COUNT(*) FROM comments WHERE news = ?',
                [(int) $array['id']]
            )->fetchColumn();
            $uid = (int) ($array['userid'] ?? 0);
            ?>
            <a data-bs-toggle="collapse" data-bs-target="#news<?= (int) $array['id'] ?>">
                <i class="fa fa-info-circle"></i>
            </a>
            <b><?= e($array['title']) ?></b>
            - <b><?= e(__('POSTED')) ?>:</b> <?= e(user_timezone_date($array['added'])) ?>
            <b><?= e(__('BY')) ?>:</b> <?= $uid ? coloredname($uid) : e(__('UNKNOWN_USER')) ?>
            <div class="collapse <?= $disp ?>" id="news<?= (int) $array['id'] ?>">
                <div><?= format_comment($array['body'] ?? '') ?></div>
            </div>
            <br><?= e(__('COMMENTS')) ?>
            (<a href="<?= url('comment/view/' . (int) $array['id'] . '/news') ?>"><?= number_format($numcomm) ?></a>)
            <br>
            <?php
            $news_flag++;
        }
        echo '</div>';
    } else {
        echo '<br><b>' . e(__('NO_NEWS')) . '</b>';
    }
    block_end();
}

// ----- Shoutbox -----
if (!empty(CONFIG['SHOUTBOX']) && ($current_user->hideshoutbox ?? '') !== 'yes') {
    block_begin(__('SHOUTBOX'));
    echo '<p id="shoutbox"></p>';
    if (Auth::check()) {
        ?>
        <form id="contactForm1" name="contactForm1" action="<?= url('shoutbox/add') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row"><div class="col-md-12">
                <?php shoutbbcode('contactForm1', 'message'); ?>
            </div></div>
        </form>
        <?php
    }
    block_end();
}

// ----- Latest forum posts -----
if (!empty(CONFIG['LATESTFORUMPOSTONINDEX'])) {
    block_begin(__('LATEST_POST'));
    latestforumposts();
    block_end();
}

// ----- Forums on index -----
if (!empty(CONFIG['FORUMONINDEX'])) {
    $forums_res = DB::run('SELECT * FROM forumcats WHERE id IN (1,2,3) ORDER BY sort');
    block_begin(__('FORUM'));
    if ($forums_res->rowCount() === 0) {
        echo e(__('NO_FORUMS_FOUND') !== 'NO_FORUMS_FOUND' ? __('NO_FORUMS_FOUND') : 'No forums found');
    } else {
        echo '<div class="p-4">';
        foreach ($forums_res as $cat) {
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-6"><?= e($cat['name']) ?></th>
                            <th class="col-2"><?= e(__('TOPICS')) ?></th>
                            <th class="col-2"><?= e(__('POSTS')) ?></th>
                            <th class="col-2"><?= e(__('LAST_POST')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $stmt = DB::run(
                        'SELECT * FROM forum_forums WHERE category = ? ORDER BY sort, name',
                        [(int) $cat['id']]
                    );
                    foreach ($stmt as $row) {
                        $lastpostid = get_forum_last_post((int) $row['id']);
                        $detail = lastpostdetails($lastpostid);
                        $postcount = (int) DB::run(
                            'SELECT COUNT(id) FROM forum_posts WHERE topicid IN (SELECT id FROM forum_topics WHERE forumid = ?)',
                            [(int) $row['id']]
                        )->fetchColumn();
                        $topiccount = (int) DB::run(
                            'SELECT COUNT(id) FROM forum_topics WHERE forumid = ?',
                            [(int) $row['id']]
                        )->fetchColumn();
                        ?>
                        <tr>
                            <td>
                                <?= $detail['img'] ?>&nbsp;
                                <a href="<?= url('topic/view/' . (int) $row['id']) ?>"><b><?= e($row['name']) ?></b></a><br>
                                <small>- <?= format_comment($row['description'] ?? '') ?></small>
                            </td>
                            <td><?= $topiccount ?></td>
                            <td><?= $postcount ?></td>
                            <td><?= $detail['lastpost'] ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        echo '</div>';
    }
    block_end();
}

// Carousel / grid — original condition was inverted (showed when view_torrents != yes).
// Show when user may view torrents:
$canViewTorrents = Auth::check()
    && (($current_user->view_torrents ?? '') === 'yes' || empty(CONFIG['MEMBERSONLY']));

if ($canViewTorrents) {
    $stmt = TorrentsLogic::getAll('WHERE visible = ? AND banned = ?', ['yes', 'no']);
    $rows = $stmt['stmt'] ?? [];

    block_begin(__('CAROUSEL') !== 'CAROUSEL' ? __('CAROUSEL') : 'Carousel');
    // … optional carousel markup using $rows + TmdbLogic::getImage($row) …
    block_end();

    block_begin(__('GRID') !== 'GRID' ? __('GRID') : 'Grid');
    echo '<div class="row">';
    foreach ($rows as $row) {
        echo '<div class="col-2"><a href="' . url('torrent/view/' . (int) $row['id']) . '">';
        echo '<img src="' . e(TmdbLogic::getImage($row)) . '" width="100" height="250" alt=""></a><br>';
        echo '<p class="text-center">' . e(CutName($row['name'] ?? '', 20)) . '<br>';
        echo '<span style="color:#00cc00"><b>S</b></span> ' . (int) ($row['seeders'] ?? 0);
        echo ' <span style="color:#FF0000"><b>L</b></span> ' . (int) ($row['leechers'] ?? 0) . '</p></div>';
    }
    echo '</div>';
    block_end();
}

// ----- Torrent table -----
block_begin(__('TORRENT'));
if (!empty(CONFIG['MEMBERSONLY']) && !Auth::check()) {
    echo e(__('MEMBERS_ONLY'));
} else {
    $res = TorrentsLogic::getAll('WHERE visible = ? AND banned = ?', ['yes', 'no']);
    if (($res['count'] ?? 0) > 0) {
        echo '<div class="text-center"><a href="' . url('torrents/browse') . '">' . e(__('BROWSE_TORRENTS')) . '</a></div>';
        torrenttable($res['stmt'] ?? []);
    } else {
        echo e(__('NO_TORRENT_FOUND'));
    }
    if (Auth::check()) {
        DB::run(
            'UPDATE users SET last_browse = ? WHERE id = ?',
            [CONFIG['TT_TIME'], (int) $current_user->id]
        );
    }
}
block_end();

// ----- Visited users -----
block_begin(__('VISITED_USERS'));
$visitors = DB::run(
    'SELECT id, username, class, donated, warned, avatar FROM users
     WHERE enabled = ? AND status = ? AND privacy != ?
     AND UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(last_access) <= ?',
    ['yes', 'confirmed', 'strong', CONFIG['TT_DATE'], 86400]
);
foreach ($visitors as $row) {
    $avatar = (string) ($row['avatar'] ?? '');
    if ($avatar === '') {
        $avatar = url('page/theme/default/images/misc/default_avatar.webp');
    }
    echo "<img src='" . e($avatar) . "' width='15' height='15' alt=''> ";
    echo '<a href="' . url('profile/view/' . (int) $row['id']) . '"><small><b>'
       . coloredname((int) $row['id']) . '</b></small></a>&nbsp;';
}
block_end();

if (!empty(CONFIG['DISCLAIMERON'])) {
    block_begin(__('DISCLAIMER'));
    echo __('DISCLAIMERTXT');
    block_end();
}

theme_footer();