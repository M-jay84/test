<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;

/*
* Bans Table Data
*/
class BansLogic
{
    // Admin Delete Ban
    public static function delete($dat)
    {
        // Get Input
        $ids = multiIds('ids');

        // Delete Ban
        $bans_query = "SELECT * FROM bans WHERE id IN ($ids)";
        $bans = DB::run($bans_query)->fetchAll();
        foreach ($bans as $row) {
            message('ban/admin', LANG["ACTION_COMPLETE"]);
            $bans_query = "DELETE FROM `bans` WHERE id = ?";
            DB::run($bans_query, [$row['id']]);
            LogsLogic::write("Ban ($row[first] - $row[last]) was removed by " . $data['current_user']->id . " (" . $data['current_user']->username . ")");    
        }
    }

    // Admin Add Ban
    public static function insert($current_user)
    {
        $first = Input1('first');
        $last = Input1('last');
        $comment = Input1('comment');
        $type = Input1('type');

        if ($first == "" || $type == "" || $comment == "") {
            message('ban/admin', LANG["MISSING_FORM_DATA"]);
        }
        $bans_query = "INSERT INTO `bans` (added,addedby,first,last,comment,type) VALUES (?,?,?,?,?,?)";
        DB::run($bans_query,
        [CONFIG['TT_DATE'],$current_user->id,$first,$last,$comment,$type]);
        LogsLogic::write(sprintf(LANG["BAN_ADDED"], $first, $current_user->username));
        message('ban/admin', LANG["SUCCESS"]);
    }

    // Admin Get Bans
    public static function getAll($type)
    {
        if ($type == 'ip') {
            $users_query = "SELECT count(id) FROM `users`";
            $count = DB::run($users_query)->fetchColumn();
            list($pager, $limit) = pagination(50, $count, URLROOT . "/ban/admin/ip"); // 50 per page
            $bans_query = "SELECT bans.id, bans.added, bans.addedby, bans.first, bans.last,bans.comment,bans.type, users.username 
                         FROM bans 
                         LEFT JOIN users 
                         ON bans.addedby=users.id 
                         WHERE bans.type=?
                         ORDER BY added $limit";
                         $stmt = DB::run($bans_query, ['ip'])->fetchAll();
        } elseif ($type == 'email') {
            $users_query = "SELECT count(id) FROM `users`";
            $count = DB::run($users_query)->fetchColumn();
            list($pager, $limit) = pagination(40, $count, URLROOT . "/ban/admin/email");
            $bans_query = "SELECT bans.id, bans.added, bans.addedby, bans.first, bans.last,bans.comment,bans.type, users.username 
                         FROM bans 
                         LEFT JOIN users 
                         ON bans.addedby=users.id 
                         WHERE bans.type=?
                         ORDER BY added $limit";
                         $stmt = DB::run($bans_query, ['email'])->fetchAll();
        } elseif ($type == 'domain') {
            $users_query = "SELECT count(id) FROM `users`";
            $count = DB::run($users_query)->fetchColumn();
            list($pager, $limit) = pagination(40, $count, URLROOT . "/ban/admin/email");
            $bans_query = "SELECT bans.id, bans.added, bans.addedby, bans.first, bans.last,bans.comment,bans.type, users.username 
                         FROM bans 
                         LEFT JOIN users 
                         ON bans.addedby=users.id 
                         WHERE bans.type=?
                         ORDER BY added $limit";
                         $stmt = DB::run($bans_query, ['domain'])->fetchAll();
        }

        $array = [
            'bans' => $stmt,
            'pager' => $pager
        ];

        return $array;
    }

    // Check Email account/invite
    public static function checkEmail($email)
    {
        $maildomain = (substr($email, strpos($email, "@") + 1));
        $bans_query = "SELECT count(*) FROM `bans` WHERE first = ?";
        $a = DB::run($bans_query, [$email])->fetchColumn();
        if ($a != 0) {
            $message = sprintf(LANG["EMAIL_ADDRESS_BANNED_S"], $email);
        }
        $bans_query = "SELECT count(*) FROM `bans` WHERE first LIKE ?";
        $a = DB::run($bans_query, ['%$maildomain%'])->fetchColumn();
        if ($a != 0) {
            //$message = sprintf(LANG["EMAIL_ADDRESS_BANNED_S"], $email);
        }
        // check if email addy is already in use
        $users_query = "SELECT count(id) FROM `users` WHERE email=?";
        $a = DB::run($users_query, [$email])->fetchColumn();
        if ($a != 0) {
            $message = sprintf(LANG["EMAIL_ADDRESS_INUSE_S"], $email);
        }
        return $message;
    }

}