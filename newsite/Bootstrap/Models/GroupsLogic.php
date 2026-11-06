<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use PDO;

/*
* Groups Table Data
*/
class GroupsLogic
{
    // Fetch Groups Level
    public static function fetchlevel($ids)
    {
        $groups_query = "SELECT DISTINCT level FROM `groups` WHERE group_id IN ($ids)";
        $stmt = DB::run($groups_query)->fetchAll();
		return $stmt;
	}

    // Function That Returns The Group Name
    public static function GetUserClassName($i, $data = [])
    {
        $groups_query = "SELECT level FROM `groups`  WHERE group_id = ?";
        $row = DB::run($groups_query, [$i])->fetch(PDO::FETCH_ASSOC);
        if ($i == $data['current_user']->class) {
           return  $row['level'];
        }
        return $row['level'];
    }

    // Function To List Groups Of Members Of The Database
    public static function getGroups()
    {
        $groups_query = "SELECT * FROM `groups` 
                         LEFT OUTER JOIN `permissions` 
                         ON `groups`.group_role_id=`permissions`.permission_id";
        $stmt = DB::run($groups_query)->fetchAll();
        return $stmt;
    }

public static function delete(int $group_id): void
{
    if ($group_id === 1 || $group_id === (int) (CONFIG['_ADMINISTRATOR'] ?? 0)) {
        redirect('group/view', __('YOU_CANT_DEL_THIS_GRP'), 'warning');
    }
    DB::run('DELETE FROM `groups` WHERE group_id = ?', [$group_id]);
    redirect('group/view', __('DEL_COMPLETE'), 'success');
}

public static function insertOrUpdate(): void
{
    $group_id = input_int('group_id', 0);
    $level    = input_string('level');
    $color    = input_string('group_color');
    $role_id  = input_string('group_role_id');
    $slots    = input_int('maxslots', 0);
    $cp       = input_string('control_panel') === 'yes' ? 'yes' : 'no';
    $staff    = input_string('staff_page') === 'yes' ? 'yes' : 'no';
    $public   = input_string('staff_public') === 'yes' ? 'yes' : 'no';
    $sort     = input_int('staff_sort', 0);

    if ($group_id < 1) {
        DB::run(
            'INSERT INTO `groups` (level, Color, group_role_id, maxslots, control_panel, staff_page, staff_public, staff_sort)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$level, $color, $role_id, $slots, $cp, $staff, $public, $sort]
        );
        redirect('group/view', __('SUCCESS'), 'success');
    }

    DB::run(
        'UPDATE `groups` SET level = ?, Color = ?, group_role_id = ?, maxslots = ?,
         control_panel = ?, staff_page = ?, staff_public = ?, staff_sort = ?
         WHERE group_id = ?',
        [$level, $color, $role_id, $slots, $cp, $staff, $public, $sort, $group_id]
    );
    redirect('group/view', __('UPDATE_COMPLETE'), 'success');
}

}