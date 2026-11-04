<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;

/*
* Logs Table Data
*/
class LogsLogic
{
    // Add Log
    public static function write($text)
    {
        $log_query = "INSERT INTO log (added, txt) VALUES (?,?)";
        $stmt = DB::run($log_query, [CONFIG['TT_DATE'], $text]);
        return $stmt;
    }

    // Admin Get All Logs
    public static function getAll()
    {
        // Check User Input
        $search = Input1('search');

        // Pagination
        $where = $search != '' ? "WHERE txt LIKE '$search%'" : '';
        $log_query = "SELECT COUNT(*) FROM log $where";
        $count = DB::run($log_query)->fetchColumn();
        list($pager, $limit) = pagination(25, $count,url('adminlog?'));
        $log_query = "SELECT id, added, txt FROM log $where ORDER BY id DESC $limit";
        $logs = DB::run($log_query)->fetchAll();
        return array('logs' => $logs, 'pager' => $pager);
    }

    // Admin Delete Logs
    public static function delete()
    {
        $ids = multiIds('ids');
        $log_query = "DELETE FROM `log` WHERE `id` IN (?)";
        DB::run($log_query, [$ids]);
        message('adminlog', LANG["DEL_COMPLETE"]);
    }

}