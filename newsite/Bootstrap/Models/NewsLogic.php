<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\CommentsLogic;

/*
* News Table Data
*/
class NewsLogic
{
    // Admin Add/Edit News
    public static function insertOrUpdate()
    {
        // Check User Input
        $body = Input1('body');
        $title = Input1('title');
        $added = Input1('added');
        $id = Input1('id');
       
        // Check Correct Input
        if (!$body || !$title) {
            message('adminnews', LANG["MISSING_FORM_DATA"]);
        }
        
        if (!$added) {
            $added = CONFIG['TT_DATE'];
        }

        if (!$id) {
            $news_query = "INSERT INTO news (userid,added,body,title) VALUES (?,?,?,?)";
            DB::run($news_query, [$id, CONFIG['TT_DATE'], $body, $title]);
        } else {
            $news_query = "UPDATE news SET body = ? AND title = ? WHERE id=?";
            DB::run($news_query, [$body, $title, $id]);
        }
        message('adminnews', LANG["ACTION_COMPLETE"]);
    }

    // Admin Delete Catagorie
    public static function getAll()
    {
        $news_query = "SELECT * FROM `news` ORDER BY added DESC";
        $news = DB::run($news_query);
        return $news;
    }

    // Admin Delete News
    public static function delete($id, $userid)
    {
        $news_query = "DELETE FROM `news` WHERE id' = ?";
        DB::run($news_query, [$id]);
        CommentsLogic::delete($id, $userid, 'news');
        message('adminnews', LANG["DEL_COMPLETE"]);
    }

}