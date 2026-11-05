<?php
declare(strict_types=1);

/**
 * URL: /demo/forms/submit (POST)
 */

if (!is_post() || !csrf_verify()) {
    redirect('demo/forms', 'Invalid form request', 'warning');
}

$name  = trim((string) post('name', ''));
$email = trim((string) post('email', ''));
$agree = (bool) post('agree', false);

if ($name === '') {
    redirect('demo/forms', 'Name is required', 'warning');
}

if (!$agree) {
    redirect('demo/forms', 'You must agree to continue', 'warning');
}

// Demo only — show what was posted via flash
$msg = 'Form OK. Name: ' . $name
     . ($email !== '' ? ', Email: ' . $email : '');

redirect('demo/forms', $msg, 'success');