<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::check()) {
    http_response_code(403);
    exit;
}

if (!is_post()) {
    exit;
}

$result = UsersLogic::getUserName();
if (empty($result)) {
    exit;
}

echo '<ul id="country-list">';
foreach ($result as $user) {
    $name = e($user['username'] ?? '');
    echo '<li onclick="userCountry(\'' . $name . '\')">' . $name . '</li>';
}
echo '</ul>';
exit;