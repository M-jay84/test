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
 public static function deleteByIds(): never
{
    $ids = multiIds('ids'); // existing helper
    if ($ids === '' || $ids === []) {
        redirect('snatch/admin', __('NOTHING_SELECTED'), 'warning');
    }

    // Prefer proper IN expansion if multiIds returns array
    DB::run(
        "UPDATE snatched SET last_time = ?, hnr = ?, done = ? WHERE sid IN ($ids)",
        [86400, 'no', 'yes']
    );
    redirect('snatch/admin', __('DEL_COMPLETE'), 'success');
}

public static function trade(int $uid): array
{
    return DB::run(
        "SELECT s.tid, t.name, t.size, s.upload, s.download, s.last_time, s.hnr,
                u.uploaded, u.downloaded, u.seedbonus
         FROM snatched s
         INNER JOIN users u ON s.uid = u.id
         INNER JOIN torrents t ON s.tid = t.id
         WHERE u.status = 'confirmed'
           AND s.uid = ?
           AND s.hnr = 'yes'
           AND s.done = 'no'
         ORDER BY s.start_time DESC",
        [$uid]
    )->fetchAll(\PDO::FETCH_ASSOC);
}

public static function submitTrade(object $user): never
{
    $torid = input_int('torid');
    if ($torid < 1) {
        redirect('snatch/trade', __('MISSING_ID'), 'warning');
    }

    $row = DB::run(
        "SELECT t.size, u.seedbonus, u.uploaded
         FROM snatched s
         INNER JOIN torrents t ON t.id = s.tid
         INNER JOIN users u ON u.id = s.uid
         WHERE s.tid = ? AND s.uid = ? AND s.hnr = 'yes' AND s.done = 'no'",
        [$torid, (int) $user->id]
    )->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        redirect('snatch/trade', __('NOTHING_FOUND'), 'warning');
    }

    $size = (int) $row['size'];

    if (!empty($_POST['requestpoints'])) {
        if ((int) $row['seedbonus'] < $size) {
            redirect('snatch/trade', __('NOT_ENOUGH_BONUS'), 'warning');
        }
        $mod = date('d-M-Y') . ' - ' . __('DELETED_RECORDING') . ": {$torid}";
        DB::run(
            'UPDATE users SET seedbonus = seedbonus - ?, modcomment = CONCAT(?, "\n", IFNULL(modcomment,"")) WHERE id = ?',
            [$size, $mod, (int) $user->id]
        );
        DB::run(
            'UPDATE snatched SET last_time = ?, hnr = ?, done = ? WHERE tid = ? AND uid = ?',
            [86400, 'no', 'yes', $torid, (int) $user->id]
        );
        LogsLogic::write($user->username . ' cleared HnR torrent ' . $torid . ' with bonus');
        redirect('snatch/trade', __('DEL_COMPLETE'), 'success');
    }

    if (!empty($_POST['requestupload'])) {
        if ((int) $row['uploaded'] < $size) {
            redirect('snatch/trade', __('NOT_ENOUGH_UPLOAD'), 'warning');
        }
        $mod = date('d-M-Y') . ' - ' . __('DELETED_RECORDING') . ": {$torid}";
        DB::run(
            'UPDATE users SET uploaded = uploaded - ?, modcomment = CONCAT(?, "\n", IFNULL(modcomment,"")) WHERE id = ?',
            [$size, $mod, (int) $user->id]
        );
        DB::run(
            'UPDATE snatched SET last_time = ?, hnr = ?, done = ? WHERE tid = ? AND uid = ?',
            [86400, 'no', 'yes', $torid, (int) $user->id]
        );
        LogsLogic::write($user->username . ' cleared HnR torrent ' . $torid . ' with upload');
        redirect('snatch/trade', __('DEL_COMPLETE'), 'success');
    }

    redirect('snatch/trade', __('ACTION_FAILED'), 'warning');
}

}