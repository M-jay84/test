<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\TopicsLogic;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();

if (($current_user->view_forum ?? '') !== 'yes' && !Auth::isStaff()) {
    redirect('home', __('FORUM_BANNED'), 'warning');
}

$forumid = (int) param(0, 0);
if ($forumid < 1) {
    redirect('forum/view', __('MISSING_ID'), 'warning');
}

$TOPICS     = TopicsLogic::getForumTopics($forumid, $current_user);
$FORUM_ID   = $forumid;
$TOPIC_NAME = (string) ($TOPICS['topic_name'] ?? '');
$FORUM_NAME = (string) ($TOPICS['forum_name'] ?? '');
$canMod     = (($current_user->edit_forum ?? '') === 'yes'
           || ($current_user->delete_news ?? '') === 'yes');

theme_header(__('FORUM'));
render_flash();
block_begin(__('FORUM'));
forumheader($FORUM_NAME . '/' . $TOPIC_NAME);
?>
<div class="d-flex flex-row-reverse">
    <a type="button" class="model_form ttbtn" data-bs-toggle="modal" data-bs-target="#postModal"><?= e(__('NEW_TOPIC')) ?></a>
</div><br>

<div class="rounded">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-1"><?= e(__('READ')) ?></th>
                    <th class="col-5"><?= e(__('TOPIC')) ?></th>
                    <th class="col-1"><?= e(__('REPLIES')) ?></th>
                    <th class="col-1"><?= e(__('VIEWS')) ?></th>
                    <th class="col-1"><?= e(__('AUTHOR')) ?></th>
                    <th class="col-3"><?= e(__('LAST_POST')) ?></th>
                    <?php if ($canMod) : ?>
                        <th class="col-1"><?= e(__('MODERATE')) ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($TOPICS['topics'] ?? [] as $row) : ?>
                <tr>
                    <td><?= $row['read'] ?></td>
                    <td>
                        <a href="<?= e(url('post/view/' . (int) $row['id'])) ?>">
                            <b><?= e(encodehtml(stripslashes((string) ($row['subject'] ?? '')))) ?></b>
                        </a>
                    </td>
                    <td><?= (int) ($row['replys'] ?? 0) ?></td>
                    <td><?= (int) ($row['views'] ?? 0) ?></td>
                    <td><?= coloredname((int) ($row['userid'] ?? 0)) ?></td>
                    <td><?= $row['lastuserpost'] ?? '' ?></td>
                    <?php if ($canMod) : ?>
                        <td>
                            <?php if (!empty($row['locked'])) : ?>
                                <a href="<?= e(url("topic/action/{$FORUM_ID}/{$row['id']}/unlock")) ?>"><i class="fa fa-unlock" style="color:red"></i></a>
                            <?php else : ?>
                                <a href="<?= e(url("topic/action/{$FORUM_ID}/{$row['id']}/lock")) ?>"><i class="fa fa-lock"></i></a>
                            <?php endif; ?>
                            <a href="<?= e(url('topic/delete/' . (int) $row['id'])) ?>"><i class="fa fa-trash" style="color:red"></i></a>
                            <?php if (!empty($row['sticky'])) : ?>
                                <a href="<?= e(url("topic/action/{$FORUM_ID}/{$row['id']}/unsticky")) ?>"><i class="fa fa-exclamation" style="color:red"></i></a>
                            <?php else : ?>
                                <a href="<?= e(url("topic/action/{$FORUM_ID}/{$row['id']}/sticky")) ?>"><i class="fa fa-exclamation"></i></a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div><br>

<?= $TOPICS['pager'] ?? '' ?>

<form name="form" action="<?= e(url('topic/submit/' . $FORUM_ID)) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="text" class="form-control fullwidth" maxlength="200" name="subject"
                           placeholder="<?= e(__('SUBJECT')) ?>">
                    <?php textbbcode('form', 'body'); ?>
                    <p class="text-center"><b><?= e(__('ADD_ATTACH')) ?></b><br>
                        <input type="file" name="file[]" multiple>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn ttbtn m-2" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn ttbtn m-2"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
block_end();
theme_footer();