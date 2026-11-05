<?php
declare(strict_types=1);

/**
 * Login view (controller merged into the page).
 * Logic: AccountsLogic::login()
 * URL: /authorize/login
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\AccountsLogic; // adjust namespace if yours differs

// Optional: block users who are already logged in
if (Auth::check()) {
    redirect('home', __('ALREADY_LOGGED'), 'info');
}

// ---------- POST ----------
if (is_post()) {
    if (!csrf_verify()) {
        redirect('authorize/login', 'Invalid request', 'warning');
    }

    $result = AccountsLogic::login($_POST);

    if (!empty($result['error'])) {
        // Model returned a validation / login error
        redirect('authorize/login', $result['error'], 'warning');
    }

    // Success: model already redirected (redirect / message)
    return;
}

// ---------- GET ----------
theme_header(__('LOGIN'));
render_flash();

block_begin(__('LOGIN'));

if (!empty(CONFIG['MEMBERSONLY'])) {
    echo '<p class="text-center"><b>' . e(__('MEMBERS_ONLY')) . '</b></p>';
}

form_start(url('authorize/login'), 'post');
echo csrf_field(); // name="_token" — model/csrf must match

form_input('username', __('USERNAME'), '', 'text', __('USERNAME'), true);
form_input('password', __('PASSWORD'), '', 'password', __('PASSWORD'), true);
form_checkbox(
    'remember',
    __('REMEMBER_ME') !== 'REMEMBER_ME' ? __('REMEMBER_ME') : 'Remember me for 90 days',
    '1',
    false
);

if (function_exists('captcha_html')) {
    echo captcha_html();
}

echo '<div class="text-center">';
button(__('LOGIN'));
echo '<p class="text-center"><i>' . e(__('COOKIES')) . '</i></p>';
echo '</div>';

echo '<div class="p-2 text-center">';
echo '<a href="' . url('authorize/signup') . '">' . e(__('SIGNUP')) . '</a> | ';
echo '<a href="' . url('authorize/recover') . '">' . e(__('RECOVER')) . '</a>';
echo '</div>';

form_end();
block_end();

theme_footer();