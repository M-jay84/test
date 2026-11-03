<?php
declare(strict_types=1);

theme_header('Home – Examples');
render_flash();

block_begin('Welcome');
echo '<p>Welcome Home. These links show how routing, flash, forms, params and errors work.</p>';
if (empty($_SESSION['id'])) {
    echo '<p>Status: <strong>Guest</strong></p>';
} else {
    echo '<p>Status: <strong>Logged in</strong> as ' . e($_SESSION['username'] ?? 'user') . '</p>';
}
block_end();

block_begin('Examples');
echo '<ul>';
echo '<li><a href="' . url('login') . '"><strong>Login</strong></a> – form + CSRF + flash redirect on success/fail</li>';
echo '<li><a href="' . url('logout') . '"><strong>Logout</strong></a> – clear session + flash</li>';
echo '<li><a href="' . url('flash') . '"><strong>Flash + redirect</strong></a> – message appears on the next page</li>';
echo '<li><a href="' . url('message') . '"><strong>Message page</strong></a> – themed page, then auto-redirect</li>';
echo '<li><a href="' . url('error') . '"><strong>Error page</strong></a> – themed error()</li>';
echo '<li><a href="' . url('view/3') . '"><strong>Params</strong></a> – /account/view/3 → param(0) = 3</li>';
echo '<li><a href="' . url('view') . '"><strong>Missing param</strong></a> – triggers error for missing id</li>';
echo '<li><a href="' . url('does-not-exist') . '"><strong>404</strong></a> – missing page file</li>';
echo '</ul>';
block_end();

theme_footer();