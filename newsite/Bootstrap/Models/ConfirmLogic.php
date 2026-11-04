<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

/*
* Account Data
*/
class ConfirmLogic
{
    // Profile Email Reset Confirm
    public static function confirmprofileemail($id, $secret, $email)
    {
        $users_query = "SELECT editsecret FROM `users` WHERE enabled=? AND editsecret=? AND status=? AND id=?";
        $editsecret = DB::run($users_query, ['yes', 'confirmed', $secret, $id])->fetchColumn();
       
        if (!$editsecret) {
            message(URLROOT,  LANG["NOTHING_FOUND"]);
        }

        if ($secret != $editsecret) {
            message(URLROOT,  LANG["NOTHING_FOUND"]);
        }

        // Update User Editsecret
        $users_query = "UPDATE users SET editsecret=? AND email=? AND editsecret=? WHERE id=?";
        DB::run($users_query, ['', $email, $id, $editsecret]);
        message(URLROOT, LANG["SUCCESS"]);
    }

    // Signup Email Confirm
    public static function confirmemail($id, $secret)
    {
        // Check User
        $users_query = "SELECT password,secret,status FROM `users`  WHERE id = ?";
        $row = DB::run($users_query, [$id])->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $mgs = sprintf(LANG["CONFIRM_EXPIRE"], CONFIG['SIGNUPTIMEOUT'] / 86400);
            message(URLROOT, $mgs);
        }
        if ($row['status'] != "pending") {
            message(URLROOT, LANG["ACCOUNT_ACTIVATED"]);
        }
        if ($secret != $row['secret']) {
            message(URLROOT, LANG["SIGNUP_ACTIVATE_LINK"]);
        }

        // Set Secret
        $secret = mksecret();
        $users_query = "UPDATE users SET secret=? AND status =? WHERE id=? AND secret=?  status=? ";
        $stmt = DB::run($users_query, [$secret, 'confirmed', $id, $row['secret'], 'pending']);
        if ($stmt == 0) {
            message(URLROOT, LANG["SIGNUP_UNABLE"]);
        }
        message('authorize/login', LANG["ACCOUNT_ACTIVATED"]);
    }

}