<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use Bootstrap\Libs\Validate;
use PDO;

/*
* Download Logic
*/
class EmailLogic
{
    // Country Flag //todo view
    public static function emailAll($current_user)
    {
        // Check User Input
        $msg_log = "Sent to classes: ";
        set_time_limit(0);
        $subject = Input1('subject');
        $body = format_comment($_POST["body"]);

        // Check Correct Input
        if (!$subject || !$body) {
           message('adminemail', LANG["MISSING_ID"]);
        }

        if (!@count($_POST["groups"])) {
            message('adminemail', LANG["NOTHING_SELECTED"]);
        }

        // Get Group Data
        $ids = array_map("intval", $_POST["groups"]);
        $ids = implode(", ", $ids);
        $res_log = GroupsLogic::fetchlevel($ids);
        foreach ($res_log as $row_log) {
            $msg_log .= $row_log["level"] . ", ";
        }
        
        // Send Email
        $users_query = "SELECT u.email FROM users u LEFT JOIN `groups` g ON u.class = g.group_id WHERE u.enabled = 'yes' AND u.status = 'confirmed' AND u.class IN ($ids)";
        $res = DB::run($users_query);
        $siteemail = CONFIG['SITEEMAIL'];
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $TTMail = new TTMail();
            $TTMail->Send($row["email"], $subject, $body, "Content-type: text/html; charset=utf-8", "-f$siteemail");
        }

        LogsLogic::write("<b><font color='Magenta'>A Mass E-Mail</font> was sent by (<font color='Navy'>".$current_user->username."</font>) $msg_log<b>");
        
        message('adminemail', "<b><font color='#ff0000'>Mass mail sent....</font></b>");
    }

}