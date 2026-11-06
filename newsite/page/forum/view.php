<?php
declare(strict_types=1);

use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$FORUMS = ForumsLogic::getCatForums();

theme_header(__('FORUM'));
render_flash();
block_begin(__('FORUM'));
forumheader();
latestforumposts();

foreach ($FORUMS as $country_id => $cat_info) :
?>
<div class="table-responsive tt_block">
    <table class="table table-striped">
        <thead class="tt_blockhead">
            <tr>
                <th class="col-6"><?= e((string) ($cat_info['c_name'] ?? '')) ?></th>
                <th class="col-2"><?= e(__('TOPICS')) ?></th>
                <th class="col-2"><?= e(__('POSTS')) ?></th>
                <th class="col-2"><?= e(__('LAST')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cat_info['c_forums'] ?? [] as $forum_id => $forum_info) :
            $lastpostid = get_forum_last_post((int) $forum_id);
            $detail = lastpostdetails($lastpostid);
            ?>
            <tr>
                <td>
                    <?= $detail['img'] ?? '' ?>&nbsp;
                    <a href="<?= e(url('topic/view/' . (int) $forum_id)) ?>">
                        <b><?= e((string) ($forum_info['f_name'] ?? '')) ?></b>
                    </a><br>
                    <small>- <?= format_comment((string) ($forum_info['f_description'] ?? '')) ?></small>
                </td>
                <td><?= (int) ($forum_info['topic_count'] ?? 0) ?></td>
                <td><?= (int) ($forum_info['post_count'] ?? 0) ?></td>
                <td><?= $detail['lastpost'] ?? '' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<div class="rounded p-1">
    <i class="fa fa-file-text tticon-red"></i>&nbsp;<?= e(__('NEW_POST')) ?>&nbsp;
    <i class="fa fa-file-text"></i>&nbsp;<?= e(__('READ')) ?>&nbsp;
    <i class="fa fa-lock"></i>&nbsp;<?= e(__('LOCKED')) ?>&nbsp;
    <i class="fa fa-exclamation"></i>&nbsp;<?= e(__('STICKY')) ?>
</div>
<?php
block_end();
theme_footer();