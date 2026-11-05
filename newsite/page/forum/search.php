<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ForumsLogic;

$current_user = ForumsLogic::forum_guard();

$data     = ForumsLogic::search();
$TITLE    = $data['TITLE'] ?? __('SEARCH');
$KEYWORDS = $data['KEYWORDS'] ?? (string) get('keywords', '');
$FORUMS   = $data['FORUMS'] ?? ($data['stmt'] ?? []);
$PAGER    = $data['PAGER'] ?? ($data['pager'] ?? '');
$USER     = $current_user;

theme_header(is_string($TITLE) ? $TITLE : __('SEARCH'));
render_flash();
block_begin($TITLE);
forumheader('search');
?>
<div class="tt_blockcenter p-2">
    <form method="get" action="<?= url('forum/search') ?>">
        <div class="row justify-content-md-center">
            <input id="keywords" type="text" class="form-control w-50" name="keywords"
                   minlength="3" maxlength="25" value="<?= e($KEYWORDS) ?>">
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SEARCH_TOPIC')) ?></button>
            <button type="submit" class="btn btn-sm ttbtn" name="type" value="deep"><?= e(__('SEARCH_POST')) ?></button>
        </div>
    </form>
</div>

<div class="table-responsive p-3">
    <p class="text-center"><?= e(__('SEARCH')) ?>: <b><?= e($KEYWORDS) ?></b></p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= e(__('SUBJECT')) ?></th>
                <th><?= e(__('FORUM')) ?></th>
                <th><?= e(__('ADDED')) ?></th>
                <th><?= e(__('ADDED_BY')) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($FORUMS as $row) :
            if (($row['name'] ?? '') === ''
                || ((int) ($row['minclassread'] ?? 0) > (int) ($USER->class ?? 0)
                    && ($row['guest_read'] ?? '') === 'no')) {
                continue;
            }
            ?>
            <tr>
                <td><a href="<?= url('post/view/' . (int) $row['topicid']) ?>"><?= e($row['subject'] ?? '') ?></a></td>
                <td><a href="<?= url('topic/view/' . (int) $row['forumid']) ?>"><?= e($row['name'] ?? '') ?></a></td>
                <td><?= e((string) ($row['added'] ?? '')) ?></td>
                <td><?= coloredname((int) ($row['userid'] ?? 0)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $PAGER ?>
<?php
block_end();
theme_footer();