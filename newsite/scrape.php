<?php

require 'Bootstrap/config/database.php';
require 'Bootstrap/Libs/DB.php';

use Bootstrap\Libs\DB;
/*
* Scrape
*/

if (stristr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") && extension_loaded('zlib') && ini_get("zlib.output_compression") == 0) {
    if (ini_get('output_handler') != 'ob_gzhandler') {
        ob_start("ob_gzhandler");
    } else {
        ob_start();
    }
} else {
    ob_start();
}
$infohash = array();
foreach (explode("&", $_SERVER["QUERY_STRING"]) as $item) {
    if (preg_match("#^info_hash=(.+)\$#", $item, $m)) {
        $hash = urldecode($m[1]);
        $info_hash = stripslashes($hash);
        if (strlen($info_hash) == 20) {
            $info_hash = bin2hex($info_hash);
        } elseif (strlen($info_hash) != 40) {
            continue;
        }
        $infohash[] = strtolower($info_hash);
    }
}

if (!count($infohash)) {
    die("Invalid infohash.");
}

$hashes = join(",", $infohash);
$torrents_query = "SELECT info_hash, seeders, leechers, times_completed, filename FROM `torrents` WHERE `info_hash` IN (?)";
$query = DB::run($torrents_query, [$hashes])->fetch(PDO::FETCH_ASSOC);

$result = "d5:filesd";
$result .= "20:" . hex2bin($$query['info_hash']) . "d";
$result .= "8:completei" . $query['seeders'] . "e";
$result .= "10:downloadedi" . $query['times_completed'] . "e";
$result .= "10:incompletei" . $query['leechers'] . "e";
$result .= "4:name" . strlen($query['filename']) . ":" . $query['filename'] . "e";
$result .= "e";
$result .= "ee";
echo $result;
ob_end_flush();