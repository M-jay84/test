<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\NewsLogic;
use PDO;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('news/view', 'Invalid request', 'warning');
    }
    NewsLogic::insertOrUpdate();
    return;
}

$NEWS = NewsLogic::getAll(); // PDOStatement or array — handle both

theme_admin_header(__('NEWS'));
render_flash();
block_begin(__('NEWS'));
?>
<p class="text-center">
    <a href="javascript:;" class="newsAttr model_form ttbtn btn-sm"
       data-bs-toggle="modal" data-bs-target="#newsModal"><?= e(__('ADD')) ?></a>
</p>

<?php
$rows = $NEWS;
if ($NEWS instanceof PDOStatement) {
    $rows = $NEWS->fetchAll(PDO::FETCH_ASSOC);
}
if (!empty($rows)) :
    foreach ($rows as $row) :
        $body  = format_comment($row['body'] ?? '');
        $added = e(user_timezone_date($row['added'] ?? ''))
               . ' (' . e(get_elapsed_time(strtotime((string) ($row['added'] ?? 'now')))) . ' ago)';
        ?>
        <div class="row rounded p-3">
            <div class="col">
                <?= $added ?> — by <?= coloredname((int) ($row['userid'] ?? 0)) ?>
                <a href="<?= url('news/delete/' . (int) ($row['id'] ?? 0)) ?>"><i class="fa fa-trash"></i></a>
                <a href="javascript:;" class="newsAttr" data-bs-toggle="modal" data-bs-target="#newsModal"
                   data-id="<?= (int) ($row['id'] ?? 0) ?>"
                   data-body="<?= e($row['body'] ?? '') ?>"
                   data-title="<?= e($row['title'] ?? '') ?>">
                    <i class="fa fa-edit"></i>
                </a><br>
                <b><?= e($row['title'] ?? '') ?></b><br><?= $body ?>
            </div>
        </div>
        <?php
    endforeach;
else :
    echo '<p class="text-center">No News Posted</p>';
endif;
?>

<script>
document.querySelectorAll('.newsAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('title').value = this.dataset.title || '';
        var bodyEl = document.getElementById('body');
        if (bodyEl) bodyEl.value = this.dataset.body || '';
    });
});
</script>

<form id="submit" method="post" action="<?= url('news/view') ?>">
    <?= csrf_field() ?>
    <div class="modal fade" id="newsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id" value="">
                    <?= e(__('TITLE')) ?>
                    <input type="text" name="title" id="title" class="form-control fullwidth"><br>
                    <?php textbbcode('submit', 'body'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
block_end();
theme_admin_footer();