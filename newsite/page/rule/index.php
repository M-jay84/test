<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RulesLogic;

$current_user = Auth::user();

if (!Auth::check() && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$RULES = RulesLogic::gettAll();
$isStaffEditor = ((int) ($current_user->class ?? 0) > (int) (CONFIG['_UPLOADER'] ?? 0));

theme_header(__('RULE'));
render_flash();

foreach ($RULES as $row) {
    $public = ($row['public'] ?? '') === 'yes';
    $canSee = $public
        || (!$public && (int) ($row['class'] ?? 0) <= (int) ($current_user->class ?? 0));

    if (!$canSee) {
        continue;
    }

    block_begin($row['title'] ?? '');
    echo format_comment($row['text'] ?? '') . '<br>';

    if ($isStaffEditor) {
        echo '<p class="text-end">';
        echo '<i class="fa fa-user-secret m-1 tt_prime" title="' . e(__('STAFF')) . '"></i> ';
        echo '<a href="' . url('rule/delete/' . (int) $row['id']) . '"><i class="fa fa-trash"></i></a> ';
        echo '<a href="javascript:;" class="rulesAttr" data-bs-toggle="modal" data-bs-target="#rulesModal"';
        echo ' data-id="' . (int) $row['id'] . '"';
        echo ' data-title="' . e($row['title'] ?? '') . '"';
        echo ' data-text="' . e($row['text'] ?? '') . '"';
        echo ' data-public="' . e($row['public'] ?? '') . '"';
        echo ' data-classs="' . e((string) ($row['class'] ?? '')) . '">';
        echo '<i class="fa fa-pencil"></i></a></p>';
    }

    block_end();
}

if ($isStaffEditor) :
?>
    <p class="text-end">
        <button type="button" class="ttbtn" data-bs-toggle="modal" data-bs-target="#rulesModal">
            <i class="fa fa-user-secret m-1 tt_prime" title="<?= e(__('STAFF')) ?>"></i><?= e(__('ADD')) ?>
        </button>
    </p>

    <script>
    document.querySelectorAll('.rulesAttr').forEach(function (el) {
        el.addEventListener('click', function () {
            document.getElementById('id').value = this.dataset.id || '';
            document.getElementById('title').value = this.dataset.title || '';
            document.getElementById('text').value = this.dataset.text || '';
            document.getElementById('public').value = this.dataset.public || 'yes';
            document.getElementById('classs').value = this.dataset.classs || '';
        });
    });
    </script>

    <form id="submit" method="post" action="<?= url('rule/edit') ?>">
        <?= csrf_field() ?>
        <div class="modal fade" id="rulesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                <div class="modal-content">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id" value="">
                        <?= e(__('TITLE')) ?>:<br>
                        <input class="form-control fullwidth" type="text" name="title" id="title"><br>
                        <?= e(__('RULE')) ?>:<br>
                        <textarea style="height:150px;" class="form-control" name="text" id="text"></textarea><br>
                        <?= e(__('PUBLIC')) ?>:<br>
                        <select name="public" id="public" class="form-control">
                            <option value="yes"><?= e(__('YES')) ?></option>
                            <option value="no"><?= e(__('NO')) ?></option>
                        </select><br>
                        <?= e(__('MIN_CLASS')) ?>:<br>
                        <input type="text" name="classs" id="classs" class="form-control">
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
endif;

theme_footer();