<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ShoutboxLogic;

$current_user = Auth::user();
$SHOUTS = ShoutboxLogic::shouts(20);
$defaultAv = url('page/theme/default/images/misc/default_avatar.webp');
$canMod = (($current_user->edit_users ?? '') === 'yes') || Auth::isStaff();
?>
<div class="table-responsive" style="height:250px;">
    <table class="table table-striped p-2">
        <?php foreach ($SHOUTS as $row) :
            $uid = (int) ($row['userid'] ?? 0);
            $av  = ($uid > 0 && !empty($row['avatar']))
                ? (string) $row['avatar']
                : $defaultAv;
            ?>
            <tr>
                <td style="width:119px;">
                    <small class="d-none d-sm-block" style="font-size:11px">
                        <i class="fa fa-clock-o"></i> <?= e(user_timezone_date($row['date'])) ?>
                    </small>
                </td>
                <td style="width:119px;">
                    <img src="<?= e($av) ?>" alt="" width="20" height="20">
                    <b><?= coloredname($uid) ?>:</b>
                </td>
                <td>
                    <?= nl2br(format_comment($row['message'] ?? '')) ?>
                    <?php
                    $canEdit = $canMod;
                    if (!$canEdit && Auth::check() && (string) ($current_user->username ?? '') === (string) ($row['user'] ?? '')) {
                        $ts = modify('date', $row['date'], '+1 day');
                        $canEdit = $ts > (CONFIG['TT_DATE'] ?? '');
                    }
                    if ($canMod) :
                        ?>
                        <a href="<?= e(url('shoutbox/delete/' . (int) $row['msgid'])) ?>"><i class="fa fa-remove"></i></a>
                    <?php endif;
                    if ($canEdit) : ?>
                        <a href="javascript:;" class="editAttr" data-bs-toggle="modal" data-bs-target="#editModal"
                           data-id="<?= (int) $row['msgid'] ?>"
                           data-text="<?= e($row['message'] ?? '') ?>"
                           data-user="<?= $uid ?>"><i class="fa fa-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if (Auth::check()) : ?>
<script>
document.querySelectorAll('.editAttr').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('id').value = this.dataset.id || '';
        document.getElementById('text').value = this.dataset.text || '';
        document.getElementById('user').value = this.dataset.user || '';
    });
});
</script>
<form name="shoutboxform" action="<?= e(url('shoutbox/edit')) ?>" method="post">
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
<?php endif; ?>