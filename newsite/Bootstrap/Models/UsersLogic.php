<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\GroupsLogic;
use Bootstrap\Models\ReadLogic;
use Bootstrap\Models\ReportsLogic;
use Bootstrap\Models\WarningsLogic;
use PDO;

/*
* Users Table Data
*/
class UsersLogic
{
    // Admin Confirm User Reg
    public static function confirmReg()
    {
        $ids = multiIds('ids');
        $users_query = "UPDATE `users` SET `status` = ? WHERE `status` = ? AND `invited_by` = ? AND `id` IN (?)";
        DB::run($users_query, ['confirmed', 'pending', 0, $ids])->fetchAll();
        message('adminconfirmreg', LANG["ACTION_COMPLETE"]);
    }

    // Get Members
    public static function getMembers()
    {
        // Check User Input
        $search = (isset($_GET['search'])) ? trim(filter_input(INPUT_GET, 'search')) : '';
        $class = (int) (isset($_GET['class'])) ? $_GET['class'] : 0;
        $letter = (isset($_GET['letter'])) ? trim(filter_input(INPUT_GET, 'letter')) : '';

        // Search Users
        $q = $query = null;
        if ($search) {
            $query = "username LIKE '$search%' AND status='confirmed'";
            if ($search) {
                $q = "search=" . htmlspecialchars($search);
            }
        } elseif ($letter) {
            if (strlen($letter) > 1) {
                unset($letter);
            }
            if ($letter == "" || strpos("abcdefghijklmnopqrstuvwxyz", $letter) === false) {
                unset($letter);
            } else {
                $query = "username LIKE '$letter%' AND status='confirmed'"; // todo esc
            }
            $q = "letter=$letter";
        }
        if (!$query) {
            $query = "status='confirmed'";
        }
        if (!$class) {
            unset($class);
        } else {
            $query .= " AND class=$class";
            $q .= ($q ? "&amp;" : "") . "class=$class";
        }

        $data = ['query' => $query, 'q' => $q];
        // Pagination
        $users_query = "SELECT COUNT(*) FROM users WHERE $data[query]";
        $count = DB::run($users_query)->fetchcolumn();
        list($pager, $limit) = pagination(25, $count, URLROOT . "/users/members?$data[q]&");
        $users_query = "SELECT users.*, groups.level FROM users INNER JOIN `groups` ON groups.group_id=users.class WHERE $data[query] ORDER BY username $limit";
        $results = DB::run($users_query)->fetchAll();

        // Get Group Data
        $groups_query = "SELECT group_id, level FROM `groups` ORDER BY group_id ASC";
        $res = DB::run($groups_query);

        $array = [
            'groups' => $res,
            'array' => $results,
            'pager' => $pager
        ];

        return $array;
    }

    // Function That Removes All From An Account
    public static function deleteUser($userid)
    {
        $users_query = "DELETE FROM users WHERE id' = ?";
        DB::run($users_query, [$userid]);
        $warnings_query = "DELETE FROM warnings WHERE userid' => ?";
        DB::run($warnings_query, [$userid]);
        $ratings_query = "DELETE FROM ratings WHERE user";
        DB::run($ratings_query, [$userid]);
        $peers_query = "DELETE FROM peers WHERE userid' = ? ";
        DB::run($peers_query, [$userid]);
        $reports_query = "DELETE FROM `reports` WHERE votedfor = ? AND type=?";
        DB::run($reports_query, [$userid, 'user']);
        $readposts_query = "DELETE FROM `forum_readposts` WHERE userid=?";
        DB::run($readposts_query, [$userid]);
        $snatched_query = "DELETE FROM snatched WHERE uid' = ?";
        DB::run($snatched_query, [$userid]);
    }

    // Message ajax member search
    public static function getUserName()
    {
        $users_query = "SELECT id,username FROM users WHERE username LIKE '" . $_POST["keyword"] . "%' ORDER BY username LIMIT 0,6";
        $stmt = DB::run($users_query);
		return $stmt;
	}

    // Message ajax member search
    public static function gmaxUser()
    {
        // Check Max Users
        $invite_query = "SELECT count(id) FROM `users` WHERE enabled = ?";
        $users = DB::run($invite_query, ['yes'])->fetchColumn();
        if ($users >= CONFIG['MAXUSERSINVITE']) {
            message(URLROOT, "Sorry, The current user account limit (" . number_format(CONFIG['MAXUSERSINVITE']) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");
        }

		return true;
	}
	
    // Message ajax member search
    public static function usersToConfirm()
    {
        $peers_query = "SELECT count(id) FROM `users` WHERE status = ? AND invited_by !=?";
        $count = DB::run($peers_query, ['pending',0])->fetchColumn();
        list($pager, $limit) = pagination(25, $count, 'adminconfirmreg?');
        $users_query = "SELECT id,username,email,added,ip FROM `users` WHERE status=? AND invited_by=? ORDER BY `added` DESC $limit";
        $users = DB::run($users_query, ['pending',0]);
        
        $stmt = ['users'=>$users,'count'=>$count,'pager'=>$pager];
		return $stmt;
	}
    public static function inactiveUsers()
    {
        $cday = Input1('cday', 'int') ?? 30;
        $dt = date("Y-m-d H:i:s", CONFIG['TT_TIME'] - ($cday * 86400));
        
        // Get Users Data
        $users_query = "SELECT id,username,class,email,uploaded,downloaded,last_access,ip,added FROM `users` WHERE last_access<? AND status=? AND enabled=? ORDER BY  last_access DESC";
        $users = DB::run($users_query, [$dt, 'confirmed', 'yes']);
        $count = $users->rowCount();
        $stmt = ['users'=>$users,'count'=>$count,'days'=>$cday];
		return $stmt;
	}
	
    // User whew inactive?
    public static function selectWhereIn($option = false)
    {
        if ($option) {
            $users_query = "SELECT id, email , username, added, last_access FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY last_access DESC ";
			$stmt = DB::run($users_query);
		} else {
            $users_query = "SELECT id, modcomment FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY id DESC ";
			$stmt = DB::run($users_query);
		}
		return $stmt;
	}

    public static function inactiveUsersSubmit($username)
    {
        $sitename = CONFIG['SITENAME'];
        $siteurl = URLROOT;
        
            // Check User Input
            $action = Input1('action');
            $cday = Input1('cday', 'int');
            $days = 30; // Number of days of inactivite

            // Check Correct Input
            if (!is_numeric($cday)) {
                message('admininactive', LANG["ACTION_FAILED"]);
            }

            if (empty($_POST["userid"]) && (($action == "deluser") || ($action == "mail") || ($action == "disable"))) {
                message('admininactive', ("NOTHING_SELECTED"));
            }

            if ($action == "cday" && ($cday > $days)) {
                $days = $cday;
            }

            // Delete
            if ($action == "deluser" && (!empty($_POST["userid"]))) {
                $ids = implode(", ", $_POST['userid']);
                $users_query = "DELETE FROM `users` WHERE `id` IN (?)";
                DB::run($users_query, [$ids]);
                message('admininactive', LANG["SUCCESS"]);
            }

            // Disable
            if ($action == "disable" && (!empty($_POST["userid"]))) {
                $res = UsersLogic::selectWhereIn();
                while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
                    $id = 0 + $arr["id"];
                    $cname = $username;
                    $modcomment = $arr["modcomment"];
                    $modcomment = date("Y-m-d") . " - Disabled for inactivity by $cname.n" . $modcomment;
                    $users_query = "UPDATE users SET modcomment = ? AND enabled = ? WHERE id=?";
                    DB::run($users_query, [$modcomment,'no',$id]);
                }
                message('admininactive', LANG["UPDATE_COMPLETE"]);
            }

            // Email
            if ($action == "mail" && (!empty($_POST["userid"]))) {
                $res = UsersLogic::selectWhereIn(true);
                while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
                    $id = $arr["id"];
                    $username = htmlspecialchars($arr["username"]);
                    $email = htmlspecialchars($arr["email"]);
                    $added = $arr["added"];
                    $last_access = $arr["last_access"];
                    $subject = "Warning for inactive account on $sitename";

                    // Send Email
                    $body = file_get_contents(VIEW_PATH . "/email/changeemail.php");
                    $body = str_replace("%username%", $username, $body);
                    $body = str_replace("%sitename%", $sitename, $body);
                    $body = str_replace("%siteurl%", $siteurl, $body);

                    $body = str_replace("%added%", $$added, $body);
                    $body = str_replace("%last_access%", $last_access, $body);
                    $TTMail = new TTMail();
                    $mail = $TTMail->Send($email, $subject, $body, "From: " . CONFIG['SITEEMAIL'] . "", "-f" . CONFIG['SITEEMAIL'] . "");
                }

                if ($mail) {
                    message('admininactive', "Messages sent.");
                } else {
                    message('admininactive',  "Try again.");
                }

            }
	}
}