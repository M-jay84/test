<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;

/*
* Snatched Table Data
*/
class SnatchedLogic
{
    // Completed Snatched
    public static function completed($id)
    {
        $snatched_query =
            "SELECT users.id 
            FROM completed 
            LEFT JOIN users 
            ON completed.userid = users.id 
            WHERE users.enabled = 'yes' 
            AND users.status = 'confirmed' AND completed.torrentid = $id"
        ;
        $stmt = DB::run($snatched_query)->fetchAll();
        return  $stmt;
    }

    // Admin Delete By IDs
    public static function deleteByIds()
    {
        // Delete
        if (Input1('do') == 'delete') {
            $ids = multiIds('ids');
            $snatched_query = "UPDATE snatched SET last_time = ? AND hnr = ? AND done = ? WHERE `sid` IN (?)";
            DB::run($snatched_query, [86400, 'no', 'yes', $ids]);
            message('adminsnatch', LANG["DEL_COMPLETE"]);
        }
    }

    // Get Snatched Data
    public static function join($where, $value)
    {
        $snatched_query = "SELECT COUNT(*) FROM snatched WHERE $where";
        $count = DB::run($snatched_query, [$value])->fetchColumn();
        if ($where == 'snatched.uid = ?') {
            list($pager, $limit) = pagination(25, $count, "/snatch/$value/user&");
        } elseif ($where == 'snatched.tid = ?') {
            list($pager, $limit) = pagination(25, $count, "/snatch/$value/torrent&");
        } elseif ($where == 'snatched.tid = ? AND snatched.completed != 0') {
            list($pager, $limit) = pagination(25, $count, "/snatch/$value/complete&");
        }

        $snatched_query =
            "SELECT
            users.id, users.username, users.class,
            snatched.uid as uid,
             snatched.tid as tid,
             snatched.sid,
             snatched.upload,
             snatched.download,
             snatched.start_time,
             snatched.upload_time,
             snatched.last_time,
             snatched.completed,
             snatched.hnr,
             torrents.name,
            (
                SELECT seeder
                FROM peers
                WHERE torrent = tid AND userid = uid
                LIMIT 1
            ) AS seeding
            FROM snatched
            INNER JOIN users ON snatched.uid = users.id
            INNER JOIN torrents ON snatched.tid = torrents.id
            WHERE users.status = 'confirmed' AND torrents.banned = 'no' AND $where
            ORDER BY start_time 
            DESC $limit";
        $res = DB::run($snatched_query,
            [$value]
        );
        //)->fetchAll();

        $data = [
            'stmt' => $res,
            'pager' => $pager,
            'count' => $count
        ];

        return $data;
    }

    // Trade Snatch
    public static function trade($uid)
    {
        $snatched_query =
            "SELECT  snatched.tid as tid, torrents.name,  torrents.size, 
	                snatched.upload, snatched.download, snatched.last_time, 
		            snatched.hnr, users.uploaded, users.downloaded, users.seedbonus, users.modcomment 
		    FROM snatched 
		    INNER JOIN users ON snatched.uid = users.id 
		    INNER JOIN torrents ON snatched.tid = torrents.id
		    WHERE users.status = 'confirmed'
		    AND snatched.uid = '$uid'
		    AND snatched.hnr = 'yes'
		    AND snatched.done = 'no'
		    ORDER BY start_time DESC";
        $res = DB::run($snatched_query)->fetchAll();

        return $res;
    }

    // Submit Trade
    public static function submitTrade($current_user)
    {
        $torid = Input1('torid');
        $torsize = Input1('size');
        if ($_POST["requestpoints"]) {
            $modcomment = date("d-M-Y") . " - " . LANG["DELETED_RECORDING"] . ": " . $torid . " with " . $torsize . " " . LANG["POINTS_OF_SEED_BONUS"];
            $snatched_query = "UPDATE users SET seedbonus = seedbonus - ? AND modcomment = ? WHERE id = ?";
            DB::run($snatched_query, [$torsize, $modcomment, $current_user->id]);
            $snatched_query = "UPDATE snatched SET last_time = ? AND hnr =? AND done =? WHERE tid=? AND uid =?";
            DB::run($snatched_query, [86400, 'no', 'yes', $torid,  $current_user->id]);
            LogsLogic::write("<a href=" . URLROOT . "/profile/" . $current_user->id . "><b>" . $current_user->username . "</b></a> " . LANG["DELETED_RECORDING"] . ": <a href=" . URLROOT . "/torrent/$torid><b>$torid</b></a>  " . LANG["HIT_AND_RUN_WITH"] . " <b>$torsize</b> " . LANG["POINTS_OF_SEED_BONUS"] . "");
            message('snatch/trade', LANG["DEL_COMPLETE"]);
        } elseif ($_POST["requestupload"]) {
            $modcomment = date("d-M-Y") . " - " . LANG["DELETED_RECORDING"] . ": " . $torid . " with " . mksize($torsize) . " " . LANG["OF_UPLOAD"];
            $users_query = "UPDATE users SET uploaded = uploaded - '$torsize' AND modcomment = ? WHERE id = ?";
            DB::run($users_query, [$modcomment, $current_user->id]);
            $snatched_query = "UPDATE snatched SET last_time = ? AND hnr =? AND done =? WHERE tid=? AND uid =?";
            DB::run($snatched_query, [86400, 'no', 'yes', $torid,  $current_user->id]);
            LogsLogic::write("<a href=" . URLROOT . "/profile/" . $current_user->id . "><b>" . $current_user->username . "</b></a> " . LANG["DELETED_RECORDING"] . ": <a href=" . URLROOT . "/torrent/$torid><b>$torid</b></a> " . LANG["HIT_AND_RUN_WITH"] . " <b>$torsize</b> " . LANG["OF_UPLOAD"] . "");
            message('snatch/trade', LANG["DEL_COMPLETE"]);
        } else {
            message('snatch/trade', LANG["ACTION_FAILED"]);
        }
    }

}