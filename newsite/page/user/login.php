<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use PDO;

// Already logged in?
if (Auth::check()) {
    redirect('home', 'You are already logged in', 'info');
}

// ---------- POST: process login ----------
if (is_post()) {

    if (!csrf_verify()) {
        redirect('login', 'Invalid request', 'warning');
    }

    // Captcha (keep your existing helper)
    if (function_exists('captcha_response')) {
        captcha_response(post('g-recaptcha-response', ''));
    }

    $username = trim((string) post('username', ''));
    $password = (string) post('password', '');
    $remember = (bool) post('remember', false);

    if ($username === '' || $password === '') {
        redirect('login', __('MISSING_FORM_DATA'), 'warning'); // yellow
    }

    $sql = DB::run(
        "SELECT id, username, password, secret, passkey, status, enabled, class, role_id
         FROM users
         WHERE username = ?",
        [$username]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$sql || !password_verify($password, $sql['password'])) {
        redirect('login', __('LOGIN_INCORRECT'), 'warning'); // yellow
    }

    if (($sql['status'] ?? '') === 'pending') {
        redirect('login', __('ACCOUNT_PENDING'), 'warning');
    }

    if (($sql['enabled'] ?? '') === 'no') {
        redirect('login', __('ACCOUNT_DISABLED'), 'warning');
    }

    // Success
    session_regenerate_id(true);

    // Short session always; long cookie only if "Remember me"
    Auth::loginUser($sql, $remember ? 90 : 0);

    DB::run(
        "UPDATE users SET last_login = ? WHERE id = ?",
        [CONFIG['TT_DATE'], $sql['id']]
    );

    redirect('home', __('SUCCESS'), 'success'); // green
}

// ---------- GET: show form ----------
theme_header(__('LOGIN'));
render_flash();

block_begin(__('LOGIN'));

if (!empty(CONFIG['MEMBERSONLY'])) {
    echo '<p class="text-center"><b>' . e(__('MEMBERS_ONLY')) . '</b></p>';
}

form_start(url('login'), 'post');
echo csrf_field();

form_input('username', __('USERNAME'), '', 'text', __('USERNAME'), true);
form_input('password', __('PASSWORD'), '', 'password', __('PASSWORD'), true);

form_checkbox('remember', __('REMEMBER_ME') !== 'REMEMBER_ME'
    ? __('REMEMBER_ME')
    : 'Remember me for 90 days', '1', false);

if (function_exists('captcha_html')) {
    echo captcha_html();
}

echo '<div class="text-center">';
button(__('LOGIN'));
echo '<p class="text-center"><i>' . e(__('COOKIES')) . '</i></p>';
echo '</div>';

echo '<div class="p-2 text-center">';
echo '<a href="' . url('signup') . '">' . e(__('SIGNUP')) . '</a> | ';
echo '<a href="' . url('recover') . '">' . e(__('RECOVER')) . '</a>';
echo '</div>';

form_end();
block_end();

theme_footer();