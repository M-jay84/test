<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use Bootstrap\Models\ShoutboxLogic;

$current_user = Auth::user();
$SHOUTS = ShoutboxLogic::shouts(20);
?>
<div class="table-responsive" style="height:250px;">
    <table class="table table-striped p-2">
        <?php foreach ($SHOUTS as $row) :
            $ol3 = DB::run('SELECT avatar FROM users WHERE id = ?', [(int) $row['userid']])->fetchColumn();
            if (!empty($ol3)) {
                $av = '<img src="' . e((string) $ol3) . '" alt="" width="20" height="20">';
            } else {
                $av = '<img src="' . e(url('page/theme/default/images/misc/default_avatar.webp')) . '" alt="" width="20" height="20">';
            }
            if ((int) ($row['userid'] ?? 0) === 0) {
                $av = '<img src="' . e(url('page/theme/default/images/misc/default_avatar.webp')) . '" alt="" width="20" height="20">';
            }
            ?>
            <tr>
                <td style="width:119px;">
                    <small class="d-none d-sm-block" style="font-size:11px">
                        <i class="fa fa-clock-o"></i> <?= e(user_timezone_date($row['date'])) ?>
                    </small>
                </td>
                <td style="width:119px;"><?= $av ?> <b><?= coloredname((int) $row['userid']) ?>:</b></td>
                <td>
                    <?= nl2br(format_comment($row['message'] ?? '')) ?>
                    <?php if (($current_user->edit_users ?? '') === 'yes') : ?>
                        <a href="<?= url('shoutbox/delete/' . (int) $row['msgid']) ?>"><i class="fa fa-remove"></i></a>
                        <a href="javascript:;" class="editAttr" data-bs-toggle="modal" data-bs-target="#editModal"
                           data-id="<?= (int) $row['msgid'] ?>"
                           data-text="<?= e($row['message'] ?? '') ?>"
                           data-user="<?= (int) $row['userid'] ?>"><i class="fa fa-pencil"></i></a>
                    <?php elseif (
                        Auth::check()
                        && (string) ($current_user->username ?? '') === (string) ($row['user'] ?? '')
                    ) :
                        $ts = modify('date', $row['date'], '+1 day');
                        if ($ts > CONFIG['TT_DATE']) :
                            ?>
                            <a href="javascript:;" class="editAttr" data-bs-toggle="modal" data-bs-target="#editModal"
                               data-id="<?= (int) $row['msgid'] ?>"
                               data-text="<?= e($row['message'] ?? '') ?>"
                               data-user="<?= (int) $row['userid'] ?>"><i class="fa fa-pencil"></i></a>
                            <?php
                        endif;
                    endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if ((int) ($current_user->class ?? 0) > (int) (CONFIG['_UPLOADER'] ?? 0)) : ?>
<script>
document.querySelectorAll('.editAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('text').value = this.dataset.text || '';
        document.getElementById('user').value = this.dataset.user || '';
    });
});
</script>
<form name="shoutboxform" action="<?= url('shoutbox/edit') ?>" method="post">
    <?= csrf_field() ?>
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="user" id="user">
                    <input type="text" class="form-control fullwidth" name="text" id="text">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                    <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SUBMIT')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif;