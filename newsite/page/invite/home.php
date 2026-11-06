<?php
declare(strict_types=1);

/**
 * URL: /invite/home
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic; // if gmaxUser lives there

$current_user = Auth::user();

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (empty(CONFIG['INVITEONLY']) && empty(CONFIG['ENABLEINVITES'])) {
    redirect('home', __('INVITES_DISABLED_MSG'), 'warning');
}

if ((int) ($current_user->invites ?? 0) === 0) {
    redirect('home', __('YOU_HAVE_NO_INVITES_MSG'), 'warning');
}

if (class_exists(UsersLogic::class) && method_exists(UsersLogic::class, 'gmaxUser')) {
    UsersLogic::gmaxUser(); // may redirect if site full
}

theme_header(__('INVITES'));
render_flash();
block_begin(__('INVITES'));

form_start(url('invite/submit'), 'post');
echo csrf_field();
form_input('email', __('EMAIL'), '', 'email', __('EMAIL'), true);
form_textarea('mess', __('MESSAGE'), '', 7);

echo '<div class="text-center">';
button(__('SEND_AN_INVITE'));
echo '</div>';

form_end();
block_end();
theme_footer();