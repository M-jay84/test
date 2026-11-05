<?php
declare(strict_types=1);

use Bootstrap\Models\Cleanup;
use Bootstrap\Libs\Auth;
use Bootstrap\Models\TasksLogic;

        $current_user = Auth::user();

        if (!$current_user->isStaff) {
            message(URLROOT, LANG["NO_PERMISSION"]);
        }
                TasksLogic::updateTask('cleanup');
        Cleanup::run();
        message('admincp', LANG["FORCE_CLEAN_COMPLETED"]);

        