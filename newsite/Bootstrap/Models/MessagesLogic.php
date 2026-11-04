<?php

namespace Bootstrap\Models;

use PDO;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\GroupsLogic;
use Bootstrap\Models\LogsLogic;

/*
* Messages Table Data
*/
class MessagesLogic
{
    // Add Message
    public static function newinsert($data)
    {
        DB::insert('messages', $data);
        // email notif
        $users_query = "SELECT id, username, acceptpms, notifs, email FROM `users`  WHERE id = ?";
        $user = DB::run($users_query, [$data['receiver']])->fetch(PDO::FETCH_ASSOC);

        if (strpos($user['notifs'], '[pm]') !== false) {
            $url = URLROOT;
            $body = "You have received a PM\n\nYou can use the URL below to view the message (you may have to login).\n\n$url/message/mailbox\n\n".CONFIG['SITENAME']."";
            $TTMail = new TTMail();
            $TTMail->Send($user["email"], "You have received a PM", $body, "From: " . CONFIG['SITEEMAIL'] . "", "-f" . CONFIG['SITEEMAIL'] . "");
        }
    }

    // Get Messages
    public static function msgPagination($type, $id)
    {
        switch ($type) {
            case 'inbox':
                $where = "`receiver` = ".$id." AND `location` IN ('in','both') ORDER BY added DESC";
                $messages_query = "SELECT COUNT(*) FROM messages WHERE $where";
                $count = DB::run($messages_query)->fetchColumn();
                list($pagerbuttons, $limit) = pagination(25, $count, URLROOT."/message/mailbox/inbox&");
                break;
            case 'outbox':
                $where = "`sender` = ".$id." AND `location` IN ('out','both') ORDER BY added DESC";
                $messages_query = "SELECT COUNT(*) FROM messages WHERE $where";
                $count= DB::run($messages_query)->fetchColumn();
                list($pagerbuttons, $limit) = pagination(25, $count, URLROOT."/message/mailbox/outbox&");
                break;
        }
        $messages_query = "SELECT * FROM messages WHERE $where $limit";
        $messages = DB::run($messages_query)->fetchAll();

        $arr = ['pager' => $pagerbuttons, 'messages' => $messages];

        return $arr;
    }

    // Add Message Type
    public static function insertByType($id)
    {
        // Check User Input
        $receiver = Input1('receiver');
        $subject = Input1('subject');
        $body = Input1('body');
        $history = Input1('history', 'int');

        // Check Correct Input
        if (strlen($body) < 5 || strlen($subject) < 3) {
            message('message/mailbox/inbox', LANG['SMALL_INPUT']);
        }

        // Get User Id
        if (!is_numeric($receiver)) {
            $users_query = "SELECT id FROM `users` WHERE username=?";
            $receiver = DB::run($users_query, [$receiver])->fetchColumn();
        }
        
        if ($receiver == 0 && $_REQUEST['Update'] == 'create') {
            message('message/mailbox/inbox', LANG['MISSING_ID']);
        }
        

        switch ($_REQUEST['Update']) {
            case 'create':
                if (isset($_POST['save'])) {
                    MessagesLogic::newinsert(['sender'=>$id, 'receiver'=>$receiver, 'added'=>CONFIG['TT_DATE'], 'subject'=>$subject, 'msg'=>$body, 'unread'=>'yes', 'location'=>'both', 'history' => $history]);
                } else {
                    MessagesLogic::newinsert(['sender'=>$id, 'receiver'=>$receiver, 'added'=>CONFIG['TT_DATE'], 'subject'=>$subject, 'msg'=>$body, 'unread'=>'yes', 'location'=>'in', 'history' => $history]);
                }
                message('message/mailbox/outbox', LANG['MESSAGES_SENT']);
                break;
            case 'draft':
                MessagesLogic::newinsert(['sender'=>$id, 'receiver'=>$receiver, 'added'=>CONFIG['TT_DATE'], 'subject'=>$subject, 'msg'=>$body, 'unread'=>'no', 'location'=>'draft']);
                message('message/mailbox/draft', LANG['SAVED_DRAFT']);
                break;
            case 'template':
                MessagesLogic::newinsert(['sender'=>$id, 'receiver'=>$receiver, 'added'=>CONFIG['TT_DATE'], 'subject'=>$subject, 'msg'=>$body, 'unread'=>'no', 'location'=>'template']);
                message('message/mailbox/templates', LANG['SAVED_TEMPLATE']);
                break;
        }
    }

    // Delete Message
    public static function delete($type, $current_user)
    {
        $ids = multiIds('del');

        if ($type == 0 && $current_user->isStaff) {
            die('todo');
        } elseif ($_POST["read"]) {
            $messages_query = "UPDATE messages SET unread' => ? WHERE id IN (?)";
            DB::run($messages_query, ['no', $ids]);
        } elseif ($type == 'inbox') {
            $messages_query = "DELETE FROM messages WHERE `location` = ? AND `receiver` = ? AND `id` IN (?)";
            DB::run($messages_query, ['in', $current_user->id, $ids]);
            $messages_query = "UPDATE messages SET `location` = ? WHERE `location` = ? AND `receiver` = ? AND `id` IN (?)";
            DB::run($messages_query, ['out', 'both', $current_user->id, $ids]);
        } elseif ($type == 'outbox') {
            $messages_query = "UPDATE messages SET `location` =  WHERE `location` = ? AND `sender` = ? AND `id` IN (?)";
            DB::run($messages_query, ['in', 'both', $current_user->id, $ids]);
            $messages_query = "DELETE FROM messages WHERE `location` IN ('out', 'draft', 'template') AND `sender` = ? AND `id` IN (?)";
            DB::run($messages_query, [$current_user->id, $ids]);
        }
        message(("message/mailbox/$type"), LANG["SUCCESS"]);
    }

    // Get Message Id
    public static function getById($id, $userid)
    {
        $messages_query = "SELECT * FROM `messages` WHERE id = ?";
        $arr = DB::run($messages_query, [$id])->fetch(PDO::FETCH_ASSOC);
        
        if ($arr["history"] != 0) {
            $messages_query= "SELECT * FROM `messages` WHERE history=?";
            $history = DB::run($messages_query, [$arr["history"]])->fetchAll();
        }

        // Check User
        if ($arr["sender"] != $userid && $arr["receiver"] != $userid) {
            message(URLROOT, LANG['NO_PERMISSION']);
        }
        
        // Mark Read
        if ($arr["unread"] == "yes" && $arr["receiver"] == $userid) {
            $messages_query = "UPDATE messages SET unread = ? WHERE id=? AND receiver = ? ";
            DB::run($messages_query, ['no', $id, $userid]);
        }

		$arr["replyto"] = (($userid == $arr["receiver"])) ? $arr["sender"] : $arr["receiver"];
        $arr['previous'] = $history;

        return $arr;
    }

    // Update Message
    public static function update($id, $userid)
    {
        // Get Message
        $messages_query = "SELECT * FROM `messages` WHERE id = ?";
        $row = DB::run($messages_query, [$id])->fetch(PDO::FETCH_ASSOC);
        
        if ($row["sender"] != $userid && $row["receiver"] != $userid) {
            message(URLROOT, LANG['NO_PERMISSION']);
        }
        if (!$row) {
            message('message/mailbox/inbox', LANG["MISSING_ID"]);
        }
        
        // Submit Form
        if (!empty($_POST)) {
            $msg = isset($_POST['msg']) ? $_POST['msg'] : '';
            // Update Message
            $messages_query = "UPDATE messages SET msg = ? WHERE id=?";
            DB::run($messages_query, [$msg,$id]);
            message('message/mailbox/inbox', LANG["UPDATE_COMPLETE"]);
        }
        message('message/mailbox/inbox', LANG["EDIT_FAILED"]);
    }

    // Admin Delete Messages
    public static function deleteByIds()
    {
        $ids = multiIds('ids');
        $messages_query = "DELETE FROM `messages` WHERE `id` IN (?)";
        DB::run($messages_query, [$ids]);
        message('adminmessage', LANG["DEL_COMPLETE"]);
    }

    // Admin Send Multiple Messages
    public static function multiPM($userid)
    {
        $sender_id = Input1('sender_id');
        $receiver = Input1('receiver');
        $id = Input1('id');
        $msg = Input1('msg');
        $subject = Input1('sebject');
        // Check Correct Input
        if (!$msg) {
            message('adminmessage/masspm', LANG["MISSING_FORM_DATA"]);
        }

        
        $updateset = array_map("intval", $_POST['classes']);
        $users_query = "SELECT id FROM users WHERE class IN (" . implode(",", $updateset) . ")";
        $query = DB::run($users_query);// AND enabled = 'yes' AND status = 'confirmed'
        while ($dat = $query->fetch(PDO::FETCH_ASSOC)) {
            $messages_query = "INSERT INTO `messages` (sender,receiver,added,msg,subject) VALUES (?,?,?,?,?)";
            DB::run($messages_query,
            [$sender_id, $dat['id'], CONFIG['TT_DATE'], $msg, $subject]);
        }
        
        LogsLogic::write("A Mass PM was sent by " . $userid . "");
        message('adminmessage/masspm', LANG["SUCCESS"]);
    }

    // Admin Get All Messages
    public static function getAll()
    {
        // Pagination
        $messages_query = "SELECT COUNT(*) FROM messages WHERE location in ('in', 'both')";
        $count = DB::run($messages_query)->fetchColumn();
        list($pager, $limit) = pagination(40, $count, '/adminmessage&');
        $messages_query = "SELECT * FROM messages WHERE location in ('in', 'both') ORDER BY id DESC $limit";
        $stmt = DB::run($messages_query)->fetchAll();

        // Get Groups
        $groups_query = "SELECT group_id,level FROM `groups`";
        $groups =DB::run($groups_query);
        $array = [
            'res' => $stmt,
            'pager' => $pager,
            'groups' => $groups
        ];
        return $array;
    }

}