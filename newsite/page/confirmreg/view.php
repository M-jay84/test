<?php
declare(strict_types=1);


use Bootstrap\Libs\Auth;
use Bootstrap\Models\UsersLogic;

if (!Auth::isStaff()) { redirect('home', __('NO_PERMISSION'), 'warning'); }
$users = UsersLogic::usersToConfirm();
$COUNT = $users['count'] ?? 0;
$PAGER = $users['pager'] ?? '';
$USERS = $users['users'] ?? [];

theme_admin_header(__('REG_CONFIRM'));
render_flash();
block_begin(__('REG_CONFIRM'));
// table + form action url('confirmreg/submit') + csrf_field()
block_end();
theme_admin_footer();