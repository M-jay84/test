<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Models\TorrentsLogic;
use PDO;

/*
* Peers Table Data
*/
class PeersLogic
{
    // Get Peers
    public static function getPeers($type, $id, $current_user, $pagination = false)
    {

        $user_query = "SELECT id,username,privacy FROM `users` WHERE id = ?";
        $user =  DB::run($user_query, [$id])->fetch(PDO::FETCH_ASSOC);

        if ($current_user->view_users != "yes" && $current_user->id != $id && $current_user->class < CONFIG['_UPLOADER'] && $type == ('seeding' || 'leeching')) {
            message(URLROOT, LANG["NO_USER_VIEW"]);
        }

        $title = LANG["USER_DETAILS_FOR"] . coloredname($id);

        if ($type == 'torrent') {
            if ($current_user->torrents == "no" && $current_user->id != $id && $current_user->class < CONFIG['_UPLOADER']) {
                message(URLROOT, LANG["NO_TORRENT_VIEW"]);
            }
            $where = 'peers.torrent = ?';
            $values = [$id];
            $torrents_query = "SELECT name FROM `torrents` WHERE id =?";
            $name = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);
            $title = LANG["TORRENT_DETAILS_FOR"] . $name;
        } elseif ($type == 'admin') {
            $where = 'torrents.id > ?';
            $values = [0];
        }  elseif ($type == 'seeding') {
            $where = 'peers.userid = ? AND peers.seeder =  ?';
            $values = [$id, 'yes'];
        } elseif ($type == 'leeching') {
            $where = 'peers.userid = ? AND peers.seeder =  ?';
            $values = [$id, 'no'];
        }

        $peers_query =
            "SELECT COUNT(*) FROM peers 
             LEFT JOIN torrents ON peers.torrent = torrents.id 
             LEFT JOIN categories ON torrents.category = categories.id 
             WHERE $where";
             $count = DB::run($peers_query, $values
        )->fetchColumn();

        list($pager, $limit) = pagination(25, $count, URLROOT . "/$pagination&");

        $peers_query = "SELECT peers.id as pid, peers.torrent, peers.peer_id, peers.ip, peers.port, peers.uploaded, peers.downloaded,
                   peers.to_go, peers.seeder, peers.started, peers.last_action, peers.connectable, peers.client, peers.userid, peers.passkey,
                   torrents.info_hash, torrents.name, torrents.size, torrents.visible,  torrents.owner, torrents.freeleech, torrents.vip, 
                   torrents.seeders, torrents.leechers, torrents.category,
                   categories.name AS cat_name,  categories.parent_cat as cat_parent
            FROM peers 
            LEFT JOIN torrents ON peers.torrent = torrents.id 
            LEFT JOIN categories ON torrents.category = categories.id 
            WHERE $where $limit";
        $stmt = DB::run($peers_query
            , $values
        )->fetchAll();

        $array = [
            'stmt' => $stmt,
            'pager' => $pager,
            'count' => $count,
            'title' => $title,
            'user' => $user
        ];
    
        return $array;
    }
}