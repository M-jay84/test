<?php
declare(strict_types=1);

/**
 * Signup view (controller merged into the page).
 * Logic: AccountsLogic::register() / checkinvite()
 * Extra: CountriesLogic::dropdown()
 * URL: /authorize/signup
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\Ip;
use Bootstrap\Models\AccountsLogic;   // adjust if needed
use Bootstrap\Models\CountriesLogic;  // adjust if needed

if (Auth::check()) {
    redirect('home', __('ALREADY_LOGGED'), 'info');
}

if (!empty(CONFIG['IPCHECK']) && method_exists(Ip::class, 'checkIP')) {
    Ip::checkIP();
}

// ---------- POST ----------
if (is_post()) {
    if (!csrf_verify()) {
        redirect('authorize/signup', 'Invalid request', 'warning');
    }

    $result = AccountsLogic::register($_POST);

    if (!empty($result['error'])) {
        redirect('authorize/signup', $result['error'], 'warning');
    }

    // Success handled inside the model
    return;
}

// ---------- GET ----------
$checkInvite = AccountsLogic::checkinvite(); // 0 = normal, non-zero = invite flow
$countryHtml = CountriesLogic::dropdown();  // HTML <option> list

theme_header(__('SIGNUP'));
render_flash();

block_begin(__('SIGNUP'));

echo '<p class="text-center">' . e(__('COOKIES')) . '</p>';

form_start(url('authorize/signup'), 'post');
echo csrf_field();

// Keep invite tokens when registering via invite link
if ($checkInvite != 0) {
    form_hidden('invite', (string) (int) get('invite', 0));
    form_hidden('secret', (string) get('secret', ''));
}

form_input('wantusername', __('USERNAME'), '', 'text', __('USERNAME'), true);
form_input('wantpassword', __('PASSWORD'), '', 'password', __('PASSWORD'), true);
form_input(
    'passagain',
    __('CONFIRM') !== 'CONFIRM' ? __('CONFIRM') : 'Confirm password',
    '',
    'password',
    '',
    true
);

if ($checkInvite == 0) {
    form_input('email', __('EMAIL'), '', 'email', __('EMAIL'), true);
}

form_input('age', __('AGE'), '', 'number', __('AGE'), true, 'min="10" max="120"');

echo '<div class="form-group">';
echo '<label for="country">' . e(__('COUNTRY')) . '</label>';
echo '<select name="country" id="country" class="form-control">';
echo $countryHtml; // trusted HTML from model
echo '</select></div>';

form_select('gender', __('GENDER'), [
    ''       => __('GENDER'),
    'Male'   => __('MALE'),
    'Female' => __('FEMALE'),
], '', true);

form_input(
    'client',
    __('PREF_BITTORRENT_CLIENT') !== 'PREF_BITTORRENT_CLIENT'
        ? __('PREF_BITTORRENT_CLIENT')
        : 'Preferred BitTorrent client',
    '',
    'text',
    '',
    true
);

if (function_exists('captcha_html')) {
    echo captcha_html();
}

echo '<div class="text-center">';
button(__('SIGNUP'));
echo '</div>';

echo '<div class="p-2 text-center">';
echo '<a href="' . url('authorize/login') . '">' . e(__('LOGIN')) . '</a> | ';
echo '<a href="' . url('authorize/recover') . '">' . e(__('RECOVER')) . '</a>';
echo '</div>';

form_end();
block_end();

theme_footer();