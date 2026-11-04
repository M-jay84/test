<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

/*
* Download Logic
*/
class DownloadLogic
{
    // Download Torrent
    public static function torrent($id, $passkey)
    {
        // Get User
        $users_query = "SELECT count(id) FROM `users` WHERE passkey=?";
        $member = DB::run($users_query, [$passkey])->fetchColumn();
        
        if ($member == 1) {
            $user = array();
            $users_query = "SELECT users.id, users.class, users.downloadbanned, users.role_id FROM `users` 
            LEFT OUTER JOIN `groups` 
            ON users.class=groups.group_id 
            WHERE passkey = ?";
            $visitor = DB::run($users_query,
            [$passkey]
            )->fetch(\PDO::FETCH_ASSOC);;
            foreach ($visitor as $key => $value) {
                $user[$key] = $value;
            }
            $role = $user['role_id'] == 1 ? $user['group_role_id'] : $user['role_id'];
            $permissions_query = "SELECT can_download, view_torrents FROM `permissions` WHERE permission_id=?";
            $permission = DB::run($permissions_query, [$role])->fetch(PDO::FETCH_ASSOC);
            foreach ($permission as $key => $value) {
                $user[$key] = $value;
            }
        }
            
        if (CONFIG['MEMBERSONLY']) {
            if (!$user) {
                die(LANG["MEMBERS_ONLY"]);
            } elseif ($user["can_download"] == "no") {
                message(URLROOT, LANG["NO_PERMISSION_TO_DOWNLOAD"]);
            } elseif ($user["downloadbanned"] == "yes") {
                message(URLROOT, LANG["DOWNLOADBAN"]);
            }
        } elseif ($user['passkey'] == '') {
            $rand = array_sum(explode(" ", microtime()));
            // todo passkey
            $guestpasskey = md5('username' . $rand . uniqid(20) . ($rand * mt_rand()));
            $passkey = $guestpasskey;
        }
            
        // Get Torrent
        $torrents_query = "SELECT filename, banned, external, announce, owner, vip FROM `torrents` WHERE id=?";
        $torrent = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);

        if (!$torrent) {
            message(URLROOT, LANG["TORRENT_NOT_FOUND"]);
        } elseif ($torrent["banned"] == "yes") {
            message(URLROOT, LANG["BANNED_TORRENT"]);
        } elseif ($user['class'] < CONFIG['_VIP'] && $torrent['vip'] == "yes") {
            message(URLROOT, LANG["VIPTODOWNLOAD"]);
        } elseif ($user['view_torrents'] != "yes" && CONFIG['MEMBERSONLY']) {
            message(URLROOT, LANG["NO_TORRENT_VIEW"]);
        }
            
        // Thanks
        if (CONFIG['FORCETHANKS'] && CONFIG['MEMBERSONLY']) {
            if ($user["id"] != $torrent["owner"]) {
                $likes_query = "SELECT user FROM `likes` WHERE liked =? AND type =? AND user =?";
                $like = DB::run($likes_query, [$id, 'torrent', $user['id']])->fetch(PDO::FETCH_ASSOC);
                if (!$like) {
                    message(URLROOT, LANG["PLEASE_THANK"]);
                }
            }
        }

        // Check The File
        $fn = TORRENT_PATH . "/$id.torrent";
        if (!is_file($fn)) {
            message(TORRENT_PATH . "/$id.torrent", LANG["FILE_NOT_FILE"]);
        }
        if (!is_readable($fn)) {
            message(TORRENT_PATH . "/$id.torrent", LANG["FILE_UNREADABLE"]);
        }
            
        // Name Download File
        $name = $torrent['filename'];
        $friendlyurl = str_replace("http://", "", URLROOT);
        $friendlyname = str_replace(".torrent", "", $name);
        $friendlyext = ".torrent";
        $name = $friendlyname . "[" . $friendlyurl . "]" . $friendlyext;

        // Update Hit When Downloaded
        $pvkey_var = "d_" . $id;
        $views = $_SESSION[$pvkey_var] == 1 ? $torrent["hits"] + 0 : $torrent["hits"] + 1;
        $_SESSION[$pvkey_var] = 1;
        $torrents_query = "UPDATE torrents SET hits =? WHERE id=?";
        DB::run($torrents_query, [$views,$id]);

        $data = [
            'file' => $fn,
            'external' => $torrent["external"],
            'name' => $name
        ];

        return $data;
    }

}