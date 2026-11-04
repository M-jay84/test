<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

/*
* Likes Table Data
*/
class LikesLogic
{
    // Add/Delete Like
    public static function switch($id, $type, $current_user, $post = 0)
    {
        switch ($type) {
            
            case 'liketorrent':
                $likes_query = "INSERT INTO `likes` (user, liked, added, type, reaction) VALUES (?,?,?,?,?)";
                DB::run($likes_query, [$current_user->id, $id, CONFIG['TT_DATE'], 'torrent', 'like']);
                message(("torrent/$id"), LANG["ITEM_LIKED"]);
                break;

            case 'unliketorrent':
                $likes_query = "DELETE FROM `likes` WHERE user = ? AND  liked = ? AND type=?";
                DB::run($likes_query, [$current_user->id, $id, 'torrent']);
                message(("torrent/$id"), LANG["ITEM_UNLIKED"]);
                break;

            case 'torrent':
                $likes_query = "INSERT INTO `likes` (user, liked, added, type) VALUES (?,?,?,?)";
                DB::run($likes_query, [$current_user->id, $id, CONFIG['TT_DATE'], 'forum']);
                message(("torrent/$id"), LANG["GIVE_APPRECIATE"]);
                break;
    
            case 'thanksforum':
                $likes_query = "INSERT INTO `likes` (user, liked, added, type) VALUES (?,?,?,?)";
                DB::run($likes_query, [$current_user->id, $id, CONFIG['TT_DATE'], 'torrent']);
                message(("post/$post"), LANG["GIVE_APPRECIATE"]);
                break;
    
            default:
                message(URLROOT, LANG["ITEM_LIKED"]);
                break;
        }
    }

    // Show Who liked
    public static function showLikes($data)
    {
        if (CONFIG['ALLOWLIKES']) {
            $likes_query = "SELECT user FROM `likes` WHERE liked =? AND type =? AND user =? AND reaction=?";
            $data1 = DB::run($likes_query, [$data['id'],'torrent',$data['current_user']->id,'like'])->fetch(PDO::FETCH_ASSOC);
            $likes = $data1;
            if ($likes) {
                $show['like'] = "&nbsp;<a href='". URLROOT ."/like/$data[id]/unliketorrent'><i class='fa fa-thumbs-down' title='UnLike'></i></a><br>";
            } else {
                $show['like'] = "&nbsp;<a href='".URLROOT."/like/$data[id]/liketorrent'><i class='fa fa-thumbs-up' title='Like'></i></a><br>";
            }
        }
        if (CONFIG['ALLOWLIKES']) {
            $likes_query = "SELECT user FROM `likes` WHERE liked=? AND type=?";
            $data3 = DB::run($likes_query, [$data['id'], 'torrent']);
            foreach ($data3 as $stmt) :
                $show['liked'] .= "<a href='" . URLROOT . "/profile/$stmt[user]'>" . coloredname($stmt['user']) . "</a>&nbsp;";
            endforeach;
        }
        return $show;
    }

    // Show Who Thanked
    public static function showThanks($target)
    {
        $users_query = "SELECT * FROM `users` AS u LEFT JOIN `likes` AS l ON(u.id = l.user) WHERE liked=? AND type=?";
        $data3 = DB::run($users_query, [$target, 'torrent']);
        $count = $data3->rowCount();
        if ($count > 0) {
            $thanks = 'Thanks by<br>';
            foreach ($data3 as $stmt) :
                $thanks .= "<a href='" . URLROOT . "/profile/$stmt[id]'>" . coloredname($stmt['id']) . "</a>&nbsp;";
            endforeach;
            $thanks .= "<br>";
        } else {
            $thanks = '';
        }
        return $thanks;
    }
}