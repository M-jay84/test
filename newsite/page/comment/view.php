<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\CommentsLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$id   = (int) param(0, 0);
$type = (string) param(1, 'torrent');

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

$comments  = CommentsLogic::getInfo($id, $type);
$TITLE     = $comments['title'] ?? __('COMMENTS');
$ROW       = $comments['row'] ?? [];
$COMMENT   = $comments['res'] ?? [];
$PAGER     = $comments['pager'] ?? '';
$COUNT     = (int) ($comments['count'] ?? 0);

theme_header(is_string($TITLE) ? strip_tags($TITLE) : __('COMMENTS'));
render_flash();
block_begin(is_string($TITLE) ? strip_tags($TITLE) : __('COMMENTS'));

if ($type !== 'user') :
?>
    <p class="text-center">
        <a type="button" class="model_form ttbtn btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><?= e(__('ADD')) ?></a>
    </p>
<?php
endif;

if ($type === 'news') :
?>
    <div class="tt_blockcenter rounded p-3">
        <div class="text-center">
            <b><?= e($ROW['title'] ?? '') ?></b><br>
            <?= format_comment($ROW['body'] ?? '') ?><br>
        </div>
    </div><br>
<?php
endif;

if ($COUNT > 0) {
    commenttable($COMMENT, $type);
} else {
    echo '<br><center><b>' . e(__('NOCOMMENTS')) . '</b></center><br>';
}

echo $PAGER;
?>
<form id="submit" method="post" action="<?= e(url("comment/submit/{$id}/{$type}")) ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <?php textbbcode('submit', 'text'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SAVE')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
block_end();
theme_footer();