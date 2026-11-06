<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ProfileLogic;

$current_user = Auth::user();
$id = (int) param(0, 0);

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if ($id < 1) {
    redirect('home', __('MISSING_ID'), 'warning');
}

if (($current_user->view_users ?? '') !== 'yes' && (int) $current_user->id !== $id) {
    redirect('home', __('NO_USER_VIEW'), 'warning');
}

// Model: load user, counts, html bits, invitees list; redirect if missing/disabled
$pack = ProfileLogic::getProfile($id, $current_user);
$USER     = $pack['user'];
$HTML     = $pack['html'];
$COUNT    = $pack['count'];
$DROPDOWN = $pack['dropdown'];
$INVITEES = $pack['invitees'] ?? []; // array of display HTML from model
$ID       = $id;

if (
    ($USER['privacy'] ?? '') === 'strong'
    && !Auth::isStaff()
    && (int) $current_user->id !== $id
) {
    redirect('home', __('USER_PRIVATE'), 'warning');
}

$title = __('USER_DETAILS_FOR') . ' ' . strip_tags(coloredname($id));

theme_header($title);
render_flash();
block_begin($title);
usermenu($ID);

$days = max(1, (CONFIG['TT_TIME'] - strtotime((string) $USER['added'])) / 86400);
?>
<div class="row p-4">
    <div class="col-md-2 text-center">
        <img class="embed-responsive" src="<?= e($HTML['avatar']) ?>" width="70%" height="200" alt=""><br><br>
        <?php if ((int) $current_user->id === $ID || (int) $current_user->class > (int) CONFIG['_UPLOADER']) : ?>
            <a href="#" data-bs-toggle="modal" data-bs-target="#changepassModal"><?= e(__('RESET_PASSWORD')) ?></a><br>
            <a href="#" data-bs-toggle="modal" data-bs-target="#changeemailModal"><?= e(__('RESET_EMAIL')) ?></a>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <b><?= e(__('PROFILE')) ?></b><br>
        <?= e(__('USERNAME')) ?>: <?= coloredname((int) $USER['id']) ?><br>
        <?php if (Auth::isStaff()) : ?>
            <?= e(__('EMAIL')) ?>: <?= e($USER['email']) ?><br>
            <?= e(__('PASSKEY')) ?>: <?= e($USER['passkey']) ?><br>
            <?= e(__('IP')) ?>: <?= e($USER['ip']) ?><br>
        <?php endif; ?>
        <?= e(__('USERCLASS')) ?>: <?= e(get_user_class_name($USER['class'])) ?><br>
        <?= e(__('THEME_NAME')) ?>: <?= e($USER['stylesheet']) ?><br>
        <?= e(__('TITLE')) ?>: <i><?= format_comment($USER['title'] ?? '') ?></i><br>
        <?= e(__('JOINED')) ?>: <?= e(user_timezone_date($USER['added'])) ?><br>
        <?= e(__('LAST_VISIT')) ?>: <?= e(user_timezone_date($USER['last_access'])) ?><br>
        <?= e(__('LAST_SEEN')) ?>: <?= e($USER['page'] ?? '') ?><br>
    </div>
    <div class="col-md-3">
        <b><?= e(__('INFO')) ?></b><br>
        <?= e(__('AGE')) ?>: <?= e((string) $USER['age']) ?><br>
        <?= e(__('GENDER')) ?>: <?= e($USER['gender'] ?? '') ?><br>
        <?= e(__('CLIENT')) ?>: <?= e($USER['client'] ?? '') ?><br>
        <?= e(__('COUNTRY')) ?>: <?= $HTML['country'] ?><br>
        <?= e(__('DONATED')) ?> <?= e(CONFIG['CURRENCYSYMBOL'] ?? '') ?><?= number_format((float) $USER['donated'], 2) ?><br>
        <?= e(__('WARNINGS')) ?>: <?= e((string) ($USER['warned'] ?? '')) ?><br>
        <?php if (($current_user->edit_users ?? '') === 'yes') : ?>
            <?= e(__('ACCOUNT_PRIVACY_LVL')) ?>: <b><?= e($USER['privacy'] ?? '') ?></b><br>
        <?php endif; ?>
        <?= e(__('SIGNATURE')) ?>:<br><?= format_comment($HTML['usersignature'] ?? '') ?><br><br>
        <?php if (!empty($USER['invited_by'])) : ?>
            <b><?= e(__('INVITED_BY')) ?>:</b> <?= coloredname((int) $USER['invited_by']) ?><br>
        <?php endif; ?>
        <b><?= e(__('INVITES')) ?>:</b> <?= number_format((int) ($USER['invites'] ?? 0)) ?><br>
        <?php if ($INVITEES) : ?>
            <b><?= e(__('INVITEES')) ?>:</b> <?= implode(', ', $INVITEES) ?><br>
        <?php endif; ?>
        [<a href="<?= e(url('invite/tree/' . (int) $USER['id'])) ?>"><?= e(__('INVIRE_TREE')) ?></a>]<br>
    </div>
    <div class="col-md-3">
        <b><?= e(__('STATISTICS')) ?></b><br>
        <?= e(__('UPLOADED')) ?>: <?= mksize($USER['uploaded'] ?? 0) ?><br>
        <?= e(__('DOWNLOADED')) ?>: <?= mksize($USER['downloaded'] ?? 0) ?><br>
        <?= e(__('RATIO')) ?>: <?= get_ratio_color($HTML['userratio']) ?><br>
        <?= e(__('BONUS')) ?>: <a href="<?= e(url('bonus/view')) ?>"><?= e((string) ($USER['seedbonus'] ?? '0')) ?></a><br>
        <?= e(__('HNR')) ?>: <?= (int) ($COUNT['numhnr'] ?? 0) ?><br>
        <?= e(__('AVG_DAILY_DL')) ?>: <?= mksize(($USER['downloaded'] ?? 0) / $days) ?><br>
        <?= e(__('AVG_DAILY_UL')) ?>: <?= mksize(($USER['uploaded'] ?? 0) / $days) ?><br>
        <?= e(__('TORRENTS')) ?>: <a href="<?= e(url('snatch/index/' . (int) $USER['id'] . '/user')) ?>"><?= number_format((int) ($COUNT['numtorrents'] ?? 0)) ?></a><br>
        <?= e(__('COMMENTS')) ?>: <a href="<?= e(url('comment/view/' . (int) $USER['id'] . '/user')) ?>"><?= number_format((int) ($COUNT['numcomments'] ?? 0)) ?></a><br>
        <?= e(__('POSTS')) ?>: <a href="<?= e(url('post/user/' . (int) $USER['id'])) ?>"><?= number_format((int) ($COUNT['numforumposts'] ?? 0)) ?></a><br>
    </div>
    <p>
        <span style="float:right;">
            <?php if (($current_user->edit_users ?? '') === 'yes') : ?>
                <a href="<?= e(url('snatch/index/' . (int) $USER['id'] . '/user')) ?>"><i class="fa fa-history fa-2x" title="<?= e(__('SNATCH')) ?>"></i></a>
            <?php endif; ?>
            <?php if ((int) $current_user->id === $ID || (int) $current_user->class > (int) CONFIG['_UPLOADER']) : ?>
                <a href="#" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa fa-pencil fa-2x" title="<?= e(__('EDIT')) ?>"></i></a>
            <?php endif; ?>
            <?php if ((int) $current_user->class >= (int) CONFIG['_UPLOADER']) : ?>
                <a href="#" data-bs-toggle="modal" data-bs-target="#adminModal"><i class="fa fa-user-secret fa-2x" title="<?= e(__('ADMIN')) ?>"></i></a>
            <?php endif; ?>
        </span>
    </p>
</div>
<?php
// TODO: include edit / admin / avatar / password / email / report modals here
// Each POST form: action via url(), csrf_field(), target profile/* action pages
block_end();
theme_footer();