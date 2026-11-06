<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\AccountsLogic;

if (Auth::check()) {
    redirect('home', __('ALREADY_LOGGED'), 'info');
}

if (is_post()) {
    csrf_verify() || redirect('authorise/login', __('INVALID_REQUEST'), 'warning');

    $result = AccountsLogic::login($_POST);

    if (!empty($result['error'])) {
        redirect('authorise/login', $result['error'], 'warning');
    }
    // success: model redirects
    return;
}

theme_header(__('LOGIN'));
render_flash();
block_begin(__('LOGIN'));

if (!empty(CONFIG['MEMBERSONLY'])) {
    echo '<p class="text-center"><b>' . e(__('MEMBERS_ONLY')) . '</b></p>';
}

form_start(url('authorise/login'), 'post');
echo csrf_field();
form_input('username', __('USERNAME'), '', 'text', __('USERNAME'), true);
form_input('password', __('PASSWORD'), '', 'password', __('PASSWORD'), true);
form_checkbox('remember', __('REMEMBER_ME'), '1', false);

if (function_exists('captcha_html')) {
    echo captcha_html();
}

echo '<div class="text-center">';
button(__('LOGIN'));
echo '<p class="text-center"><i>' . e(__('COOKIES')) . '</i></p>';
echo '</div>';

echo '<div class="p-2 text-center">';
echo '<a href="' . e(url('authorise/signup')) . '">' . e(__('SIGNUP')) . '</a> | ';
echo '<a href="' . e(url('authorise/recover')) . '">' . e(__('RECOVER')) . '</a>';
echo '</div>';

form_end();
block_end();
theme_footer();