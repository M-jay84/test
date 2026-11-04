<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\AttachmentsLogic;
use Bootstrap\Models\ForumCatsLogic;
use Bootstrap\Models\ForumsLogic;
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\ReadLogic;
use Bootstrap\Models\ShoutboxLogic;
use PDO;

/*
*  Topics Table Data
*/
class TopicsLogic
{	

    // Get Topics
    public static function getForumTopics($forumid, $current_user)
    {
        // Pagination
        $topics_query = "SELECT count(id) FROM `forum_topics` WHERE forumid = ?";
        $count = DB::run($topics_query, [$forumid])->fetchColumn();
        list($pager, $limit) = pagination(25, $count, URLROOT . "/topic/$forumid&");
        $topics_query = "SELECT * FROM `forum_topics` WHERE forumid=? ORDER BY lastpost DESC $limit";
        $topics = DB::run($topics_query, [$forumid]);
        foreach ($topics as $arr) {
            $forums_query = "SELECT * FROM `forum_forums` WHERE id=?";
            $arr1 = DB::run($forums_query, [$forumid])->fetch(PDO::FETCH_ASSOC);

            if (!$arr1['name'] || $current_user->class < $arr1["minclassread"] && $arr1["guest_read"] == "no") {
                message('forum', LANG["FORUMS_NOT_PERMIT"]);
            }

            $post_query = "SELECT count(id) FROM `forum_posts` WHERE topicid =?";
            $arr2 = DB::run($post_query, [$arr['id']])->fetchColumn();
            $object[$arr['id']]['id'] = $arr['id'];
            $object[$arr['id']]['userid'] = $arr['userid'];
            $object[$arr['id']]['subject'] = $arr['subject'];
            $object[$arr['id']]['locked'] = $arr['locked'] == "yes";
            $object[$arr['id']]['forumid'] = $arr['forumid'];
            $object[$arr['id']]['lastpost'] = $arr['lastpost']; 
            $object[$arr['id']]['moved'] = $arr['moved'] == "yes";
            $object[$arr['id']]['sticky'] = $arr['sticky'] == "yes";
            $object[$arr['id']]['views'] = $arr['views'];

            $object[$arr['id']]['replys'] = $arr2;

            if ($_SESSION) {
                $readposts_query = "SELECT lastpostread FROM `forum_readposts` WHERE userid=? AND topicid=?";
                $r = DB::run($readposts_query, [$current_user->id, $arr['id']])->fetch(PDO::FETCH_ASSOC);
                $a = $r;
            }
            if ($new = !$a || $arr['lastpost'] > $a[0]) {
                $img = "<i class='fa fa-file-text tticon-red' title='Read'></i>";
            }
            $topicpic = $new ? "<i class='fa fa-file-text tticon-red' title='Read'></i>" : "<i class='fa fa-file-text' title='Read'></i>";
    
            $object[$arr['id']]['read'] = $topicpic;
            
            $posts_query = "SELECT * FROM `forum_posts` WHERE topicid=? ORDER BY id DESC LIMIT 1";
            $arr3 = DB::run($posts_query, [$arr['id']])->fetch(PDO::FETCH_ASSOC);
            $object[$arr['id']]['lastuserpost'] = "by&nbsp;".coloredname($arr3['userid'])."<br />".user_timezone_date($arr3['added'])."</span>";
            
            $object[$arr['id']]['added'] = $arr1['added'];
            $object[$arr['id']]['name'] = $arr1['name'];
            $object[$arr['id']]['minclassread'] = $arr1['minclassread'];
            $object[$arr['id']]['minclasswrite'] = $arr1['minclasswrite'];
            $object[$arr['id']]['guest_read'] = $arr1['guest_read'];
        }
        /*
        if (count($object) == 0) {
            message(URLROOT, LANG["FORUM_AVAILABLE"));
        }
        */
        $forumcats_query = "SELECT name FROM `forumcats` WHERE Id=?";
        $fname = DB::run($forumcats_query, [$forumid])->fetch(PDO::FETCH_ASSOC);
        $forums_query = "SELECT name FROM `forum_forums` WHERE Id=?";
        $tname = DB::run($forums_query, [$forumid])->fetch(PDO::FETCH_ASSOC);

        $array = [
            'topics' => $object,
            'forum_name' => $fname['name'],
            'topic_name' => $tname['name'],
            'pager' => $pager
        ];

        // Init Data
        return $array;
    }

    // Add New Topic
    public static function insertTopic($forumid, $current_user)
    {

        $subject = Input1('subject');
        $body = htmlspecialchars_decode($_POST["body"]);

        if (!$body || !$subject) {
            message('forum', LANG["MISSING_FORM_DATA"]);
        }

        // Make sure sure user has write access in forum
        $arr = ForumsLogic::getForumAccessLevels($forumid) or message('forum', "Bad forum ID");
        if ($current_user->class < $arr["write"]) {
            message('forum', LANG["FORUMS_NOT_PERMIT"]);
        }

        // Create Topic / Reply
        $topics_query = "INSERT INTO `forum_topics` (userid, forumid, subject) VALUES (?,?,?)";
        DB::run($topics_query, [$current_user->id, $forumid, $subject]);
        $topicid = DB::lastInsertId() or message('forum', "Topics id n/a");

        // Insert the new post
        $posts_query = "INSERT INTO forum_posts (topicid,userid,added,body) VALUES (?,?,?,?)";
        DB::run($posts_query, [$topicid, $current_user->id, CONFIG['TT_DATE'], $body]);
        $postid = DB::lastInsertId();

        // Update topic last post
        $topics_query = "UPDATE forum_topics SET lastpost = ? WHERE id=?";
        DB::run($topics_query, [$postid, $topicid]);

        $data['topicid'] = $topicid;
        $data['postid'] = $postid;

        // attachments
        AttachmentsLogic::set_attachment($data);
        // Insert Shout
        $msg_shout = "New Forum Topic: [url=" . URLROOT . "/post/" . $topicid . "]" . $subject . "[/url] posted by [color=".$current_user->Color."]".$current_user->username."[/color].";
        $shoutbox_query = "INSERT INTO shoutbox (userid,date,user,message) VALUES (?,?,?,?)";
        DB::run($shoutbox_query, [0, CONFIG['TT_DATE'], 'System', $msg_shout]);
        redirect("post/$topicid"); // die; todo
    }

    // Delete Topic
    public static function delTopic($topicid, $sure = 0)
    {
       /*- 
        if (!intval(intval$topicid) || !$data['current_user']->delete_news'] == 'yes') {
            message('forum', LANG["NO_PERMISSION"));
        }
       */
        if ($sure == "0") {
            message('forum', "Sanity check: You are about to delete a topic. Click <a href='" . URLROOT . "/topic/delete/$topicid/1'>here</a> if you are sure.");
        }

        $topics_query= "DELETE FROM forum_topics WHERE id' = ?";
        DB::run($topics_query, [$topicid]);
        $posts_query = "DELETE FROM `forum_posts` WHERE topicid=?";
        DB::run($posts_query, [$topicid]);
        $readposts_query = "DELETE FROM `forum_readposts` WHERE topicid = ?";
        DB::run($readposts_query, [$topicid]);

        AttachmentsLogic::deleteAll($topicid);
        message('forum', LANG["DEL_COMPLETE"]);
    }

    // Rename a Topic Submit
    public static function rename($topicid, $current_user)
    {
        if (!$current_user->delete_news == 'yes' || !$current_user->edit_forum == 'yes') {
            message('forum', LANG["NO_PERMISSION"]);
        }

        // Check User Input
        $subject = Input1('subject');

        if ($subject == '') {
            message('forum', LANG["MISSING_FORM_DATA"]);
        }

        // Update
        $topics_query = "UPDATE forum_topics SET subject = ? WHERE id=?";
        DB::run($topics_query, [$subject,$topicid]);
        message(("post/$topicid"), LANG["NAME_UPDATED"]);
    }

    // Move a Topic Submit
    public static function move($topicid, $current_user)
    {
        // Check User Input
        $forumid = Input1('forumid', 'id');

        // Check User
        if (!intval($forumid) || !$current_user->delete_news == 'yes' || !$current_user->edit_forum == 'yes') {
            message('forum', LANG["NO_PERMISSION"]);
        }

        // Check Permisson To View
        $forums_query = "SELECT minclasswrite FROM `forum_forums` WHERE id=?";
        $arr = DB::run($forums_query, [$forumid])->fetch(PDO::FETCH_ASSOC);

        if ($arr->rowCount() != 1) {
            message('forum', LANG["NOTHING_FOUND"]);
        }
        if ($current_user->class < $arr['minclasswrite']) {
            message('forum', LANG["NO_PERMISSION"]);
        }

        // Move Topic
        $forumcats_query = "SELECT subject,forumid FROM `forumcats` WHERE id=?";
        $arr = DB::run($forumcats_query, [$topicid])->fetch(PDO::FETCH_ASSOC);
        if ($arr->rowCount() != 1) {
            message('forum', LANG["NOTHING_FOUND"]);
        }
        if ($arr["forumid"] != $forumid) {
            $topics_query = "UPDATE forum_topics SET forumid = ? AND moved =? WHERE id=?";
            DB::run($topics_query, [$forumid,'yes',$topicid]);
        }

       redirect("topic/$forumid");
    }

    // Lock/Sticky Topic Submit
    public static function action($forumid, $topicid, $type, $current_user)
    {
        if ($type == 'sticky') {
            $action = ['sticky'];
            $values = ['yes',$topicid];
        } elseif ($type == 'unsticky') {
            $action = ['sticky'];
            $values = ['no',$topicid];
        } elseif ($type == 'lock') {
            $action = ['locked'];
            $values = ['yes',$topicid];
        } elseif ($type == 'unlock') {
            $action = ['locked'];
            $values = ['no',$topicid];
        }

        // Check User
        if (!intval($topicid) || !$current_user->delete_news == 'yes' || !$current_user->edit_forum == 'yes') {
            message('forum', LANG["NO_PERMISSION"]);
        }

        // Update
        $topics_query = "UPDATE forum_topics SET $action =? WHERE id=?";
        DB::run($topics_query, $values);
       redirect("topic/$forumid");
    }

}