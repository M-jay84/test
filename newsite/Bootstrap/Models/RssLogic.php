<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;

/*
* Rss Data
*/
class RssLogic
{

    // RSS Info
    public static function rsslink($current_user)
    {
            // Sort Data
            $params = array();

            if ($current_user->id) {
                $params[] = "passkey=".$current_user->passkey."";
            }
            if ($cats = $_POST["cats"]) {
                $catlist = array();
                foreach ($cats as $cat) {
                    if (is_numeric($cat)) {
                        $catlist[] = $cat;
                    }
                }
                if ($catlist) {
                    $params[] = "cat=" . implode(",", $catlist);
                }
            }
            if ($_POST["incldead"]) {
                $params[] = "incldead=1";
            }
            if (intval($_POST['user'])) {
                $params[] = "user=$_POST[user]";
            }
            if ($params) {
                $param = "?" . implode("&amp;", $params);
            } else {
                $param = "";
            }
            
            message(URLROOT, "Your RSS link is: <a href='".URLROOT."/rss$param'>".URLROOT."/rss$param</a>");

    }

    // RSS Info
    public static function rssInfo($current_user)
    {
        // Check User Input
        $passkey = $_GET["passkey"];
        $incldead = $_GET["incldead"];
        $cat = $_GET["cat"];
        $user = $_GET["user"];

        // Check User
        if ($passkey == ''){
            $passkey = $current_user->passkey;
        }

        $user_query = "SELECT count(id) FROM `users` WHERE passkey=?";
        $member = DB::run($user_query, [$passkey])->fetchColumn();
        if ($member == 1) {
            $link = "$passkey";
        } else {
            $link = "";
        }
        
        if ($link == "" && CONFIG['MEMBERSONLY']) {
            message(URLROOT, LANG["NO_PERMISSION"]);
        }

        // Sort Input
        $where = "";
        $wherea = array();
        if (!$incldead) {
            $wherea[] = "visible='yes'";
        }
        if ($cat) {
            $cats = implode(", ", array_unique(array_map("intval", explode(",", (string) $cat))));
            $wherea[] = "category in ($cats)";
        }
        if (intval($user)) {
            $wherea[] = "owner=$user";
        }
        if ($wherea) {
            $where = "WHERE " . implode(" AND ", $wherea);
        }
        $limit = "LIMIT 50";


        $torrents_query= "SELECT torrents.id,
                               torrents.name,
                               torrents.size,
                               torrents.category,
                               torrents.added, torrents.leechers,
                               torrents.seeders, categories.parent_cat as cat_parent,
                               categories.name AS cat_name 
                        FROM torrents 
                        LEFT JOIN categories ON category = categories.id 
                        $where 
                        ORDER BY added 
                        DESC $limit";
                        $cres = DB::run($torrents_query)->fetchAll();
                        $cres['res'] = $cres;
                        $cres['link'] = $link;
        return $cres;
    }
}