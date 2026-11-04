<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Torrents\Parse;
use Bootstrap\Libs\Torrents\Scraper;
use PDO;

/*
* Scrape Logic
*/
class ScrapeLogic
{
    // Scrape External
    public static function internal()
    {
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
        $cres = DB::run($torrents_query, [$hashes])->fetch(PDO::FETCH_ASSOC);
        return $cres;
    }

    // Scrape External
    public static function external($current_user)
    {
        $id = (isset($_GET['id'])) ? (int) $_GET['id'] : 0;
        $torrent_query = "SELECT id, info_hash, announce, announcelist FROM `torrents` WHERE external =? AND id =?";
        $resu = DB::run($torrent_query, ['yes', $id])->fetch(PDO::FETCH_ASSOC);

        while ($rowu = $resu) {
            // No need parsing but lets update any old torrents first
            if ($rowu['announcelist'] == '') {
                $tor = Parse::torr(TORRENT_PATH . "/$rowu[id].torrent");
                $infohash = $tor['hash'];
                $infohash2 = $tor['hash2'];
                $annlist = $tor['announce-list'];
                $newannouncelist = serialize($annlist);
            } else {
                $infohash = $rowu['info_hash'];
                $annlist = unserialize($rowu['announcelist']);
                $newannouncelist = $rowu['announcelist'];
            }

            $scraper = new Scraper();
            $scraped = $scraper->scrape($infohash, $annlist, 20, 4, true);

            if ($scraper->has_errors()) {
                $error = implode(",", $scraper->get_errors());
                message("torrent/$id", LANG[$error]);
            }

            $myarray = array_shift($scraped);

            $seeders = $leechers = $completed = 0;
            if ($myarray['seeders'] > 0) {
                $seeders = $myarray['seeders'];
            }
            if ($myarray['leechers'] > 0) {
                $leechers = $myarray['leechers'];
            }
            if ($myarray['completed'] > 0) {
                $completed = $myarray['completed'];
            }

            if ($seeders !== 0) {
                // Update the Torrent
                $torrent_query = "UPDATE `torrents` SET leechers=? AND seeders=? AND times_completed=? AND last_action=? AND visible=?
                                 AND announcelist =? WHERE id=?";
                DB::run($torrent_query, [$leechers, $seeders, $completed, CONFIG['TT_DATE'], 'yes', $newannouncelist, $rowu['id']]);
            } else {
                // Its Dead :(
                $torrent_query = "UPDATE `torrents` SET last_action = ? WHERE id=?";
                DB::run($torrent_query, [CONFIG['TT_DATE'], $rowu['id']]);
            }

            // Redirect with message
            if ($seeders !== 0) {
                message("torrent/$id", LANG["UPDATE_COMPLETE"]);
            } else {
                message("torrent/$id", LANG["SCRAPE_DEAD"]);
            }
        }
    }
}