<?php
declare(strict_types=1);

/**
 * Sidebar: remaining invites + links to invite / invite tree.
 * Shown only when invites are enabled and the visitor is logged in.
 */

use Bootstrap\Libs\Auth;

if ((!empty(CONFIG['INVITEONLY']) || !empty(CONFIG['ENABLEINVITES'])) && Auth::check()) {
    $user    = Auth::user();
    $invites = (int) ($user->invites ?? $_SESSION['invites'] ?? 0);
    $invitees = (int) ($user->invitees ?? $_SESSION['invitees'] ?? 0);

    block_begin(__('INVITES'));
    ?>
    <div class="text-center">
        <?= sprintf(__('YOU_HAVE_INVITES'), $invites) ?>
        <?php if ($invites > 0) : ?>
            <a href="<?= url('invite/home') ?>" class="btn ttbtn"><?= e(__('INVITE')) ?></a>
        <?php endif; ?>
        <?php if ($invitees > 0) : ?>
            <a href="<?= url('invite/tree/' . (int) Auth::id()) ?>" class="btn ttbtn"><?= e(__('INVITED')) ?></a>
        <?php endif; ?>
    </div>
    <?php
    block_end();
}