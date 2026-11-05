<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;

if (!Auth::isStaff()) {
    redirect('home', __('NO_PERMISSION'), 'warning');
}

require BASEROOT . '/page/admin/admin_header.php';
render_flash();
block_begin(__('ADMINISTRATION'));
block_end();
require BASEROOT . '/page/admin/admin_footer.php';