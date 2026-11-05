<?php
declare(strict_types=1);

use Bootstrap\Libs\Auth;
use Bootstrap\Models\ForumsLogic;

if (!Auth::isStaff()) { redirect('home', __('NO_PERMISSION'), 'warning'); }
ForumsLogic::delete((int) param(0, 0), (string) param(1, ''));