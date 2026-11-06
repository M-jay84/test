<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\AttachmentsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$topicid = (int) param(0, 0);

if ($topicid < 1) {
    redirect('forum/view', __('MISSING_ID'), 'warning');
}

$post     = PostsLogic::getTopicPages($topicid, $current_user);
$POSTS    = $post['stmt'] ?? [];
$INFO     = $post; // has subject, forumid, locked, maypost, lpr, etc.
$PAGER    = $post['pager'] ?? '';
$TOPICID  = $topicid;
$USER     = $current_user;
$TITLE    = $post['subject'] ?? __('FORUM');

theme_header(strip_tags((string) $TITLE));
render_flash();
?>
<div class="tt_block rounded">
    <div class="tt_blockhead text-center"><?= e((string) $TITLE) ?></div>
    <div class="p-3 post-body">
        <?php
        forumheader(
            ($INFO['forum_name'] ?? '') . " / <a href='" . url('topic/view/' . (int) ($INFO['forumid'] ?? 0)) . "'>"
            . e($INFO['name'] ?? '') . '</a>'
        );
        ?>

        <?php if (empty($INFO['locked']) && !empty($INFO['maypost'])) : ?>
            <div class="text-end">
                <a type="button" class="model_form ttbtn" data-bs-toggle="modal" data-bs-target="#postModal"><?= e(__('REPLY')) ?></a>
            </div><br>
        <?php else : ?>
            <div class="text-end"><b><?= e(__('LOCKED')) ?></b></div><br>
        <?php endif; ?>

        <div class="text-center"><b><?= e($INFO['subject'] ?? '') ?></b></div><br>
        <?= $PAGER ?>

        <?php
        $pn = 0;
        $pc = count($POSTS);
        foreach ($POSTS as $row) :
            echo "<a id='post" . (int) $row['id'] . "'></a>";
            ++$pn;
            if ($pn === $pc) {
                echo "<a name='last'></a>\n";
                if (
                    Auth::check()
                    && (int) $row['id'] > (int) ($INFO['lpr'] ?? 0)
                ) {
                    // Fix typo topicis → topicid when you update the model/SQL
                    DB::run(
                        'UPDATE forum_readposts SET lastpostread = ? WHERE userid = ? AND topicid = ?',
                        [(int) $row['id'], (int) $USER->id, $TOPICID]
                    );
                }
            }

            $added = e(user_timezone_date($row['added']))
                   . ' (' . e(get_elapsed_time(strtotime((string) $row['added']))) . ' ago)';
            $posterdetails = posterdetails((int) $row['userid']);
            $body = format_comment($row['body'] ?? '');
            if (!empty($row['editedby'])) {
                $body .= '<br><br><small><i>Last edited by <a href="'
                    . url('profile/view/' . (int) $row['editedby']) . '">'
                    . e($posterdetails['by'] ?? '') . '</a> on '
                    . e(user_timezone_date($row['editedat'] ?? '')) . '</i></small><br>';
            }
            ?>
            <div class="row post-message">
                <div class="col-12">
                    <div class="rounded p-2">
                        <div class="row">
                            <div class="col-md-2"><?= $posterdetails['by'] ?></div>
                            <div class="col-md-10"><?= e(__('POSTED_AT')) ?> <?= $added ?></div>
                            <div class="col-md-2 d-none d-sm-block">
                                <center><i><?= $posterdetails['title'] ?></i></center><br>
                                <center><img src="<?= e($posterdetails['avatar']) ?>" alt="" class="rounded-circle scaleA"></center><br>
                                <?= e(__('UPLOADED')) ?>: <?= $posterdetails['useruploaded'] ?><br>
                                <?= e(__('DOWNLOADED')) ?>: <?= $posterdetails['userdownloaded'] ?><br>
                                <?= e(__('POSTS')) ?>:
                                <a href="<?= url('post/user/' . (int) $row['userid']) ?>"><?= (int) $posterdetails['forumposts'] ?></a><br>
                                <?= e(__('RATIO')) ?>: <?= $posterdetails['userratio'] ?><br>
                            </div>
                            <div class="col-md-10"><br>
                                <?= $body ?>
                                <?php AttachmentsLogic::get_attachment((int) $row['id']); ?>
                                <?php if (!empty($posterdetails['usersignature'])) : ?>
                                    <br><hr><br>
                                    <div class="f-sig" align="center"><?= $posterdetails['usersignature'] ?></div>
                                <?php else : ?>
                                    <br>
                                <?php endif; ?>
                            </div>
                            <?php if (Auth::check()) : ?>
                                <div class="col-md-3 d-none d-sm-block">
                                    <a href="<?= url('profile/view/' . (int) $row['userid']) ?>"><i class="fa fa-user tticon"></i></a>
                                    <a href="<?= url('message/create/' . (int) $row['userid']) ?>"><i class="fa fa-comment tticon"></i></a>
                                    <a href="javascript:;" class="reportAttr" data-bs-toggle="modal" data-bs-target="#reportModal" data-id="<?= (int) $row['id'] ?>"><i class="fa fa-flag tticon"></i></a>
                                    <a href="javascript:scroll(0,0);"><i class="fa fa-arrow-up tticon"></i></a>
                                    <a href="<?= url('post/view/' . $TOPICID . '?page=' . e((string) get('page', '1')) . '#post' . (int) $row['id']) ?>"><i class="fa fa-anchor tticon"></i></a>
                                </div>
                                <div class="col-md-9 d-none d-sm-block">
                                    <?php if ((int) $USER->id !== (int) $row['userid']) : ?>
                                        <a href="<?= url('like/submit/' . (int) $row['id'] . '/thanksforum') ?>"><i class="fa fa-thumbs-up tticon"></i></a>
                                    <?php endif; ?>
                                    <?php if (
                                        (int) $USER->id === (int) $row['userid']
                                        || ($USER->delete_news ?? '') === 'yes'
                                        || ($USER->edit_forum ?? '') === 'yes'
                                    ) : ?>
                                        <a data-bs-toggle="modal" data-bs-target="#editModal<?= (int) $row['id'] ?>"><i class="fa fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (($USER->delete_news ?? '') === 'yes') : ?>
                                        <a href="<?= url('post/delete/' . (int) $row['id']) ?>"><i class="fa fa-trash tticon-red"></i></a>
                                    <?php endif; ?>
                                    <?php if (empty($INFO['locked']) && !empty($INFO['maypost'])) : ?>
                                        <a type="button" class="model_form ttbtn" data-bs-toggle="modal" data-bs-target="#postModal"><i class="fa fa-reply tticon"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <form name="formv" action="<?= url('post/edit') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal fade" id="editModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                        <div class="modal-content">
                            <div class="modal-body">
                                <input type="hidden" name="page" value="<?= e((string) get('page', '')) ?>">
                                <input type="hidden" name="postid" value="<?= (int) $row['id'] ?>">
                                <?php textbbcode('formv', 'body', (string) ($row['body'] ?? '')); ?>
                                <p class="text-center"><b><?= e(__('ADD_ATTACHMENT')) ?></b><br>
                                    <input type="file" name="file[]" multiple>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn ttbtn m-2" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                                <button type="submit" class="btn ttbtn m-2"><?= e(__('SAVE')) ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>

        <?= $PAGER ?>

        <form name="form" action="<?= url('post/submit/' . $TOPICID) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <?php textbbcode('form', 'body'); ?>
                            <p class="text-center"><b><?= e(__('ADD_ATTACHMENT')) ?></b><br>
                                <input type="file" name="file[]" multiple>
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn ttbtn m-2" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                            <button type="submit" class="btn ttbtn m-2"><?= e(__('SAVE')) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        document.querySelectorAll('.reportAttr').forEach(function (el) {
            el.addEventListener('click', function () {
                document.getElementById('id').value = this.dataset.id || '';
            });
        });
        </script>

        <form method="post" action="<?= url('report/submit') ?>">
            <?= csrf_field() ?>
            <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p class="text-center"><?= e(__('ARE_YOU_SURE_REPORT')) ?> Forum.</p>
                            <input type="hidden" name="forumid" value="<?= $TOPICID ?>">
                            <input type="hidden" name="id" id="id" value="">
                            <input type="hidden" name="type" value="forum">
                            <input class="form-control" type="text" name="reason" placeholder="<?= e(__('REASON')) ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                            <button type="submit" class="btn btn-sm ttbtn"><?= e(__('REPORT')) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <?php
        if (!empty($INFO['locked'])) {
            echo e(__('FORUMS_TOPIC_LOCKED')) . '<br><br>';
        } elseif (empty($INFO['maypost'])) {
            echo '<i>' . e(__('FORUMS_YOU_NOT_PERM_POST_FORUM')) . '</i><br><br>';
        }

        if (($USER->delete_news ?? '') === 'yes' || ($USER->edit_forum ?? '') === 'yes') {
            modoptions(
                $TOPICID,
                (string) ($INFO['subject'] ?? ''),
                (int) ($INFO['forumid'] ?? 0),
                $INFO['locked'] ?? false,
                $INFO['sticky'] ?? false
            );
        }
        ?>
    </div>
</div>
<?php
// no block_end if you used custom tt_block wrapper
if (function_exists('theme_footer')) {
    theme_footer();
}