<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;
use Bootstrap\Models\MessagesLogic;

/*
* Warnings Table Data
*/
class WarningsLogic
{
    // Get All Users Warnings
    public static function getByUserId($userid)
    {
        $warnings_query = "SELECT * FROM `warnings` WHERE userid=? ORDER BY id DESC";
        $stmt = DB::run($warnings_query, [$userid]);
        return $stmt;
    }

    // User Warn Form Submit
    public static function newwarn($userid)
    {
        // Check User Input
        $input = [
            'reason' => Input1('reason'),
            'expiry' => Input1('expiry', 'int'),
            'type' => Input1('type'),
            'userid' => $userid
        ];

        // Check Input
        if (!$input['reason'] || !$input['expiry'] || !$input['type']) {
            message("profile/view/$input[userid]", LANG["MISSING_FORM_DATA"]);
        }


        $expiretime = date("Y-m-d H:i:s", CONFIG['TT_TIME'] + (86400 * $input['expiry']));
        WarningsLogic::insert(['userid'=>$input['userid'], 'reason'=>$input['reason'], 'added'=>CONFIG['TT_DATE'],'expiry'=>$expiretime,'warnedby'=>'staff','type'=>$input['type']]);
        message("warning/$input[userid]", LANG["SUCCESS"]);
    }

    // Delete All Warnings
    public static function deleteAll($userid, $current_user)
    {
        // Set Data
        $data = [
            'current_user' => $current_user,
            'userid' => $userid,
            'post_id' => multiIds("ids")
        ];

        if ($data['current_user']->control_panel == "yes") {
            $warnings_query = "SELECT count(id) FROM `warnings` WHERE userid=? ";
            $count = DB::run($warnings_query, [$data['userid']])->fetchColumn();
            $warnings_query = "DELETE FROM `warnings` WHERE `id` IN (?)";
            $deleted = DB::run($warnings_query, [$data['post_id']]);
            if ($count == $deleted || $count <= $deleted) {
                $users_query = "UPDATE users SET warned = ? WHERE id=?";
                DB::run($users_query, ['no', $data['userid']]);
            }
            message("warning/$data[userid]", LANG["DEL_COMPLETE"]);
        }  
        message("warning/$data[userid]", LANG["ACTION_FAILED"]);
    }

    // Add Warning
    public static function insert($data)
    {
        $warnings_query = "INSERT INTO `warnings` (userid, reason, added,expiry,warnedby,type) VALUES (?,?,?,?,?,?)";
        DB::run($warnings_query, 
        [$data['userid'], $data['reason'], CONFIG['TT_DATE'], $data['expiry'], $data['warnedby'], $data['type']]);
        MessagesLogic::newinsert(['sender' => 0, 'receiver' => $data['userid'], 'added' => CONFIG['TT_DATE'], 'subject' => LANG['NEW_WARNING'], 'msg' => $data['reason'], 'unread' => 'yes', 'location' => 'in']);
        LogsLogic::write($data['reason']);
    }

    // Update Warning
    public static function update($data)
    {
        LogsLogic::write($data['reason']);
        $users_query = "UPDATE users SET warned = ? WHERE id=?";
        DB::run($users_query, [$data['warned'], $data['id']]);
        WarningsLogic::update(['expiry', 'active'], ['userid='], [CONFIG['TT_DATE'], $data['warned'], $data['id']]);
        MessagesLogic::newinsert(['sender' => 0, 'receiver' => $data['id'], 'added' => CONFIG['TT_DATE'], 'msg' => $data['reason']]);
    }

    // Get All Warnings
    public static function getAll()
    {
        // Pagination
        $users_query = "SELECT id FROM `users` WHERE warned=?";
        $count = DB::run($users_query, ['yes'])->fetchColumn();
        list($pager, $limit) = pagination(25, $count, URLROOT . '/warning/admin?');
        $users_query = "SELECT users.id,users.username,users.class,users.added,users.last_access 
                        FROM users
                        WHERE warned = ?
                        ORDER BY `added` 
                        DESC $limit";
        $stmt = DB::run($users_query, ['yes'])->fetchAll();
        
        $array = [
            'count' => $count,
            'stmt' => $stmt,
            'pager' => $pager
        ];
        return $array;
    }

    // Admin Delete Warnings
    public static function deleteAllAdmin()
    {
        $ids = multiIds('ids');
        $warnings_query = "DELETE FROM `warnings` WHERE `active` = ? AND `userid` IN (?)";
        DB::run($warnings_query, ['yes', $ids]);
        $users_query = "UPDATE `users` SET `warned` = ? WHERE `id` IN (?)";
        DB::run($users_query, ['no', $ids]);
        message('adminwarning', LANG["SUCCESS"]);
    }

}