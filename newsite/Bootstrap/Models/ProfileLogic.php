<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\CommentsLogic;
use Bootstrap\Models\CountriesLogic;
use Bootstrap\Models\GroupsLogic;
use Bootstrap\Models\LogsLogic;
use Bootstrap\Models\MessagesLogic;
use Bootstrap\Models\PostsLogic;
use Bootstrap\Models\SnatchedLogic;
use Bootstrap\Models\TorrentsLogic;
use PDO;

/*
* Users Table Data
*/
class ProfileLogic
{
    // Get User Profile
    public static function getProfile($id, $current_user)
    {
        $users_query = "SELECT * FROM `users` WHERE id = ?";
        $user = DB::run($users_query, [$id])->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            message(URLROOT, LANG["NO_USER"] . " $id.");
        }

        if (($user["enabled"] == "no" || ($user["status"] == "pending")) && !$current_user->edit_users == 'yes') {
            message(URLROOT, LANG["NO_ACCESS_ACCOUNT_DISABLED"]);
        }

        $dropdown['countries'] = CountriesLogic::dropdown($user['country']);
        $dropdown['tz'] = timeZoneDropDown($user['tzoffset']);
        $dropdown['gender'] = "<option value='Male'" . ($user['gender'] == "Male" ? " selected='selected'" : "") . ">" . LANG["MALE"] . "</option>\n"
            . "<option value='Female'" . ($user['gender'] == "Female" ? " selected='selected'" : "") . ">" . LANG["FEMALE"] . "</option>\n";

        $torrents_query = "SELECT COUNT(`id`) FROM `torrents` WHERE owner=?";
        $count['numtorrents'] = DB::run($torrents_query, [$id])->fetchColumn();
        $comments_query = "SELECT COUNT(`id`) FROM `comments` WHERE user=?";
        $count['numcomments'] = DB::run($comments_query, [$id])->fetchColumn();
        $posts_query = "SELECT COUNT(`id`) FROM `forum_posts` WHERE userid=?";
        $count['numforumposts'] = DB::run($posts_query, [$id])->fetchColumn();
        $snatched_query = "SELECT COUNT(`hnr`) FROM `snatched` WHERE uid=?";
        $count['numhnr'] = DB::run($snatched_query, [$id])->fetchColumn();

        $html['userratio'] = $user["downloaded"] > 0 ? number_format($user["uploaded"] / $user["downloaded"], 1) : "---";
        $html['avatar'] = htmlspecialchars($user["avatar"]) ? htmlspecialchars($user["avatar"]) : URLROOT . "/page/theme/default/images/misc/default_avatar.webp";
        $html['usersignature'] = stripslashes($user["signature"]);
        $html['country'] = CountriesLogic::fetchRow($user['country']);

        // Init Data
        $data = [
            'dropdown' => $dropdown,
            'count' => $count,
            'user' => $user,
            'html' => $html
        ];
        return $data;
    }

    // Edit Profile Form Submit
    public static function submit($id, $data)
    {

        if (!empty($_POST)) {
            // Init Data
            $data = [
                'privacy' => Input1('privacy'),
                'notifs' => Input1('pmnotif') == 'yes' ? "[pm]" : "",
                'stylesheet' => Input1('stylesheet'),
                'client' => Input1('client'),
                'age' => Input1('age', 'int'),
                'gender' => Input1('gender'),
                'country' => Input1('country'),
                'avatar' => Input1('avatar'),
                'title' => Input1('title'),
                'hideshoutbox' => Input1('hideshoutbox') == 'yes' ? 'yes' : 'no',
                'tzoffset' => Input1('tzoffset', 'int'),
                'signature' => Input1('signature')
            ];

            if ($_POST['resetpasskey']) {
                $rand = array_sum(explode(" ", microtime()));
                $passkey = md5($data['current_user']->username . $rand . $data['current_user']->secret . ($rand * mt_rand()));
                $data['passkey'] = $passkey;
            }

            // Update
            DB::updateset("users", $data, ['id' => $id]);
            message(("profile/view/$id"), LANG["UPDATE_COMPLETE"]);
        }
    }

    // Admin Edit Profile Form Submit
    public static function adminSubmited($id, $current_user)
    {
        $email = Input1('email', 'email');

        // Check User
        if ($current_user->class < 5 && $id != $current_user->id) {
            message(("profile/view/$id"), LANG["NO_PERMISSION"]);
        }

        // Init Data
        $data = [
            'downloaded' => strtobytes(Input1('downloaded')),
            'uploaded' => strtobytes(Input1('uploaded')),
            'ip' => Input1('ip'),
            'class' => Input1('class'),
            'donated' => Input1('donated'),
            'password' => Input1('password'),
            'warned' => Input1('warned'),
            'downloadbanned' => Input1('downloadbanned'),
            'shoutboxpos' => Input1('shoutboxpos'),
            'modcomment' => Input1('modcomment'),
            'enabled' => Input1('enabled'),
            'invites' => Input1('invites'),
            'email' => $email,
            'seedbonus' => Input1('bonus'),
        ];

        // Change User Class
        if ($data['class'] != 0 && $data['class'] != $current_user->class) {
            // change user class
            $users_query = "SELECT class FROM `users` WHERE id = ?";
            $arr = DB::run($users_query, [$id])->fetch(PDO::FETCH_ASSOC);
            $uc = $arr['class'];
            // skip if class is same as current
            if ($uc != $data['class'] && $uc > $current_user->class) {
                message(URLROOT, LANG["YOU_CANT_DEMOTE_YOURSELF"]);
            } elseif ($uc == $current_user->class) {
                message(URLROOT, LANG["YOU_CANT_DEMOTE_SOMEONE_SAME_LVL"]);
            } else {
                $users_query = "UPDATE users SET class = ? WHERE id=?";
                DB::run($users_query, [$data['class'], $id]);
                // Notify user
                $prodemoted = ($data['class'] > $uc ? "promoted" : "demoted");
                $msg = "You have been $prodemoted to " . GroupsLogic::GetUserClassName($data['class'], $current_user) . " by " . $current_user->username . "";
                MessagesLogic::newinsert(['sender' => 0, 'receiver' => $id, 'added' => CONFIG['TT_DATE'], 'subject' => 'Group Change', 'msg' => $msg]);
            }
        }

        // Reset Passkey Check
        if ($_POST['resetpasskey'] == 'yes') {
            $rand = array_sum(explode(" ", microtime()));
            $passkey = md5($current_user->username . $rand . $current_user->secret . ($rand * mt_rand()));
            $data = ['passkey' => $passkey];
        }

        // Change Password
        $chgpasswd = Input1('chgpasswd') == 'yes' ? true : false;
        if ($chgpasswd) {
            $users_query = "SELECT password FROM `users` WHERE id = ?";
            $passres = DB::run($users_query, [$id])->fetch(PDO::FETCH_ASSOC);
            if ($data['password'] != $passres['password']) {
                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $data = ['password' => $password];
                LogsLogic::write($current_user->username . " has changed password for user: $id");
            }
        }

        // Update
        DB::updateset("users", $data, ['id' => $id]);
        LogsLogic::write($current_user->username . " has edited user: $id details");
        message(("profile/view/$id"), LANG["UPDATE_COMPLETE"]);
    }

    // Delete Profile Default Page otdo (tidy up)
    public static function deleteprofile($userid, $current_user)
    {
        // Check User Input
        $delreason = Input1('delreason');
        $sure = Input1('sure', 'int');

        // Check User
        if (!$current_user->delete_users) {
            message(("profile/view/$userid"), LANG["NO_PERMISSION"]);
        }

        if (!intval($userid)) {
            message(("profile/view/$userid"), LANG["MISSING_ID"]);
        }

        if ($current_user->id == $userid) {
            message(("profile/view/$userid"), LANG["YOU_CANT_DELETE_ADMIN"]);
        }

        if (!$delreason) {
            message(("profile/view/$userid"), LANG["MISSING_FORM_DATA"]);
        }

        // Delete Profile
        UsersLogic::deleteUser($userid);
        LogsLogic::write($current_user->username . " has deleted account: " . coloredname($userid) . "");
        message(("profile/view/$userid"), LANG["DEL_COMPLETE"]);
    }

}