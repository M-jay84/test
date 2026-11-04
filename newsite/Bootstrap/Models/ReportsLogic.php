<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;

/*
* Report Table Data
*/
class ReportsLogic
{
    // Admin Get Reports
    public static function pagination()
    {
        $pager = array();
        $where = array();
        
        switch ($_GET["type"]) {
            case "user":
                $where[] = "type = 'user'";
                $pager[] = "type=user";
                break;
            case "torrent":
                $where[] = "type = 'torrent'";
                $pager[] = "type=torrent";
                break;
            case "comment":
                $where[] = "type = 'comment'";
                $pager[] = "type=comment";
                break;
            case "forum":
                $where[] = "type = 'forum'";
                $pager[] = "type=forum";
                break;
            case "req":
                $where[] = "type = 'req'";
                $pager[] = "type=req";
                break;
            default:
                break;
        }

        switch ($_GET["completed"]) {
            case 1:
                $where[] = "complete = '1'";
                $pager[] = "complete=1";
                break;
            default:
                $where[] = "complete = '0'";
                $pager[] = "complete=0";
                break;
        }

        $where = implode(" AND ", $where);
        $pager = implode("&amp;", $pager);
        
        $switch = ['where'=>$where,'pager'=>$pager];

        // Pagination
        if ($switch['where'] != '') {
            $reports_query = "SELECT count(id) FROM reports WHERE $switch[where]";
            $count = DB::run($reports_query)->fetchColumn();
            list($pager, $limit) = pagination(25, $count, "$switch[pager]&amp;");
            $reports_query = "SELECT reports.id, reports.dealtwith, reports.dealtby, reports.addedby, reports.votedfor, reports.votedfor_xtra, reports.reason, reports.type, users.username, reports.complete 
                            FROM `reports` INNER JOIN users ON reports.addedby = users.id 
                            WHERE $switch[where] ORDER BY reports.id DESC $limit";
            $stmt = DB::run($reports_query)->fetchAll();
        } else {
            $reports_query = "SELECT count(id) FROM `reports`";
            $count = DB::run($reports_query)->fetchColumn();
            list($pager, $limit) = pagination(25, $count, "$switch[pager]&amp;");
            $reports_query = "SELECT reports.id, reports.dealtwith, reports.dealtby, reports.addedby, reports.votedfor, reports.votedfor_xtra, reports.reason, reports.type, users.username, reports.complete 
                            FROM `reports` INNER JOIN users ON reports.addedby = users.id 
                            ORDER BY reports.id DESC $limit";
            $stmt = DB::run($reports_query)->fetchAll();         
        }

        $array = ['stmt'=>$stmt,'pager'=>$pager];
        return $array;
    }

    // Admin Update Report
    public static function update($userid)
    {
        $ids = multiIds('ids');
        $reports_query = "UPDATE reports SET complete = '1', dealtwith = '1', dealtby = ".$userid." WHERE id IN ($ids)";
        DB::run($reports_query);
        message('adminreport', LANG["ACTION_COMPLETE"]);
     }

    // Admin Delete Report
    public static function deleteByIds()
    {
        
        $ids = multiIds('ids');
        $reports_query = "DELETE FROM `reports` WHERE complete =? `id` IN (?)";

        // todo
        //
        DB::run($reports_query, [$ids, 1]);
        message('adminreport', LANG["SUCCESS"]);
    }

    // Add Report
    public static function reportItem($current_user)
    {
        // Check User Input
        $id = Input1('id', 'int');
        $type = Input1('type');
        $reason = Input1('reason');
        $postid = Input1('postid', 'int');

        // Check User
        if ($current_user->view_users != "yes" && $current_user->id != $id && $type == 'user') {
            message(URLROOT, LANG["NO_USER_VIEW"]);
        } elseif ($current_user->view_torrents != "yes" && CONFIG['MEMBERSONLY'] && $type == 'torrent') {
            message(URLROOT, LANG["NO_TORRENT_VIEW"]);
        } elseif ($current_user->view_forum != "yes" && $current_user->isStaff && $type == 'forum') {
            message(URLROOT, LANG["FORUM_BANNED"]);
        }

        if (!empty($_POST)) {
            if (empty($reason) || empty($id) &&  empty($postid)) {
                message(URLROOT, LANG["YOU_MUST_ENTER_A_REASON"]);
            }

            $reports_query = "SELECT count(id) FROM `reports` WHERE addedby =? AND votedfor=? AND type=? ";
            $res = DB::run($reports_query, [$current_user->id, $id, $type])->fetchColumn();
            if (!$res) {
                if ($type == 'forum') {
                    $reports_query = "INSERT INTO `reports` (addedby,votedfor,votedfor_xtra,type,reason) VALUES (?,?,?,?,?)";
                    DB::run($reports_query, [$current_user->id, $id, $postid, $type, $reason]);
                } elseif ($type == 'other' || $type == 'req' || $type == 'comment') {
                    $reports_query = "INSERT INTO `reports` (addedby,votedfor,votedfor_xtra,type,reason) VALUES (?,?,?,?,?)";
                    DB::run($reports_query, [$current_user->id, $id, 0, $type, $reason]);
                } else {
                    $reports_query = "INSERT INTO `reports` (addedby,votedfor,votedfor_xtra,type,reason) VALUES (?,?,?,?,?)";
                    DB::run($reports_query, [$current_user->id, $id, 0, $type, $reason]);
                }
                    message(URLROOT, "$type: $id, $postid Reason: " . htmlspecialchars($reason) . "<p>Successfully Reported</p>");
            } else {
                message(URLROOT, LANG["YOU_HAVE_ALREADY_REPORTED"]);
            }
        }
    }

}