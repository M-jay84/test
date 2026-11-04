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

    // Admin Delete Group
    public static function delete($group_id)
    {
        // Protect Groups
        if (($group_id == "1") || ($group_id == CONFIG['_ADMINISTRATOR'])) {
            message('admingroup', LANG["YOU_CANT_DEL_THIS_GRP"]);
        }
        $groups_query = "DELETE FROM `groups` WHERE group_id' = ?";
        DB::run($groups_query, [$group_id]);
        message('admingroup/groups', LANG["DEL_COMPLETE"]);
    }

    // Admin Add/Edit Group
    public static function insertOrUpdate()
    {
        $group_id = Input1('group_id', 'int');
        if (!$group_id) {
            $groups_query = "INSERT INTO `groups` (level,Color,group_role_id,maxslots,control_panel,staff_page,staff_public,staff_sort) VALUES (?,?,?,?,?,?,?,?)";
            DB::run($groups_query,
             [$_POST["level"], $_POST["group_color"], $_POST["group_role_id"], $_POST["maxslots"],
                $_POST["control_panel"], $_POST["staff_page"], $_POST["staff_public"], intval($_POST['staff_sort'])]);
            message('admingroup', LANG["SUCCESS"]);
        } else {
            $groups_query = "UPDATE groups SET level=? AND Color=? AND group_role_id=? AND maxslots=? AND control_panel=? AND staff_page=? AND staff_public=?
             AND staff_sort=? WHERE group_id=?";
            DB::run($groups_query, [$_POST["level"], $_POST["group_color"], $_POST["group_role_id"], $_POST["maxslots"], $_POST["control_panel"], $_POST["staff_page"], $_POST["staff_public"], intval($_POST['staff_sort']), $group_id]);
            message('admingroup', LANG["UPDATE_COMPLETE"]);
        }
    }

}