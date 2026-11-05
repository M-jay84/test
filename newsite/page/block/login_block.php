<?php
declare(strict_types=1);

/**
 * Sidebar: member stats + account/staff links, or guest login form.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;

if (Auth::check()) {
    $user = Auth::user();
    $uid  = (int) Auth::id();

    $avatar = (string) ($user->avatar ?? $_SESSION['avatar'] ?? '');
    if ($avatar === '') {
        $avatar = url('assets/images/misc/default_avatar.webp');
    }

    $maxSlotDl = (int) DB::run(
        'SELECT COUNT(DISTINCT torrent) FROM peers WHERE userid = ? AND seeder = ?',
        [$uid, 'yes']
    )->fetchColumn();

    $maxslots = (int) ($user->maxslots ?? $_SESSION['maxslots'] ?? 0);
    $slots    = number_format($maxslots) . '/' . number_format($maxSlotDl);

    $downloaded = (float) ($user->downloaded ?? $_SESSION['downloaded'] ?? 0);
    $uploaded   = (float) ($user->uploaded ?? $_SESSION['uploaded'] ?? 0);
    $userratio  = $downloaded > 0 ? number_format($uploaded / $downloaded, 1) : '---';

    block_begin(coloredname($uid));
    ?>
    <div class="row">
        <div class="col">
            <div class="text-center">
                <img src="<?= e($avatar) ?>" alt="" class="rounded-circle scaleA">
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-5"><?= e(__('DOWNLOAD')) ?></dt>
                <dd class="col-sm-7"><?= mksize((int) $downloaded) ?></dd>
                <dt class="col-sm-5"><?= e(__('UPLOAD')) ?></dt>
                <dd class="col-sm-7"><?= mksize((int) $uploaded) ?></dd>
                <dt class="col-sm-5"><?= e(__('CLASS')) ?></dt>
                <dd class="col-sm-7"><?= e((string) ($user->level ?? $_SESSION['level'] ?? '')) ?></dd>
                <dt class="col-sm-5"><?= e(__('PRIVACY')) ?></dt>
                <dd class="col-sm-7"><?= e(__((string) ($user->privacy ?? $_SESSION['privacy'] ?? 'normal'))) ?></dd>
                <dt class="col-sm-5"><?= e(__('BONUS')) ?></dt>
                <dd class="col-sm-7">
                    <a href="<?= url('bonus/view') ?>"><?= e((string) ($user->seedbonus ?? $_SESSION['seedbonus'] ?? '0')) ?></a>
                </dd>
                <dt class="col-sm-5"><?= e(__('RATIO')) ?></dt>
                <dd class="col-sm-7"><?= get_ratio_color($userratio) ?></dd>
                <dt class="col-sm-5"><?= e(__('SLOTS')) ?></dt>
                <dd class="col-sm-7"><?= e($slots) ?></dd>
            </dl>
        </div>
    </div>
    <div class="text-center">
        <a href="<?= url('profile/view/' . $uid) ?>" class="btn ttbtn"><?= e(__('ACCOUNT')) ?></a>
        <?php if (($user->control_panel ?? $_SESSION['control_panel'] ?? '') === 'yes') : ?>
            <a href="<?= url('admincp') ?>" class="btn ttbtn"><?= e(__('STAFFCP')) ?></a>
        <?php endif; ?>
    </div>
    <?php
    block_end();
} elseif (empty(CONFIG['MEMBERSONLY'])) {
    // Public site: show login form for guests
    block_begin(__('LOGIN'));
    ?>
    <div class="justify-content-md-center">
        <form method="post" action="<?= url('authorize/login') ?>">
            <?= csrf_field() ?>
            <b><?= e(__('USERNAME')) ?>:</b>
            <input type="text" class="form-control" name="username" required>
            <b><?= e(__('PASSWORD')) ?>:</b>
            <input type="password" class="form-control" name="password" required>
            <div class="text-center">
                <?= captcha_html() ?>
                <button type="submit" class="btn btn-sm ttbtn"><?= e(__('LOGIN')) ?></button>
            </div>
            <p class="text-center">
                [<a href="<?= url('authorize/signup') ?>"><?= e(__('SIGNUP')) ?></a>]
                /
                [<a href="<?= url('authorize/recover') ?>"><?= e(__('RECOVER')) ?></a>]
            </p>
        </form>
    </div>
    <?php
    block_end();
}