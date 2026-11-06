<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

theme_admin_header(__('ADMINISTRATION'));
render_flash();
block_begin(__('ADMINISTRATION'));
block_end();
theme_admin_footer();