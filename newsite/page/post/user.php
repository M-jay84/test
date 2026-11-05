<?php
declare(strict_types=1);
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$id = (int) param(0, 0);
if ($id < 1) {
    redirect('forum/view', __('MISSING_ID'), 'warning');
}

$posts = ForumsLogic::getUsersPost($id, $current_user);
$POSTS = $posts['row'] ?? [];
$PAGER = $posts['pager'] ?? '';
$USER  = $current_user;

theme_header(__('USER_POSTS'));
render_flash();
block_begin(__('USER_POSTS'));
?>
<p class="text-center"><a href="<?= url('profile/view/' . $id) ?>">Go To Profile</a></p>
<?php
foreach ($POSTS as $row) {
    $user = userpostdetails($row);
    $private = (($USER->edit_users ?? '') !== 'yes' && ($user['privacylevel'] ?? '') === 'strong');
    ?>
    <div class="row tt_block rounded p-1">
        <div class="col-2 d-none d-sm-block">
            <center>
                <?= coloredname((int) $row['userid']) ?><br>
                <i><?= $user['title'] ?></i><br>
                <img width="80" height="80" src="<?= e($user['avatar']) ?>" alt=""><br>
                <?php if ($private) : ?>
                    Uploaded: ---<br>Downloaded: ---<br>Ratio: ---
                <?php else : ?>
                    Uploaded: <?= $user['useruploaded'] ?><br>
                    Downloaded: <?= $user['userdownloaded'] ?><br>
                    Ratio: <?= get_ratio_color($user['userratio']) ?>
                <?php endif; ?>
                <br>
                <a href="<?= url('profile/view/' . (int) $row['userid']) ?>"><i class="fa fa-user"></i></a>
                <a href="<?= url('message/create/' . (int) $row['userid']) ?>"><i class="fa fa-comment"></i></a>
            </center>
        </div>
        <div class="col-10">
            <a href="<?= url('post/view/' . (int) $row['topicid']) ?>"><b><?= e($row['subject'] ?? '') ?></b></a>
            Posted: <?= e(user_timezone_date($row['added'] ?? '')) ?>
            in <a href="<?= url('topic/view/' . (int) $row['forumid']) ?>"><b><?= e($row['name'] ?? '') ?></b></a>
            <hr>
            <?= $user['commenttext'] ?>
            <hr>
            <?= $user['usersignature'] ?>
        </div>
    </div><br>
    <?php
}
echo $PAGER;
block_end();
theme_footer();