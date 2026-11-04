<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use Bootstrap\Libs\Validate;
use PDO;

/*
* Account Data
*/
class RecoverLogic
{
    // Recover Password
    public static function recoverPassword()
    {
        // Check Inputs
		$email = Input1('email', 'email');

        captcha_response($_POST['g-recaptcha-response'] ?? '');

        // Get User By Email
        $users_query = "SELECT id, username, emai FROM `users` WHERE email = ?";
        $arr = DB::run($users_query, [$email])->fetch(PDO::FETCH_ASSOC);
        if (!$arr) {
            message(URLROOT, LANG["EMAIL_ADDRESS_NOT_FOUND"]);
        }

        // Send Email
        if ($arr) {
            $sec = mksecret();
            $id = $arr['id'];
            $username = $arr['username']; // 06/01
            $emailmain = CONFIG['SITEEMAIL'];
            $url = URLROOT;
            $body = LANG["SOMEONE_FROM"] . " " . $_SERVER["REMOTE_ADDR"] . " " . LANG["MAILED_BACK"] . " ($email) " . LANG["BE_MAILED_BACK"] . " \r\n\r\n " . LANG["ACCOUNT_INFO"] . " \r\n\r\n " . LANG["USERNAME"] . ": " . $username . " \r\n " . LANG["CHANGE_PSW"] . "\n\n$url/recover/confirm/$id/$sec\n\n\n" . $url . "\r\n";
            $TTMail = new TTMail();
            $TTMail->Send($email, LANG["ACCOUNT_DETAILS"], $body, "", "-f$emailmain");
            $users_query = "UPDATE users SET secret = ? WHERE email = ? ";
            DB::run($users_query, [$sec, $email]);
            message(URLROOT, sprintf(LANG['MAIL_RECOVER'], htmlspecialchars($email)));
        }

        message(URLROOT, LANG["NOTHING_FOUND"]);
    }

    // Confirm Recover Password
    public static function changePassword($id, $secret)
    {
        // Check Inputs
        captcha_response($_POST['g-recaptcha-response'] ?? '');
        $password = Input1('password');
        $password1 = Input1('password1');
        if (empty($password) || empty($password1)) {
             message(URLROOT, LANG["MISSING_FORM_DATA"]);
        } elseif ($password != $password1) {
            message(URLROOT, LANG["PASSWORD_NO_MATCH"]);
        }
        // Check User
        $users_query = "SELECT count(id) FROM `users` WHERE id=? AND secret=?";
        $count = DB::run($users_query, [$id,$secret])->fetchColumn();
        if ($count != 1) {
            message(URLROOT, LANG["NO_SUCH_USER"]);
        }

        // Update User
        $newsec = mksecret();
        $wantpassword = password_hash($password, PASSWORD_BCRYPT);
        $users_query = "UPDATE users SET password = ? AND secret = ? WHERE id=? AND secret = ? ";
        DB::run($users_query, [$wantpassword, $newsec, $id, $secret]);
        message(URLROOT, LANG["PASSWORD_CHANGED_OK"]);
    }

}