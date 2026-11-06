<?php
declare(strict_types=1);

/**
 * Set new password from recovery link.
 * URL: /authorise/recoverconfirm/{id}/{secret}
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RecoverLogic;

if (Auth::check()) {
    redirect('home', __('ALREADY_LOGGED'), 'info');
}

$id     = (int) param(0, 0);
$secret = (string) param(1, '');

if ($id < 1 || strlen($secret) !== 20) {
    redirect('authorise/recover', __('NOTHING_FOUND'), 'warning');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('authorise/recoverconfirm/' . $id . '/' . $secret, 'Invalid request', 'warning');
    }
    RecoverLogic::changePassword($id, $secret);
    return;
}

theme_header(__('RECOVER'));
render_flash();

block_begin(__('RECOVER'));

form_start(url("authorise/recoverconfirm/{$id}/{$secret}"), 'post');
echo csrf_field();
form_input('password', __('NEW_PASSWORD'), '', 'password', __('NEW_PASSWORD'), true);
form_input('password1', __('REPEAT'), '', 'password', __('REPEAT'), true);

echo '<div class="text-center">';
button(__('SUBMIT'));
echo '</div>';

form_end();
block_end();
theme_footer();