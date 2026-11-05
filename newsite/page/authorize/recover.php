<?php
declare(strict_types=1);

/**
 * Request password recovery email.
 * URL: /authorize/recover
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\RecoverLogic;

if (Auth::check()) {
    redirect('home', __('ALREADY_LOGGED'), 'info');
}

if (is_post()) {
    if (!csrf_verify()) {
        redirect('authorize/recover', 'Invalid request', 'warning');
    }
    // Model sends mail and should redirect
    RecoverLogic::recoverPassword();
    return;
}

theme_header(__('RECOVER'));
render_flash();

block_begin(__('RECOVER'));

form_start(url('authorize/recover'), 'post');
echo csrf_field();
form_input('email', __('EMAIL'), '', 'email', __('EMAIL'), true);

if (function_exists('captcha_html')) {
    echo captcha_html();
}

echo '<div class="text-center">';
button(__('SUBMIT'));
echo '</div>';

echo '<p class="text-center p-2">';
echo '<a href="' . url('authorize/login') . '">' . e(__('LOGIN')) . '</a> | ';
echo '<a href="' . url('authorize/signup') . '">' . e(__('SIGNUP')) . '</a>';
echo '</p>';

form_end();
block_end();
theme_footer();