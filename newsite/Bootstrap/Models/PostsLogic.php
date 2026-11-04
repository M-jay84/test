<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\AttachmentsLogic;
use Bootstrap\Models\ForumCatsLogic;
use Bootstrap\Models\ForumsLogic;
use Bootstrap\Models\ReadLogic;
use Bootstrap\Models\TopicsLogic;
use PDO;

/*
* Post Table Data
*/
class PostsLogic
{

    // Returns the ID of the last post of a forum
    public static function updateTopicLastPost($topicid)
    {
        $posts_query = "SELECT * FROM forum_posts WHERE topicid = ? ORDER BY id DESC LIMIT 1";
        $res = DB::run($posts_query, [$topicid])->fetch(PDO::FETCH_ASSOC);
        $arr = $res or message('forum', 'No post found');
        $postid = $arr[0];
        $topics_query = "UPDATE forum_topics SET lastpost = ? WHERE id=?";
        DB::run($topics_query, [$topicid, $postid]);
    }

    // Returns the forum ID of a topic, or false on error
    public static function getTopicForum($topicid)
    {
        $topics_query = "SELECT forumid FROM `forum_topics` WHERE id = ?";
        $res = DB::run($topics_query, [$topicid])->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            return false;
        }
        return $res['forumid'];
    }

    // Get Post
    public static function getTopicPages($topicid, $current_user)
    {
        // Get Post Data
        $posts_query = "SELECT count(id) FROM `forum_posts` WHERE topicid = ?";
        $count =  DB::run($posts_query, [$topicid])->fetchColumn();
        list($pager, $limit) = pagination(15, $count, URLROOT . "/post/$topicid?");
        $posts_query =  "SELECT forum_posts.id,
                        forum_posts.topicid,
                        forum_posts.userid,
                        forum_posts.added,
                        forum_posts.body,
                        forum_posts.editedby,
                        forum_posts.editedat,
                        forum_posts.attachments
                    FROM forum_posts
                    LEFT JOIN forum_topics 
                    ON forum_topics.id = forum_posts.topicid
                    LEFT JOIN likes 
                    ON forum_posts.id = likes.liked
                    WHERE forum_posts.topicid = $topicid
                    GROUP BY forum_posts.id 
                    ORDER BY forum_posts.id $limit";
					/*
                        likes.user,
                        likes.liked,
                        likes.type
					*/
        $stmt = DB::run($posts_query)->fetchAll();

        // Get Topic Data
        $topics_query = "SELECT * FROM `forum_topics` WHERE id = ?";
        $arr = DB::run($topics_query, [$topicid])->fetch(PDO::FETCH_ASSOC);
        $levels = ForumsLogic::getForumAccessLevels($arr['forumid']) or message('forum', 'issue with access');
        $maypost = $current_user->class >= $levels["write"] ? true : false;

        // Update Topic Views
        $pvkey_var = "f_".$topicid;
        $views = $_SESSION[$pvkey_var] == 1 ? $arr["views"] + 0 : $arr["views"] + 1;
        $_SESSION[$pvkey_var] = 1;
        $topics_query = "UPDATE forum_topics SET views = ? WHERE id=?";
        DB::run($topics_query, [$views,$topicid]);

        // Check if user has access to this forum
        $forums_query = "SELECT * FROM `forum_forums` WHERE id = ?";
        $arr2 = DB::run($forums_query, [$arr['forumid']])->fetch(PDO::FETCH_ASSOC);
        if (!$arr2 || $current_user->class < $arr2["minclassread"] && $arr2["guest_read"] == "no") {
            message('forum', LANG["NO_PERMISSION"]);
        }

        // Update Last Read
        if ($current_user->id) {
            $readposts_query = "SELECT lastpostread FROM `forum_readposts` WHERE userid = ? AND topicid=?";
            $lpr = DB::run($readposts_query, [$current_user->id,$topicid])->fetchColumn();
            if (!$lpr) {
                $readposts_query = "INSERT INTO `forum_readposts` (userid, topicid) VALUES (?,?)";
                DB::run($readposts_query, [$current_user->id, $topicid]);
            }
        }
        $forumcats_query = "SELECT name FROM `forumcats` WHERE id = ?";
        $fname = DB::run($forumcats_query, [$arr['forumid']])->fetch(PDO::FETCH_ASSOC);

        $array = [
            'locked' => $arr["locked"] == 'yes',
            'subject' => stripslashes($arr["subject"]),
            'sticky' => $arr["sticky"] == "yes",
            'forumid' => $arr['forumid'],
            'minclasswrite' => $levels['write'],
            'guest_read' => $levels['read'],
            'maypost' => $maypost,
            'name' => stripslashes($arr2["name"]),
            'arr2' => $arr2,
            'stmt' => $stmt,
            'pager' => $pager,
            'lpr' => $lpr,
            'forum_name' => $fname['name']
        ];

        return $array;
    }

    // Delete Post
    public static function deletePost($postid, $data, $sure = 0)
    {        // Check User
        if (!$data['current_user']->delete_news == 'yes' || !intval($postid)) {
            message('forum', LANG["NO_PERMISSION"]);
        }
        
	    // Double Check
        if ($sure) {
		    message('forum', "Sanity check: You are about to delete a post. Click <a href='" . URLROOT . "/post/delete/$postid/1'>here</a> if you are sure.");
        }

        $posts_query = "SELECT 'topicid' FROM `forum_posts` WHERE id = ?";
        $topicid = DB::run($posts_query, [$postid])->fetchColumn();

        // We can not delete the post if it is the only one of the topic
        $posts_query = "SELECT count(id) FROM `forum_posts` WHERE topicid = ?";
        $arr = DB::run($posts_query, [$topicid])->fetchColumn();
        if ($arr == 1) {
            $msg = sprintf(LANG["FORUMS_DEL_POST_ONLY_POST"], $topicid);
            message('forum', $msg);
        }

        // Delete post
        $posts_query = "DELETE FROM forum_posts WHERE id' = ?";
        DB::run($posts_query, [$postid]);
        AttachmentsLogic::deleteAll($topicid, true);
        PostsLogic::updateTopicLastPost($topicid);
        message(("post/$topicid"), LANG["DEL_COMPLETE"]);
    }

    public static function addPost($topicid, $current_user) // todo
    {
        $body = htmlspecialchars_decode($_POST["body"]);

        if (!$body) {
            message('forum', LANG["MISSING_FORM_DATA"]);
        }

        $forumid = PostsLogic::getTopicForum($topicid) or message('forum', "Bad topic ID");

        // Make sure sure user has write access in forum
        $arr = ForumsLogic::getForumAccessLevels($forumid) or message('forum', "Bad forum ID");
        if ($current_user->class < $arr["write"]) {
            message('forum', LANG["FORUMS_NOT_PERMIT"]);
        }

        //Make sure topic exists and is unlocked
        $topics_query = "SELECT * FROM `forum_topics` WHERE id = ?";
        $arr = DB::run($topics_query, [$topicid])->fetch(PDO::FETCH_ASSOC);
        if ($arr["locked"] == 'yes') {
            message('forum', LANG["LOCKED"]);
        }
        //Get forum ID
        $forumid = $arr["forumid"];

        // Insert the new post
        $posts_query = "INSERT INTO forum_posts (topicid,userid,added,body) VALUES (?, ?, ?, ?)";
        DB::run($posts_query, [$topicid, $current_user->id, CONFIG['TT_DATE'], $body]);
        $postid = DB::lastInsertId();

        // attachments
        $data['topicid'] = $topicid;
        $data['postid'] = $postid;

        AttachmentsLogic::set_attachment($data);

        // Update topic last post
        PostsLogic::updateTopicLastPost($topicid);

        message("post/$topicid/last/#post$postid", LANG["SUCCESS"]);
    }

    // Post Edit Form Submit
    public static function edit($current_user)
    {
        // Check User Input
        $postid = Input1('postid', 'int');

        // Check User Input
        $body = htmlspecialchars_decode($_POST['body']);

        // Check Correct Input
        if ($body == "") {
            message('forum', LANG["MISSING_FORM_DATA"]);
        }

        // Get Topic Data
        $posts_query = "SELECT * FROM `forum_posts` WHERE id = ?";
        $arr = DB::run($posts_query, [$postid])->fetch(PDO::FETCH_ASSOC);
        if (!$arr) {
            message('forum', LANG["MISSING_ID"]. $postid);
        }
            
        // Check User
        if ($current_user->id != $arr["userid"] && !$current_user->delete_news == 'yes' && !$current_user->edit_forum == 'yes') {
            message('forum', LANG["NO_PERMISSION"]);
        }

        // Edit Post
        $posts_query = "UPDATE forum_posts SET body = ? AND editedat = ? AND editedby = ? WHERE id=?";
        DB::run($posts_query, [$body,CONFIG['TT_DATE'],$current_user->id,$postid]);
        $data['current_user'] = $current_user;
        $data['topicid'] = $arr['topicid'];
        $data['postid'] = $postid;
        AttachmentsLogic::set_attachment($data);
        message(("post/$arr[topicid]/$_POST[page]/#post$postid"), "Post was edited successfully.");
    }
    
}