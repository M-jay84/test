<?php
declare(strict_types=1);

use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$catchup = (string) param(0, '');

if ($catchup === 'true' || $catchup === '1') {
    catch_up();
    redirect('forum/unread', __('ACTION_COMPLETE'), 'success');
}

$FORUMS = ForumsLogic::unread($current_user);

theme_header(__('FORUM'));
render_flash();
block_begin(__('FORUM'));
forumheader(__('NEW_TOPICS'));
?>
<div class="p-4">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= e(__('READ')) ?></th>
                    <th><?= e(__('TOPIC')) ?></th>
                    <th><?= e(__('FORUM')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($FORUMS as $row) :
                if (($row['lastpostread'] ?? null) == ($row['lastpost'] ?? null)) {
                    continue;
                }
                if ((int) ($current_user->class ?? 0) < (int) ($row['minclassread'] ?? 0)
                    && ($row['guest_read'] ?? '') === 'no') {
                    continue;
                }
                ?>
                <tr>
                    <td><i class="fa fa-file-text tticon-red" title="<?= e(__('UNREAD')) ?>"></i></td>
                    <td>
                        <a href="<?= e(url('post/view/' . (int) $row['topicid'] . '/last/#last')) ?>">
                            <b><?= e(stripslashes((string) ($row['subject'] ?? ''))) ?></b>
                        </a>
                    </td>
                    <td>
                        <a href="<?= e(url('topic/view/' . (int) $row['forumid'])) ?>">
                            <b><?= e((string) ($row['name'] ?? '')) ?></b>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-center">
        <a class="ttbtn btn-sm" href="<?= e(url('forum/unread/true')) ?>"><?= e(__('MARK_ALL_READ')) ?></a>
    </p>
</div>
<?php
block_end();
theme_footer();