<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::check()) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

if (empty(CONFIG['INVITEONLY']) && empty(CONFIG['ENABLEINVITES'])) {
    redirect('home', __('INVITES_DISABLED_MSG'), 'warning');
}

$user = Auth::user();
if ((int) ($user->invites ?? 0) === 0) {
    redirect('home', __('YOU_HAVE_NO_INVITES_MSG'), 'warning');
}

if (class_exists(UsersLogic::class) && method_exists(UsersLogic::class, 'gmaxUser')) {
    UsersLogic::gmaxUser();
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