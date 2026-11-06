<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Auth;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\ForumCatsLogic;
use Bootstrap\Models\GroupsLogic;
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\ReadLogic;
use Bootstrap\Models\TopicsLogic;
use PDO;

/*
* Forums Table Data
*/
class ForumsLogic
{

public static function forum_guard(): object
{
    $user = \Bootstrap\Libs\Auth::user();

    if (empty(CONFIG['FORUMS'])) {
        redirect('home', __('FORUM_AVAILABLE'), 'warning');
    }
    if (empty(CONFIG['FORUMS_GUESTREAD']) && !\Bootstrap\Libs\Auth::check()) {
        redirect('authorise/login', __('NO_PERMISSION'), 'warning');
    }
    if (($user->forumbanned ?? '') === 'yes' || ($user->view_forum ?? '') === 'no') {
        // staff override optional:
        if (!\Bootstrap\Libs\Auth::isStaff()) {
            redirect('home', __('FORUM_BANNED'), 'warning');
        }
    }
    return $user;
}

    // Returns the minimum read/write class levels of a forum
    public static function getForumAccessLevels($forumid)
    {
        $forums_query = "SELECT minclassread, minclasswrite FROM `forum_forums` WHERE id=? ";
        $arr = DB::run($forums_query, [$forumid])->fetch(PDO::FETCH_ASSOC);
        if (!$arr) {
            return false;
        }
        return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"]);
    }

    // Get Cats
    public static function getCatForums()
    {
        $list = [];
        $forumcats_query = "SELECT * FROM `forumcats` ORDER BY sort";
        $forum_cats =DB::run($forumcats_query);
        foreach ($forum_cats as $cat) {

            $country_id = $cat['id'];

            $country_info = [
                'c_id' => $country_id,
                'c_name' => $cat['name'],
                'c_sort' => $cat['sort'],
                'c_forums' => []
            ];

            $forums_query = "SELECT * FROM `forum_forums` WHERE category=? ORDER BY sort,name";
            $cat_forums = DB::run($forums_query, [$country_id]);

            foreach ($cat_forums as $forum) {

                $f_id = $forum['id'];
                $topics_query = "SELECT count(id) FROM `forum_topics` WHERE forumid =?";
                $count_topic = DB::run($topics_query, [$f_id])->fetchColumn();
                $topics_query = "SELECT count(id) FROM `forum_topics` WHERE forumid =?";
                $topic_id = DB::run($topics_query, [$f_id])->fetchColumn();
                $posts_query = "SELECT count(id) FROM `forum_posts` WHERE topicid =?";
                $count_post = DB::run($posts_query, [$topic_id])->fetchColumn();
                $country_info['c_forums'][$f_id] = [
                    'f_id' => $f_id,
                    'f_name' => $forum['name'],
                    'f_description' => $forum['description'],
                    'f_minclassread' => $forum['minclassread'],
                    'f_minclasswrite' => $forum['minclasswrite'],
                    'f_guest_read' => $forum['guest_read'],
                    'f_category' => $forum['category'],
                    'topic_count' => $count_topic,
                    'post_count' => $count_post
                ];
            }

            $list[$country_id] = $country_info;
        }

        return $list;
    }

    // Search Forum
    public static function search()
    {
        // Check User Input
        $keywords = (isset($_GET['keywords'])) ? trim(filter_input(INPUT_GET, 'keywords')) : '';
        $type = (isset($_GET['type'])) ? trim(filter_input(INPUT_GET, 'type')) : '';

        $data = [
            'FORUMS' => array(),
            'KEYWORDS' => $keywords,
            'TITLE' => LANG["FORUM"],
            'PAGER' => ''
        ];

        // Search Match
        if (!$keywords == '') {

        $keys = explode(" ", $keywords);
        foreach ($keys as $k) {
            $searcha[] = " forum_topics.subject LIKE '%$k%' ";
            $searchb[] = " forum_posts.body LIKE '%$k%' ";
            $searchc[] = " forum_topics.subject LIKE '%$k%' ";
            $searchd[] = " forum_posts.body LIKE '%$k%' ";
        }
        $whereasearch = '(' . implode(' OR ', $searcha) . ')';
        $wherebsearch = '(' . implode(' OR ', $searchb) . ')';
        $wherecsearch = implode(' OR ', $searchc);
        $wheredsearch = implode(' OR ', $searchd);
        $wherea = $whereasearch;
        $whereb = $wherebsearch;
        $wherec = $wherecsearch;
        $whered = $wheredsearch;
        if ($type == 'deep') {
            $posts_query = "SELECT *, ( $wherea + $whereb ) as count_words
                             FROM forum_posts
                             JOIN forum_topics
                             ON forum_posts.topicid=forum_topics.id
                             JOIN forum_forums
                             ON forum_forums.id=forum_topics.forumid
                             WHERE ($whered)
                             ORDER BY count_words,added DESC";
            $res2 = DB::run($posts_query);
            $count = $res2->rowCount();
            list($pager, $limit) = pagination(30, $count, URLROOT . "/forum/search?keywords=$keywords&type=deep&");
            $posts_query = "SELECT *, ( $wherea + $whereb ) as count_words
                            FROM forum_posts
                            JOIN forum_topics
                            ON forum_posts.topicid=forum_topics.id
                            JOIN forum_forums
                            ON forum_forums.id=forum_topics.forumid
                            WHERE ($whered)
                            ORDER BY count_words,added DESC $limit";
            $res = DB::run($posts_query)->fetchAll();
        } else {
            $forums_query = "SELECT topicid, subject, forumid, forum_posts.added, forum_posts.userid, forum_forums.name, forum_forums.minclassread, forum_forums.guest_read, ( $wherea ) as count_words
                             FROM forum_topics
                             JOIN forum_posts
                             ON forum_posts.topicid=forum_topics.id
                             JOIN forum_forums
                             ON forum_forums.id=forum_topics.forumid
                             WHERE ($wherec)";
            $res2 = DB::run($forums_query);
            $count = $res2->rowCount();
            list($pager, $limit) = pagination(30, $count, URLROOT . "/forum/search?keywords=$keywords&");

            $forums_query = "SELECT distinct topicid, subject, forumid, forum_posts.added, forum_posts.userid, forum_forums.name, forum_forums.minclassread, forum_forums.guest_read, ( $wherea ) as count_words
                            FROM forum_topics
                            JOIN forum_posts
                            ON forum_posts.topicid=forum_topics.id
                            JOIN forum_forums
                            ON forum_forums.id=forum_topics.forumid
                            WHERE ($wherec)
                            ORDER BY count_words,added DESC $limit";
            $res = DB::run($forums_query)->fetchAll();
        }

        $forums = ['res' => $res, 'pager' => $pager, 'count' => $count];
        if ($forums['count'] > 0) {   
            // Init Data
            $data['FORUMS'] = $forums['res'];
            $data['PAGER'] = $forums['pager'];
        } else {
            message('forum/search', LANG["NOTHING_FOUND"]);
        }
    }

        return $data;
    }

    // Unread Topics
    public static function unread($data)
    {
        $readposts_query = "SELECT forum_readposts.lastpostread, forum_readposts.topicid,
                                forum_topics.id, forum_topics.forumid, forum_topics.subject, forum_topics.lastpost,
                                forum_forums.name, forum_forums.minclassread, forum_forums.guest_read
                        FROM forum_readposts 
                        JOIN forum_topics
                        ON forum_topics.id=forum_readposts.topicid
                        JOIN forum_forums
                        ON forum_forums.id=forum_topics.id
                        WHERE forum_readposts.userid=? 
                        ORDER BY forum_topics.lastpost DESC
                        LIMIT 5";
        $stmt = DB::run($readposts_query, [$data->id])->fetchAll();
        return $stmt;
    }

    // Admin Delete Cat
    public static function deleteCats($id, $action = 0)
    {
        if ($action == 0) {
            message('forumcat/admin', "Are You Sure <a href='".URLROOT."/forumcat/delete/$id/1'>Remove Cat $id</a>");
        }

        $forumcats_query = "SELECT * FROM `forumcats` WHERE id=?";
        $v = DB::run($forumcats_query, [$id])->fetch(PDO::FETCH_ASSOC);
        if (!$v) {
            message('forumcat/admin', LANG["FORUM_INVALID_CAT"]);
        }
        $forumcats_query = "DELETE FROM `forumcats` WHERE id' = ?";
        DB::run($forumcats_query, [$id]);
        $forums_query = "SELECT id FROM `forum_forums` WHERE category=?";
        $res = DB::run($forums_query, [$id]);
        foreach ($res as $row) {
            $topics_query = "SELECT * FROM `forum_topics` WHERE forumid=?";
            $res2 = DB::run($topics_query, [$row['id']]);
            foreach ($res2 as $arr) {
                $posts_query = "DELETE FROM `forum_posts` WHERE topicid' = ?";
                DB::run($posts_query, [$arr['id']]);
                $readposts_query = "DELETE FROM `forum_readposts` WHERE topicid' = ?";
                DB::run($readposts_query, [$arr['id']]);
            }
            $topics_query = "DELETE FROM `forum_topics` WHERE forumid' = ?";
            DB::run($topics_query, [$row['id']]);
            $forums_query = "DELETE FROM `forum_forums` WHERE id = ?";
            DB::run($forums_query, [$row['id']]);
        }
        
        message('forumcat/admin', LANG["DEL_COMPLETE"]);
    }

    // Admin Get Cats
    public static function getCats()
    {
        $forumcats_query = "SELECT id, name, sort FROM `forumcats` ORDER BY sort, name";
        $forumcat = DB::run($forumcats_query);
        return $forumcat;
    }

    // Admin Reorder Cats
    public static function reorder($id, $move)
    {
        if ($move == 'up') {
            $forumcats_query = "UPDATE forumcats SET sort =? WHERE sort=? AND category=? AND id!=?";
            DB::run($forumcats_query, [$_GET["sort"], $_GET["sort"] - 1, $id]);
            $forumcats_query = "UPDATE forumcats SET sort =? WHERE  id=?  ";
            DB::run($forumcats_query, [($_GET["sort"] - 1), $id]);
        }

        if ($move == 'down') {
            $forumcats_query = "UPDATE forumcats SET sort =? WHERE id=?  ";
            DB::run($forumcats_query, [($_GET["sort"] + 1), $id]);
            $forumcats_query = "UPDATE forumcats SET sort =? WHERE sort=? AND category=? AND id!=?";
            DB::run($forumcats_query, [$_GET["sort"], $_GET["sort"] + 1, $id]);
        }
        message('forumcat/admin', LANG["MOVED"]);
    }

    // Admin Reorder Forums
    public static function reorderForum($id, $forumid, $move)
    {
        if ($move == 'up') {
            $forums_query = "UPDATE forum_forums SET sort =? WHERE sort=? AND category=? AND id!=?";
            DB::run($forums_query, [$_GET["sort"], $_GET["sort"] - 1, $id, $forumid]);
            $forums_query = "UPDATE forum_forums SET sort =? WHERE id=?  ";
            DB::run($forums_query, [($_GET["sort"] - 1), $id]);
        }

        if ($move == 'down') {
            $forums_query = "UPDATE forum_forums SET sort =? WHERE id=?";
            DB::run($forums_query, [($_GET["sort"] + 1), $id]);
            $forums_query = "UPDATE forum_forums SET sort =? WHERE sort=? AND category=? AND id!=?  ";
            DB::run($forums_query, [$_GET["sort"], $_GET["sort"] + 1, $id, $forumid]);
        }
        message(("adminforum/$id"), LANG["MOVED"]);
    }

    // Add/Edit Cat
    public static function insertOrUpdate()
    {
        $name = Input1('name');
        $sort = Input1('sort');
        $id = Input1('id');
        
        if ($name == "") {
            message('forumcat/admin', LANG["MISSING_FORM_DATA"]);
        }
        
        // Admin Insert Forum Cat
        if (!$id) {
            $forumcats_query = "INSERT INTO `forumcats` (name,sort) VALUES (?,?)";
            $res =  DB::run($forumcats_query, [$name, intval($sort)]);
            if (!empty($res)) {
                message('forumcat/admin', LANG["SUCCESS"]);
            } else {
                message('forumcat/admin', LANG["ACTION_FAILED"]);
            }
        } else {
            $forumcats_query = "UPDATE forumcats SET sort = ? AND name = ? WHERE id=?";
            DB::run($forumcats_query, [$sort, $name, $id]);
            message('forumcat/admin', "<center><b>" . LANG["UPDATE_COMPLETE"] . "</b></center>");
        }
    }

    // Get User Forum Post By Id
    public static function getUsersPost($id, $current_user)
    {
        if ($current_user->view_users != "yes" && $current_user->id != $id) {
            message(URLROOT, LANG["NO_USER_VIEW"]);
        }

        // Pagination
        $posts_query = "SELECT count(id) as count FROM `forum_posts` WHERE userid=?";
        $count = DB::run($posts_query, [$id])->fetchColumn();
        list($pager, $limit) = pagination(20, $count,url("post/user/$id&"));
        $posts_query = "SELECT
                        forum_posts.id, 
                        forum_posts.topicid, 
                        forum_posts.userid, 
                        forum_posts.added, 
                        forum_posts.body,
                        
                        users.avatar, 
                        users.signature, 
                        users.username, 
                        users.title, 
                        users.class, 
                        users.uploaded, 
                        users.downloaded, 
                        users.privacy, 
                        users.donated,
                        
                        forum_topics.subject,
                        forum_topics.id as tid,
                        forum_topics.forumid,

                        forum_forums.name
                        
                        FROM forum_posts
                        
                        LEFT JOIN users
                        ON forum_posts.userid = users.id
                        
                        LEFT JOIN forum_topics
                        ON forum_posts.topicid = forum_topics.id
            
                        LEFT JOIN forum_forums
                        ON forum_topics.forumid = forum_forums.id
                        
                        WHERE forum_posts.userid = $id 
                        ORDER BY forum_posts.added 
                        DESC $limit";

        $row = DB::run($posts_query)->fetchAll();
        //echo '<pre>'.print_r($stmt, true).'</pre>';
        return array('row' => $row, 'pager' => $pager);
    }

    // Admin Get Forums
    public static function getForums($id)
    {
        $forumcats_query = "SELECT * FROM `forumcats` WHERE id=? ORDER BY sort,name";
        $name = DB::run($forumcats_query, [$id])->fetchColumn();
        $forumcats_query = "SELECT * FROM `forumcats` ORDER BY sort, name";
        $query = DB::run($forumcats_query);
        $forumcat = array();
        foreach ($query as $row) {
            $forumcat[] = $row;
        }

        $forums_query = "SELECT * FROM `forum_forums` WHERE category=? ORDER BY sort,name";
        $query1 = DB::run($forums_query, [$id]);
        $forumforum = array();
        foreach ($query1 as $row) {
            $forumforum[] = $row;
        }

        $forums_query = "SELECT * FROM `forum_forums` WHERE category=? ORDER BY sort,category";
        $query = DB::run($forums_query, [$id]);
        $groups_query = "SELECT * FROM `groups`";
        $groups = DB::run($groups_query);
        $array = [
            'forumcat' => $forumcat,
            'forumforum' => $forumforum,
            'query' => $query,
            'name' => $name,
            'groups' => $groups
        ];
        return $array;
    }

    // Admin Add/Edit Forum
    public static function insertOrUpdateForum()
    {
        $name = Input1('name');
        $description = Input1('description');
        $sort = Input1('sort');
        $new_forum_cat = Input1('new_forum_cat');
        $minclassread = Input1('minclassread');
        $minclasswrite = Input1('minclasswrite');
        $guest_read = Input1('guest_read');
        $sub = Input1('sub');
        $id = Input1('id');

        if (!$id) {
            $forums_query = "INSERT INTO `forum_forums` (name,description,sort,category,minclassread,minclasswrite,guest_read) VALUES (?,?,?,?,?,?,?)";
            $res = DB::run($forums_query,
            [$name, $description, $sort, $new_forum_cat, $minclassread, $minclasswrite, $guest_read]);
            $id = DB::lastInsertId();
            if ($res) {
                message(("adminforum/$new_forum_cat"), LANG["SUCCESS"]);
            } else {
                message(("adminforum/$new_forum_cat"), LANG["ACTION_FAILED"]);
            }
        } else {
            $forums_query = "UPDATE forum_forums SET sort=? AND name=? AND description=? AND category=? AND minclassread=? AND added=? AND minclasswrite=?
             AND guest_read=? WHERE id=?";
            DB::run($forums_query, [$sort, $name, $description, $new_forum_cat, $minclassread, $minclasswrite, $guest_read, $id]);
            message(("adminforum/$new_forum_cat"), "<center><b>" . LANG["UPDATE_COMPLETE"] . "</b></center>");
        }
    }

    // Admin Delete Forum
    public static function delete($id, $sure = 0)
    {
        if ($sure != 'sure') {
            message('forum/admin', "Are You Sure <a href='".URLROOT."/forum/delete/$id/sure'>Remove Forum $id</a>");
        }

        $forums_query = "SELECT * FROM `forum_forums` WHERE id=? ";
        $v =  DB::run($forums_query, [$id])->fetch(PDO::FETCH_ASSOC);
        if (!$v) {
            message('forum/admin', LANG["FORUM_INVALID"]);
        }
        $forums_query = "DELETE FROM `forum_forums` WHERE id' = ?";
        DB::run($forums_query, [$id]);
        $topics_query = "DELETE FROM `forum_topics` WHERE forumid' = ?";
        DB::run($topics_query, [$id]);
        $readposts_query = "DELETE FROM `forum_readposts` WHERE topicid' = ?";
        DB::run($readposts_query, [$id]);
        $posts_query = "DELETE FROM `forum_posts` WHERE topicid' = ?";
        DB::run($posts_query, [$id]);
        message('forumcat/admin', LANG["DEL_COMPLETE"]);
    }
}