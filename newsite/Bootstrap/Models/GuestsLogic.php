<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Ip;

/*
* Guest Table Data
*/
class GuestsLogic
{
    // Add Guest
    public static function add()
    {
        $time = CONFIG['TT_TIME'];
        $guests_query = 
            "INSERT INTO `guests` (`ip`, `time`) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE `time` = ?";
        DB::run($guests_query, [Ip::getIP(), $time, $time]);
    }

    // Delete Guest
    public static function clean()
    {
        $past = (CONFIG['TT_TIME'] - 2400);
        $guests_query = "DELETE FROM `guests` WHERE `time` < ?";
        DB::run($guests_query, [$past]);
        $guests_query = "SELECT count(ip) FROM `guests`";
        $stmt = DB::run($guests_query)->fetchColumn();
        return $stmt;
    }

}