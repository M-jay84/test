<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;

/*
* Task Table Data
*/
class TasksLogic
{
    // Insert Task
    public static function updateTask($task)
    {
        $task_query = "UPDATE tasks SET last_time = ? WHERE task=?";
        DB::run($task_query, [CONFIG['TT_TIME'],$task]);
        return;
	}
	
}