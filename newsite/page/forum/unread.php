<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();
$catchup = param(0, false);
// /forum/unread/true or /forum/viewunread/true
if ($catchup === true || $catchup === 'true' || $catchup === '1') {
    catch_up();
    redirect('forum/unread', __('ACTION_COMPLETE'), 'success');
}

$FORUMS = ForumsLogic::unread($current_user);

theme_header(__('FORUM'));
render_flash();
block_begin(__('FORUM'));
forumheader('New Topics');
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
                    <td><i class="fa fa-file-text tticon-red" title="UnRead"></i></td>
                    <td>
                        <a href="<?= url('post/view/' . (int) $row['topicid'] . '/last/#last') ?>">
                            <b><?= e(stripslashes((string) ($row['subject'] ?? ''))) ?></b>
                        </a>
                    </td>
                    <td>
                        <a href="<?= url('topic/view/' . (int) $row['forumid']) ?>">
                            <b><?= e($row['name'] ?? '') ?></b>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
block_end();
theme_footer();