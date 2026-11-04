<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\GroupsLogic;

/*
* Users Search
*/
class UsersearchLogic
{
    // Admin Confirm User Reg
    public static function searchUsers()
    {
        // Get Search Data
        $data = [
            "name" => Input1('name'),
            "ratio" => Input1('ratio'),
            "member" => Input1('member'),
            "email" => Input1('email'),
            "ip" => Input1('ip') ?? $_GET['ip'],
            "account" => Input1('account'),
            "uploaded" => Input1('uploaded'),
            "class" => Input1('class'),
            "downloaded" => Input1('downloaded'),
            "warned" => Input1('warned')
        ];

        
        $url = "?"; // assign url
        $wherea = []; // assign conditions
        $params = []; // assign vars

        if (!empty($data['name'])) {
            $wherea[] = "users.username LIKE '%$data[name]%'";
        }

        if (!empty($data['ratio'])) {
            $ratio = $data['ratio'];
            $wherea[] = "users.uploaded/users.downloaded > $ratio";
        }

        if (!empty($data['member'])) {
            $wherea[] = "users.status = '$data[member]'";
        }

        if (!empty($data['email'])) {
            $wherea[] = "users.email LIKE '%$data[email]%'";
        }

        if (!empty($data['ip'])) {
            $wherea[] = "users.ip LIKE '%$data[ip]%'";
        }

        if (!empty($data['account'])) {
            $wherea[] = "users.enabled = '$data[account]'";
        }

        if (!empty($data['uploaded'])) {
            $uploaded = strtobytes($data['uploaded']);
            $wherea[] = "users.uploaded > $uploaded";
        }

        if (!empty($data['class'])) {
            $wherea[] = "users.class = '$data[class]'";
        }

        if (!empty($data['downloaded'])) {
            $downloaded = strtobytes($data['downloaded']);
            $wherea[] = "users.downloaded > $downloaded";
        }

        if (!empty($data['warned'])) {
            $wherea[] = "users.warned = '$data[warned]'";
        }

        $where = implode(' AND ', $wherea);
        if ($where != '') {
            $where = 'WHERE ' . $where;
        }

        // Pagination
        $users_query = "SELECT COUNT(`id`) FROM `users` $where";
        $count = DB::run($users_query)->fetchcolumn();
        list($pager, $limit) = pagination(25, $count, URLROOT . "/adminusersearch&");
        $users_query = "SELECT * FROM `users` $where $limit";
        $sql = DB::run($users_query)->fetchAll();

        $groups_query = "SELECT group_id,groups.level FROM `groups`";
        $groups = DB::run($groups_query);

        $array = [
            'results' => $sql,
            'pager' => $pager,
            'inputs' => $data,
            'groups' => $groups
        ];

        return $array;
    }

    // Get Members
    public static function submitUser($current_user)
    {
        $enable = Input1('enable');
        $disable = Input1('disable');
        $unwarn = Input1('unwarn');
        $warn = Input1('warn');
        $add_comment = Input1('add_comment');
        $ids = multiIds('ids');

        if ($disable != '') {
            $users_query = "UPDATE users SET enabled = ? WHERE id IN (?)";
            DB::run($users_query, ['no', $ids]);
        }

        if ($enable != '') {
            $users_query = "UPDATE users SET enabled = ? WHERE id IN (?)";
            DB::run($users_query, ['yes', $ids]);
        }

        if ($unwarn != '') {
            foreach ($_POST["ids"] as $user_id) {
                MessagesLogic::newinsert(['poster' => 0, 'sender' => 0, 'subject' => 'Warning Removed', 'receiver' => $user_id, 'added' => CONFIG['TT_DATE'], 'msg' => "Your Warning Has Been Removed"]);
            }
            $modcomment = date("Y-m-d") . " - Warning Removed By " . $current_user->username;
            $users_query = "UPDATE users SET modcomment = ? WHERE id IN (?)";
            DB::run($users_query, [$modcomment, $ids]);
            $users_query = "UPDATE users SET warned => ? WHERE id IN (?)";
            DB::run($users_query, ['no', $ids]);
        }

        if ($warn != '') {
            if (empty($add_comment)) {
                message('adminusersearch', LANG["MISSING_FORM_DATA"], 1);
            }
            foreach ($_POST["ids"] as $user_id) {
                MessagesLogic::newinsert(['poster' => 0, 'sender' => 0, 'subject' => 'New Warning', 'receiver' => $user_id, 'added' => CONFIG['TT_DATE'], 'msg' => "You have received a warning, Reason: $add_comment"]);
            }
            $modcomment = date("Y-m-d") . " - Warned by " . $current_user->username . ".\nReason: $add_comment";
            $users_query = "UPDATE users SET modcomment = ? WHERE id IN (?)";
            DB::run($users_query, [$modcomment, $ids]);
            $users_query = "UPDATE users SET warned = ? WHERE id IN (?)";
            DB::run($users_query, ['yes', $ids]);
        }

    }
}