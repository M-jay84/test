<?php
declare(strict_types=1);

if (!is_post() || !csrf_verify()) {
    redirect('login', 'Invalid request', 'error');
}

$username = trim((string) post('username', ''));
$password = (string) post('password', '');

if ($username === '' || $password === '') {
    redirect('login', __('MISSING_FORM_DATA'), 'error');
}

// Demo only – replace with real DB check later
if ($username === 'demo' && $password === 'demo') {
    $_SESSION['id']       = 1;
    $_SESSION['username'] = 'demo';
    $_SESSION['class']    = 1;
    redirect('home', 'Logged in successfully', 'success');
}

redirect('login', __('LOGIN_INCORRECT'), 'error');