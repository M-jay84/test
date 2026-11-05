<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;

/*
* Shoutbox Table Data
*/
class ShoutboxLogic
{
    // Delete Shout
    public static function remove($id)
    {
        $shoutbox_query = "DELETE FROM shoutbox WHERE id = ?";
        $stmt = DB::run($shoutbox_query, [$id]);
        message(URLROOT, LANG["ACTION_COMPLETE"]);
	}

    // Get Shouts
    public static function shouts($limit, $public = 0)
    {
        $shoutbox_query = "SELECT * FROM `shoutbox` WHERE staff=? ORDER BY msgid DESC LIMIT $limit";
        $shouts = DB::run($shoutbox_query, [$public]);
        return $shouts;
    }

    // Truncate All Shouts
    public static function truncate($current_user, $sure = 0)
    {
        if ($sure == 'sure') {
        
            $shoutbox_query = "TRUNCATE TABLE shoutbox";
            DB::run($shoutbox_query);
            LogsLogic::write("Shoutbox cleared by ".$current_user->username."");
            $msg_shout = "[color=#ff0000]" . LANG["SHOUTBOX_CLEARED_MESSAGE"] . "[/color]";
            $shoutbox_query = "INSERT INTO shoutbox (userid,date,user,message) VALUES (?,?,?,?)";
            DB::run($shoutbox_query, [0, CONFIG['TT_DATE'], 'System', $msg_shout]);
            message('admincp', "<b><font color='#ff0000'>Shoutbox Cleared....</font></b>");
        } else {
            message('admincp', "<b>Are You Sure <a href='".URLROOT."/task/clearshout/sure'>delete all shouts</a></b>");
        }
    }

    // Add Shout
    public static function add($current_user, $staff = 0)
    {
        if ($current_user->shoutboxpos != 'yes' && $current_user->id) {
            if (!empty($_POST['message']) && $current_user->id) {
                $message = Input1('message');


                $count =  DB::run('SELECT count(*) FROM `shoutbox` WHERE message=? AND user=? AND UNIX_TIMESTAMP(?)-UNIX_TIMESTAMP(date) <?', [$message, $current_user->username, CONFIG['TT_DATE'], 30])->fetchColumn();
                if ($count == '0') {

                    DB::run('INSERT INTO shoutbox (userid, date, user, message, staff) VALUES (?, ?, ?, ?, ?)', [$current_user->id, CONFIG['TT_DATE'], $current_user->username, $message, $staff]);
                }
            }
        } else {
            message(URLROOT, LANG["NO_PERMISSION"]);
        }
    }

    // Delete Shout
    public static function delete($id, $current_user, $sure = 0)
    {
        if ($sure == 0) {
            message(URLROOT, "Sanity check: Click <a href='".URLROOT."/shoutbox/delete/$id/1'>here</a> if you are sure.");
        }

        $shoutbox_query = "SELECT user FROM `shoutbox` WHERE msgid=?";
        $row =  DB::run($shoutbox_query, [$id])->fetchColumn();
        if ($current_user->edit_users == 'yes' || $current_user->username == $row['user']) {
            LogsLogic::write("<b><font color='orange'>Shout Deleted:</font> Deleted by   " . $current_user->username . "</b>");
            ShoutboxLogic::remove(['msgid'=>$id]);
        }
       url(URLROOT);
    }

    // Edit Shout
    public static function edit($current_user)
    {
        // Check User Input
        $user = Input1('user', 'int');
        if ($current_user->isStaff || $current_user->id == $user) {
            // Check User Input
            $msgid = Input1('id', 'int');
            $message = Input1('text');
            $shoutbox_query = "UPDATE shoutbox SET message = ? WHERE msgid=?";
            DB::run($shoutbox_query, [$message, $msgid]);
            message(URLROOT, LANG["UPDATE_COMPLETE"]);
        } else {
        }
    }

}