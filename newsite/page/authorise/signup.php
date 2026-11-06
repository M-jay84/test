<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\Ip;
use Bootstrap\Models\AccountsLogic;
use Bootstrap\Models\CountriesLogic;

if (Auth::check()) {
    redirect('home', __('ALREADY_LOGGED'), 'info');
}

if (!empty(CONFIG['IPCHECK']) && method_exists(Ip::class, 'checkIP')) {
    Ip::checkIP();
}

if (is_post()) {
    csrf_verify() || redirect('authorise/signup', __('INVALID_REQUEST'), 'warning');

    $result = AccountsLogic::register($_POST);

    if (!empty($result['error'])) {
        redirect('authorise/signup', $result['error'], 'warning');
    }
    return;
}

$checkInvite = AccountsLogic::checkinvite();
$countryHtml = CountriesLogic::dropdown();

theme_header(__('SIGNUP'));
render_flash();
block_begin(__('SIGNUP'));

echo '<p class="text-center">' . e(__('COOKIES')) . '</p>';

form_start(url('authorise/signup'), 'post');
echo csrf_field();

if ($checkInvite != 0) {
    form_hidden('invite', (string) (int) get('invite', 0));
    form_hidden('secret', (string) get('secret', ''));
}

form_input('wantusername', __('USERNAME'), '', 'text', __('USERNAME'), true);
form_input('wantpassword', __('PASSWORD'), '', 'password', __('PASSWORD'), true);
form_input('passagain', __('CONFIRM'), '', 'password', '', true);

if ($checkInvite == 0) {
    form_input('email', __('EMAIL'), '', 'email', __('EMAIL'), true);
}

form_input('age', __('AGE'), '', 'number', __('AGE'), true, 'min="10" max="120"');

echo '<div class="form-group">';
echo '<label for="country">' . e(__('COUNTRY')) . '</label>';
echo '<select name="country" id="country" class="form-control">';
echo $countryHtml;
echo '</select></div>';

form_select('gender', __('GENDER'), [
    ''       => __('GENDER'),
    'Male'   => __('MALE'),
    'Female' => __('FEMALE'),
], '', true);

form_input('client', __('PREF_BITTORRENT_CLIENT'), '', 'text', '', true);

if (function_exists('captcha_html')) {
    echo captcha_html();
}

echo '<div class="text-center">';
button(__('SIGNUP'));
echo '</div>';

echo '<div class="p-2 text-center">';
echo '<a href="' . e(url('authorise/login')) . '">' . e(__('LOGIN')) . '</a> | ';
echo '<a href="' . e(url('authorise/recover')) . '">' . e(__('RECOVER')) . '</a>';
echo '</div>';

form_end();
block_end();
theme_footer();