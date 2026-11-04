<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use Bootstrap\Libs\Validate;

/*
* Profile Change Data
*/
class ProfilechangeLogic
{

    // Account Add Avatar todo files
    public static function addavatar($user_id)
    {
        $maxsize = 130 * 130;
        $file = $_FILES['file']['name'];
        $ext = substr($file, -3);
        $allowedfiles = array("gif","jpg","png");

        if($_FILES['file']['size'] > $maxsize) {
            message(("profile/$user_id"), LANG["INVAILD_FILE_SIZE_IMAGE"]);
        }

        if (in_array($ext, $allowedfiles)) {
            move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_PATH . "/avatars/$user_id.$ext");
            $avatar = URLROOT . "/uploads/avatars/$user_id.$ext";
            $users_query = "UPDATE users SET avatar=? WHERE id=?";
            DB::run($users_query, [$avatar, $user_id]);
            message(("profile/$user_id"), LANG["UP_AVATAR"] . " OK");
        }
    }

    // Account password change
    public static function submitPassword($id)
    {
        $chpassword = Input1('chpassword');
        $passagain = Input1('passagain');
        if ((!$chpassword) || (!$passagain)) {
            message(("profile/$id"), LANG["MISSING_FORM_DATA"]);
        } elseif (strlen($chpassword) < 6) {
            message(("profile/$id"), LANG["PASSWORD_SHORT"]);
        } elseif ($chpassword != $passagain) {
            message(("profile/$id"), LANG["PASSWORDS_NOT_MATCH"]);
        }
        $chpassword = password_hash($chpassword, PASSWORD_BCRYPT);
        $secret = mksecret();
        
        $users_query = "UPDATE users SET password = ? AND secret=? WHERE id=?";
        $in = DB::run($users_query, [$chpassword,$secret,$id]);
        if ($in > 0) {
            message('authorize/logout', LANG["PASSWORD_CHANGED_OK"]);
        } else {
            message(("profile/$id"), LANG["ACTION_FAILED"]);
        }
    }

    // Account password change
    public static function changeemail($id)
    {
        $email = Input1('email', 'email');

        // Set Secret
        $sec = mksecret();
        $obemail = rawurlencode($email);
        $sitename = URLROOT;

        // Send Email
        $body = file_get_contents(VIEW_PATH . "/email/changeemail.php");
        $body = str_replace("%usersname%", $id, $body);
        $body = str_replace("%sitename%", $sitename, $body);
        $body = str_replace("%usersip%", $_SERVER["REMOTE_ADDR"], $body);
        $body = str_replace("%usersid%", $id, $body);
        $body = str_replace("%userssecret%", $sec, $body);
        $body = str_replace("%obemail%", $obemail, $body);
        $body = str_replace("%newemail%", $email, $body);
        $TTMail = new TTMail();
        $TTMail->Send($email, "".CONFIG['SITENAME']." profile update confirmation", $body, "From: " . CONFIG['SITEEMAIL'] . "", "-f" . CONFIG['SITEEMAIL'] . "");

        // Update User Secret
        $users_query = "UPDATE users SET editsecret = ? WHERE id=?";
        DB::run($users_query, [$sec, $id]);
        message(("profile/$id"), LANG["EMAIL_CHANGE_SEND"]);
    }

}