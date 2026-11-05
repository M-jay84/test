<?php

namespace Bootstrap\Libs\Torrents;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Torrents\TorrentFile;

/*
* Parse Data From Torrent File
*/
class Parse
{

    // Get Data From Torrent File
    public static function torr($path = "")
    {
        // First Check File Is Torrent
        $torrent = explode(".", $path);
        $fileend = strtolower(end($torrent));
        if ($fileend != "torrent") {
            message(URLROOT, LANG['NOT_TORRENT']);
        }

        // Set Array
        $torrentInfo = array();

        // Load Torrent
        try {
            $torrent = TorrentFile::load($path);
        } catch (\Exception $e) {
            message(URLROOT, LANG["ERROR_TORRENT"]);
        }

        // Get Data
        $torrentInfo['announce'] = $torrent->getAnnounce();
        $torrentInfo["announce-list"] = $torrent->getAnnounceList();
        $torrentInfo["creation date"] = date('r', $torrent->getCreationDate());
        $torrentInfo['comment'] = $torrent->getComment();
        $torrentInfo['name'] = $torrent->getName();
        $torrentInfo['protocol'] = $torrent->getProtocol(); // V1,V2,HYBRID

        $hash = $torrent->getInfoHashs();
        if ($torrentInfo['protocol'] == 'v2') {
            $torrentInfo['hash'] = substr($hash['v2'], 0, 40);
            $torrentInfo['hash2'] = null;
        } elseif ($torrentInfo['protocol'] == 'v1') {
            $torrentInfo['hash'] = $hash['v1'];
            $torrentInfo['hash2'] = null;
        } elseif ($torrentInfo['protocol'] == 'hybrid') {
            $torrentInfo['hash'] = $hash['v1'];
            $torrentInfo['hash2'] = substr($hash['v2'], 0, 40);
        }

        // Get File Data
        $parse = $torrent->parse();
        $torrentInfo['files'] = serialize($parse['fileTree']);
        $torrentInfo['length'] = $parse['total_size'];
        $torrentInfo['ttfilecount'] = $parse['count'];

        return $torrentInfo;
    }

    // Scrape Torrent
    public static function ScrapeId($id, $annlist, $infohash)
    {
        if (!is_array($annlist)) {
            $tor = Parse::torr(TORRENT_PATH . "/$id.torrent");
            $infohash = $tor['hash'];
            $infohash2 = $tor['hash2'];
            $annlist = $tor['announce-list'];
            $var2 = serialize($annlist); 
        }
        
        $scraper = new Scraper();
        $scraped = $scraper->scrape($infohash, $annlist, 20, 4, true );

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
            $torrents_query = "UPDATE torrents SET leechers = ? AND seeders = ? AND times_completed = ? AND last_action = ? AND 
             visible = ? AND announcelist = ? WHERE id=?";
            DB::run($torrents_query, [$leechers, $seeders, $completed, CONFIG['TT_DATE'], 'yes', $var2, $id]
            );
        } else {
            // Its Dead :(
            $torrents_query = "UPDATE torrents SET torrent = ? WHERE id=?";
            DB::run($torrents_query, [CONFIG['TT_DATE'], $id]);
        }
    }

    // Scrape Torrent Batch/All todo
    public static function scrapeall()
    {
        // Set A Limit ? how fast is server / how many torrents to limit ?
        //set_time_limit(15);

        // Rescrape 10 Torrents Every X Days. (Default: 2 days)
        $torrents_query = "SELECT id,info_hash,last_action,announcelist FROM `torrents` 
                        WHERE external=? AND last_action <=? DATE_SUB(UTC_TIMESTAMP(), INTERVAL 2 DAY) LIMIT 10";
        $stmt = DB::run($torrents_query, ['external=', 'last_action <='],
                                ['yes', 86400]);

        foreach ($stmt as $tor) {
            self::ScrapeId($tor['id'], $tor['announcelist'], $tor['info_hash']);
        }
    }

}