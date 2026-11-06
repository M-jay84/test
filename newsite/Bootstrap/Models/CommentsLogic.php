<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;
use Bootstrap\Models\NewsLogic;
use Bootstrap\Models\TorrentsLogic;
use PDO;

/*
* Comments Table Data
*/
class CommentsLogic
{
public static function delete(int $id, int $userid, string $type = ''): never
{
    if ($type === 'torrent') {
        $row = DB::run('SELECT torrent FROM comments WHERE id = ?', [$id])->fetch(\PDO::FETCH_ASSOC);
        if ($row && (int) $row['torrent'] > 0) {
            TorrentsLogic::updateComments($id, 'sub');
        }
    }

    DB::run('DELETE FROM comments WHERE id = ?', [$id]);
    LogsLogic::write(coloredname($userid) . " has deleted comment: ID: $id");
    redirect('comment/admin', __('DEL_COMPLETE'), 'success');
}


    // Admin Get All
    public static function grabAll()
    {
        // Pagination
        $comments_query = "SELECT count(id) FROM `comments`";
        $count = DB::run($comments_query)->fetchColumn();
        list($pager, $limit) = pagination(10, $count, URLROOT."/comment/admin?");
        $comments_query = "SELECT c.id, c.text, c.user, c.torrent, c.news, t.name, n.title, u.username, c.added 
        FROM comments c 
        LEFT JOIN torrents t ON c.torrent = t.id 
        LEFT JOIN news n ON c.news = n.id 
        LEFT JOIN users u ON c.user = u.id 
        ORDER BY c.added DESC $limit";
        $stmt = DB::run($comments_query)->fetchAll();
        $array = [
            'pager' => $pager,
            'stmt' => $stmt,
            'count' => $count
        ];
        return $array;
    }

    // Get Comment
    public static function getInfo($id, $type)
    {
        if ($type == "news") {
            $news_query = "SELECT * FROM `news`  WHERE id=?";
            $row = DB::run($news_query, [$id])->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                message((URLROOT . "/comment/view/$id/news"), LANG["MISSING_ID"]);
            }
            $title = LANG["NEWS"];
        }

        if ($type == "torrent") {
            $torrents_query = "SELECT id,name FROM `torrents` WHERE id=?";
            $row = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                message(URLROOT, LANG["TORRENT_NOT_FOUND"]);
            }
            $title = LANG["COMMENT_FOR"] . "<a href='" . URLROOT . "/torrent/view/" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</a>";
        }

        if ($type == "id") {
            $title = LANG["COMMENT_FOR"] . " " . coloredname($id);
            $type = "comments.user";
        }

        // Get Comments Data
        $comments_query = "SELECT count(id) FROM `comments` WHERE type=?";
        $commcount = DB::run($comments_query, [$id])->fetchColumn();
        if ($commcount) {
            list($pager, $limit) = pagination(10, $commcount, "comments?id=$id&amp;type=$type");
            $comments_query = "SELECT comments.id, text, user, comments.added, avatar, signature, username, title, class, uploaded, downloaded, privacy, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE $type = $id ORDER BY comments.id $limit";
            $commres = DB::run($comments_query);
        } else {
            unset($commres);
        }
        $pager = [
            'res' => $commres,
            'pager' => $pager,
            'count' => $commcount,
        ];

        $data = [
            'title' => $title,
            'row' => $row,
            'res' => $pager['res'],
            'pager' => $pager['pager'],
            'count' => $pager['count'],
        ];
        return $data;

    }

   public static function action(int $targetid, int $userid, string $type = ''): never
{
    $comment_id = input_int('comment_id');
    $text       = input_string('text'); // or keep richer body if you store HTML intentionally

    if ($text === '') {
        redirect("comment/view/{$targetid}/{$type}", __('MISSING_FORM_DATA'), 'warning');
    }

    if ($type === 'torrent') {
        TorrentsLogic::updateComments($targetid, 'add');
    }

    if ($comment_id < 1) {
        DB::run(
            "INSERT INTO comments (user, {$type}, added, text, type) VALUES (?,?,?,?,?)",
            [$userid, $targetid, CONFIG['TT_DATE'], $text, $type]
        );
        redirect("comment/view/{$targetid}/{$type}", __('SUCCESS'), 'success');
    }

    DB::run('UPDATE comments SET text = ? WHERE id = ?', [$text, $comment_id]);
    LogsLogic::write(coloredname($userid) . " has edited comment: ID:$comment_id");
    redirect("comment/view/{$targetid}/{$type}", __('UPDATE_COMPLETE'), 'success');
}
}