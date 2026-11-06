<?php

namespace Bootstrap\Libs\Torrents;

use Bootstrap\Libs\DB;
use Bootstrap\Models\MessagesLogic;
use PDO;

/*
* Clean Old Data (10 mins)
*/
class Cleanup
{
    // Automatic System Update Function
    public static function autoClean()
    {
        $task_query = "SELECT last_time FROM `tasks` WHERE task = ?";
        $row = DB::run($task_query, ['cleanup'])->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            DB::run($task_query, ['cleanup', CONFIG['TT_TIME']]);
            return;
        }

        if ($row['last_time'] + CONFIG['AUTOCLEANINTERVAL'] > CONFIG['TT_TIME']) {
            return;
        }

        $task_query = "UPDATE `tasks` SET last_time =? WHERE task=? AND last_time=?  ";
        $planned_clean = DB::run($task_query, [CONFIG['TT_TIME'], 'cleanup', $row['last_time']]);
        if (!$planned_clean) {
            return;
        }

        self::run();
    }

    // Run Cleanup Operations
    public static function run()
    { 
        self::deletePeers();
        self::makeInvisible();
        self::bonus();
        self::vipUntil();
        self::pendingUser();
        self::deleteLogs();
        self::freeleech();
        
        if (CONFIG['RATIOWARNENABLE']) {
            self::ratioWarn();
        }
        self::expiredWarn();
        self::isWarned();
        self::autoInvite();
        if (CONFIG['HNR_ON']) {
            self::hitAndRun();
        }
        //self::autoPromote(); todo
    }

    // LOCAL TORRENTS - DELETE OLD NON-ACTIVE PEERS
    public static function deletePeers()
    {
        $deadtime = date("Y-m-d H:i:s", CONFIG['TT_TIME'] - CONFIG['ANNOUNCEINTERVAL']);
        $peers_query = "DELETE FROM `peers` WHERE last_action < ?";
        DB::run($peers_query, [$deadtime]);
    }

    // LOCAL TORRENTS - MAKE NON-ACTIVE/OLD TORRENTS INVISIBLE
    public static function makeInvisible()
    {
        $deadtime = CONFIG['TT_TIME'] - CONFIG['MAXDEADTORRENTTIMEOUT'];
        $torrents_query = "UPDATE `torrents` SET visible=?
                 WHERE visible=? AND last_action < FROM_UNIXTIME(?) AND seeders = ? AND leechers = ? AND external !=?";
        DB::run($torrents_query,
                 ['no', 'yes', $deadtime, 0, 0, 'yes']);
    }

    // Set Bonus every hour
    public static function bonus()
    {
        $tasks_query = "SELECT last_time FROM `tasks` WHERE task = ?";
        $row = DB::run($tasks_query, ['bonus'])->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $tasks_query = "INSERT INTO `tasks` (task, last_time) VALUES (?,?)";
            DB::run($tasks_query, ['bonus', CONFIG['TT_TIME']]);
        }
        if ($row['last_time'] + CONFIG['ADDBONUS'] < CONFIG['TT_TIME']) {
            $peers_query = "SELECT DISTINCT userid as peer, (
                SELECT DISTINCT COUNT( torrent )
             FROM peers
             WHERE seeder = ?  AND userid = peer) 
             AS count
             FROM peers WHERE seeder = ?";
            $res1 = DB::run($peers_query, ['yes', 'yes'])->fetchAll();
            foreach ($res1 as $row) {
                $users_query = "UPDATE `users` SET seedbonus = seedbonus + '" . (CONFIG['BONUSPERTIME'] * $row['count']) . "' WHERE id = ?";
                DB::run($users_query, [$row['peer']]);
                $tasks_query = "UPDATE `tasks` SET last_time =? WHERE task=? AND last_time=?  ";
                DB::run($tasks_query, [CONFIG['TT_TIME'], 'bonus', $row['last_time']]);
            }
        }
    }

    // Reset VIP Until
    public static function vipUntil()
    {
        $users_query = "SELECT id, oldclass FROM `users` WHERE vipuntil <? AND oldclass !=?";
        $rowv1 = DB::run($users_query, [CONFIG['TT_DATE'], 0]);
        foreach ($rowv1 as $rowv) {
            MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$rowv['id'], 'added'=>CONFIG['TT_DATE'], 'subject'=> 'Your VIP class stay has just expired', 'msg'=>'Your VIP class stay has just expired']);
            $users_query = "UPDATE `users` SET class=? AND oldclass=? AND vipuntil=? WHERE vipuntil <=?";
            DB::run($users_query, [$rowv['oldclass'], 0, null, CONFIG['TT_DATE']]);
        }
    }

    // DELETE PENDING USER ACCOUNTS OVER TIMOUT AGE
    public static function pendingUser()
    {
        $deadtime = CONFIG['TT_TIME'] - CONFIG['SIGNUPTIMEOUT'];
        $users_query = "DELETE FROM `users` WHERE status = ? AND added < FROM_UNIXTIME(?)";
        DB::run($users_query, ['pending', $deadtime]);
    }

    // Delete Old Logs
    public static function deleteLogs()
    {
        $ts = CONFIG['TT_TIME'] - CONFIG['LOGCLEAN']  * 86400;
        $log_query = "DELETE FROM `log` WHERE added < FROM_UNIXTIME(?)";
        DB::run($log_query, [$ts]);
    }

    // Set Free Leech
    public static function freeleech()
    {
        if (CONFIG['FREELEECHGBON']);{
            $torrents_query = "SELECT id, name FROM `torrents` WHERE banned=? AND freeleech =? AND size >=? ";
            $query = DB::run($torrents_query, ['no', 0, CONFIG['FREELEECHGB']]);
            if ($query) {
                foreach ($query as $row) {
                    $torrents_query = "UPDATE `torrents` SET freeleech =? WHERE id=?";
                    DB::run($torrents_query, [1, $row['id']]);
                    $log_query = "INSERT INTO `log` (added, txt) VALUES (?,?)";
                    DB::run($log_query,
                    [CONFIG['TT_DATE'], "Freeleech added on  <a href='torrent/$row[id]'>$row[name]</a> because it is bigger than " . CONFIG['FREELEECHGB'] . ""]);
                }
            }
        }
    }

    // LEECH WARN USERS WITH LOW RATIO
    public static function ratioWarn()
    {
        $downloaded = CONFIG['RATIOWARN_MINGIGS'] * 1024 * 1024 * 1024;
        // ADD RATIO WARNING
        $users_query = "SELECT id, username FROM `users` WHERE class <=? AND warned =? AND enabled =? AND uploaded / downloaded < ? AND downloaded >=?";

        $res =DB::run($users_query, [CONFIG['_UPLOADER'], 'no', 'yes', CONFIG['RATIOWARNMINRATIO'], $downloaded]);
        if ($res) {
            $reason = "Auto Leech warning has been added You have been warned because of having low ratio. You need to get a " . CONFIG['RATIOWARNMINRATIO'] . " before next " . CONFIG['RATIOWARN_DAYSTOWARN'] . " days or your account may be banned.";
            $expiretime = date("Y-m-d H:i:s", CONFIG['TT_TIME'] + (86400 * CONFIG['RATIOWARN_DAYSTOWARN']));
            $msg = "".LANG['YOUVE_WARNED_BY']." " . $_SESSION['username'] . " - Reason: " . $reason . " - Expiry: " . $expiretime . "";
            foreach ($res as $arr) {
                $warnings_query = "INSERT INTO `warnings` (userid, reason, added,expiry,warnedby,type) VALUES (?,?,?,?,?,?)";
                DB::run($warnings_query, [$arr['id'], $reason, CONFIG['TT_DATE'],$expiretime,0,'Poor Ratio']);

            }
        }
        // REMOVE RATIO WARNING
        $users_query = "SELECT users.id, users.username FROM `users` INNER JOIN warnings ON users.id=warnings.userid
                         WHERE type=? AND active = ? AND warned = ? AND enabled=? AND uploaded / downloaded >= ? AND downloaded >= ?";
        $res1 = DB::run($users_query, ['Poor Ratio', 'yes', 'yes', 'yes', CONFIG['RATIOWARNMINRATIO'], $downloaded])->fetchAll();
        if ($res1) {
            $reason = "Auto Leech warning has been removed Your warning of low ratio has been removed. We highly recommend you to keep a your ratio up to not be warned again.\n";
            foreach ($res1 as $arr1) {
                $warnings_query = "INSERT INTO `warnings` (id, reason, warned) VALUES (?,?,?)";
                DB::run($warnings_query, [$arr1['id'], $reason, 'no']);
            }
        }
        // BAN RATIO WARNED USERS
        $users_query = "SELECT users.id, users.username, UNIX_TIMESTAMP(warnings.expiry) AS expiry FROM users INNER JOIN warnings ON users.id=warnings.userid
                        WHERE type=? AND active = ? AND class = ? AND enabled=? AND warned = ? AND uploaded / downloaded < ? AND downloaded >= ?";
        $res = DB::run($users_query, ['Poor Ratio', 'yes', 1, 'yes', 'yes', CONFIG['RATIOWARNMINRATIO'], $downloaded])->fetchAll();
        if ($res) {
            foreach ($res as $arr) {
                if (CONFIG['TT_TIME'] - $arr["expiry"] >= 0) {
                    $users_query = "UPDATE `users` SET warned=? AND enabled=? WHERE id=?";
                    DB::run($users_query, ['no', 'no', $arr['id']]);
                    $log_query = "INSERT INTO `log` (added, txt) VALUES (?,?)";
                    DB::run($log_query,
                    [CONFIG['TT_DATE'], "User <a href='" . URLROOT . "/profile/view/" . $arr["id"] . "'>" . coloredname($arr["id"]) . "</a> has been banned (Auto Leech warning)."]);

                }
            }
        }
    }

    // Warning Expired
    public static function expiredWarn()
    {
        // REMOVE EXPIRED WARNINGS
        $users_query = "SELECT users.id, users.username, warnings.expiry FROM users INNER JOIN warnings ON users.id=warnings.userid
                    WHERE type != ? AND warned = ? AND enabled=? AND warnings.active = ? AND warnings.expiry < ?";
        $res = DB::run($users_query, ['Poor Ratio', 'yes', 'yes', 'yes', CONFIG['TT_DATE']])->fetchAll();
        if ($res) {
            foreach ($res as $arr1) {
                $users_query = "UPDATE `users` SET warned=? WHERE id=?";
                DB::run($users_query, ['no', $arr1['id']]);
                $warnings_query = "UPDATE `warnings` SET active = ? WHERE userid = ? AND expiry < ?";
                DB::run($warnings_query, ['no', $arr1['id'], CONFIG['TT_DATE']]);
                $log_query = "INSERT INTO `log` (added, txt) VALUES (?,?)";
                DB::run($log_query,
                    [CONFIG['TT_DATE'], "Removed warning for $arr1[username]. Expiry: $arr1[expiry]"]);
            }
        }
    }

    // Set User Warned
    public static function isWarned()
    {
        // UPDATE USERS THAT STILL HAVE ACTIVE WARNINGS
        $users_query = "UPDATE `users` SET warned = 'yes' WHERE warned = 'no' AND id IN (SELECT userid FROM warnings WHERE active = 'yes')";
        DB::run($users_query);
    }

    // Invite update function
    public static function autoInvites($interval, $minlimit, $maxlimit, $minratio, $invites, $maxinvites)
    {
        $time = CONFIG['TT_TIME'] - ($interval * 86400);
        $minlimit = $minlimit * 1024 * 1024 * 1024;
        $maxlimit = $maxlimit * 1024 * 1024 * 1024;
    
        $users_query = "SELECT id, username, class, invites 
                        FROM users 
                        WHERE enabled = ? AND status = ? AND downloaded >= ? 
                        AND downloaded < ? AND uploaded / downloaded >= ? AND warned = ? 
                        AND UNIX_TIMESTAMP(invitedate) <= ?";
        $res = DB::run($users_query,
        ['yes', 'confirmed', $minlimit, $maxlimit, $minratio, 'no', $time]);
        if ($res) {
            foreach ($res as $arr) {
                $maxninvites = $maxinvites[$arr['class']];
                if ($arr['invites'] >= $maxninvites) {
                    continue;
                }
                if (($maxninvites - $arr['invites']) < $invites) {
                    $invites = $maxninvites - $arr['invites'];
                }
                $users_query = "UPDATE `users` SET invites = invites+$invites, invitedate = NOW() WHERE id=$arr[id]";
                DB::run($users_query);
                $log_query = "INSERT INTO `log` (added, txt) VALUES (?,?)";
                DB::run($log_query,
                    [CONFIG['TT_DATE'], "Gave $invites invites to $arr[username]"]);
            }
        }
    }

    // GIVE INVITES ACCORDING TO RATIO/GIGS (max 20)
    public static function autoInvite()
    {
        self::autoInvites(14, 1, 4, 0.90, 2, 20);
    }

    // Set Hit & Runs
    public static function hitAndRun()
    {
        $timenow = CONFIG['TT_TIME'];
        $length = CONFIG['HNR_DEADLINE'] * 86400; // 7 days
        $seedtime = CONFIG['HNR_SEEDTIME'] * 3600; // 48 hours
        // Update Snatched
        $snatched_query = "UPDATE snatched SET hnr = ? WHERE completed = ? AND hnr = ? AND upload < download AND $timenow - $length > start_time AND $seedtime > last_time AND done=?";
        DB::run($snatched_query, ['yes', 'yes', 'no', 'no']);
        $snatched_query = "UPDATE `snatched` SET `hnr` = ? WHERE `hnr` = ? AND upload >= download";
        DB::run($snatched_query, ['no', 'yes']);
        $snatched_query = "UPDATE `snatched` SET `hnr` = ? WHERE `hnr` = ? AND last_time >= ?";
        DB::run($snatched_query, ['no', 'yes', $seedtime]);
        // Do We Have Snatched
        $snatched_query = "SELECT DISTINCT uid FROM `snatched` WHERE hnr=? AND done=?";
        $a =  DB::run($snatched_query, ['yes', 'no']);
        if ($a):
            foreach ($a as $b):
                
                // Check User
                $user = $b['uid'];
                $snatched_query = "SELECT count(hnr) FROM `snatched` WHERE uid=? ND hnr=?";
                $count = DB::run($snatched_query, [$user, 'yes'])->fetchColumn();
                $expiretime = date("Y-m-d H:i:s", $timenow + $length);
                // Check Wrning
                $warnings_query = "SELECT type, active FROM `warnings` WHERE userid=? ";
                $f = DB::run($warnings_query, [$user])->fetch(PDO::FETCH_ASSOC);
                $type = $f['type'];
                // Warn
                if ($count >= CONFIG['HNR_WARN'] && $type != "HnR"):
                    $reason = "" . LANG["CLEANUP_WARNING_FOR_ACCUMULATING"] . " " . CONFIG['HNR_WARN'] . " H&R.";
                    $subject = "" . LANG["CLEANUP_WARNING_FOR_H&R"] . "";
                    $msg = "" . LANG["CLEANUP_YOU_HAVE_BEEN_WARNEWD_ACCUMULATED"] . " " . CONFIG['HNR_WARN'] . " " . LANG["CLEANUP_H&R_INVITE_CHECK_RULE"] . "\n[color=red]" . LANG["CLEANUP_MSG_WARNING_7_DAYS_BANNED"] . "[/color]";
                    $users_query = "SELECT enabled FROM `users` WHERE id = ?";
                    $rov =DB::run($users_query, [$user])->fetch(PDO::FETCH_ASSOC);

                    if ($rov["enabled"] == "yes"):
                        $users_query = "UPDATE users SET warned=? WHERE id=?";
                        DB::run($users_query, ['yes', $user]);
                        MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$user, 'added'=>CONFIG['TT_DATE'],'msg'=>$msg,'subject'=>$subject]);
                    endif;
                endif;
                // Unwarn
                if ($count < CONFIG['HNR_WARN'] && $type == "HnR"):
                    $subject = "" . LANG["CLEANUP_REMOVAL_OF_H&R_WARNING"] . "";
                    $msg = "" . LANG["CLEANUP_YOU_NOW_HAVE_LESS_THAN"] . " " . CONFIG['HNR_WARN'] . " H&R.\n" . LANG["CLEANUP_YOUR_WARNING_FOR_H&R_HAS_REMOVED"] . "";
                    $users_query = "UPDATE users SET warned=? WHERE id=?";
                    DB::run($users_query, ['no', $user]);
                    $warnings_query = "DELETE FROM `warnings` WHERE userid = ? AND  type=?";
                    DB::run($warnings_query, [$user, 'HnR']);
                    MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$user, 'added'=>CONFIG['TT_DATE'],'msg'=>$msg,'subject'=>$subject]);
                endif;
                // Ban
                if ($count >= CONFIG['HNR_BAN']):
                    $users_query = "SELECT username, email, modcomment FROM `users`  WHERE id = ?";
                    $h = DB::run($users_query, [$user])->fetch(PDO::FETCH_ASSOC);
                    
                    $modcomment = $h[2];
                    $modcomment = date("d/m/Y") . " - " . LANG["CLEANUP_BANNED_FOR"] . " " . $count . " H&R.\n " . $modcomment;
                    $users_query = "UPDATE users SET enabled=? AND warned=? AND modcomment=? WHERE id=?";
                    DB::run($users_query, ['no', 'no', $modcomment, $user]);
                    $warnings_query = "DELETE FROM warnings WHERE userid' = ? AND type";
                    DB::run($warnings_query, [$user, 'HnR']);
                    $log_query = "INSERT INTO `log` (added, txt) VALUES (?,?)";
                    DB::run($log_query,
                    [CONFIG['TT_DATE'], LANG["CLEANUP_THE_MEMBER"] . " <a href='account-details.php?id=" . $user . "'>" . $h[0] . "</a> " . LANG["CLEANUP_HAS_BEEN_BANNED_REASON"] . " " . $count . " H&R."]);

                    $sitename = URLROOT;
                    $body = file_get_contents(VIEW_PATH . "/email/hitnrunban.php");
                    $body = str_replace("%count%", $count, $body);
                    $body = str_replace("%sitename%", $sitename, $body);
                    $TTMail = new TTMail();
                    $TTMail->Send($h[1], LANG["CLEANUP_HAS_BEEN_DISABLED"], "$body", "From: " . CONFIG['SITEEMAIL'] . "", "-f" . CONFIG['SITEEMAIL'] . "");
                endif;
                // Download Ban
                if ($count >= CONFIG['HNR_STOP_DL']){
                    $users_query = "UPDATE users SET downloadbanned=? WHERE id=? AND downloadbanned=? ";
                    DB::run($users_query, ['yes', $user, 'no']);
                    MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$user, 'added'=>CONFIG['TT_DATE'],'msg'=>'Too many Hit & Runs. You are Download Banned','subject'=>'Download Banned']);
                }
            endforeach;
        endif;
    }
    
    // Set Auto Promotion / Demotion
    public static function autoPromote() {
        $minratio = 0.9; # ratio for demotion to LEECHE
        $gigs = 50 * 1073741824; # 50 GB
        $delay2 = date("Y-m-d H:i:s", CONFIG['TT_TIME'] - 1 * 1); # Joined > 1 month
        
        // auto promote by gb
        $users_query = "SELECT id,username FROM `users` WHERE class =? AND uploaded / downloaded >";
        $res = DB::run($users_query,  [1, $minratio]);
        foreach ($res as $arr) {
            $groups_query = "SELECT level FROM `groups` WHERE group_id=? LIMIT 1";
            $res_classname = DB::run($groups_query, [CONFIG['_POWERUSER']])->fetch(PDO::FETCH_ASSOC);
            if ($res_classname->rowCount() == 1) {
                $arr_classname = $res_classname;
                $new_classname = "$arr_classname[level]";
            }
            $username = $arr['username'];
            $users_query = "UPDATE users SET class=? WHERE id =?";
            DB::run($users_query, [CONFIG['_POWERUSER'], $arr['id']]);
            $msgg = '[b]Congratulations[/b], you were automatically promoted to [b]Member[/b] class. Please note that if your ratio drops below [b]" . $minratio . "[/b] at any time,  you will be demoted to [b]Leecher[/b]';
            MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$arr['id'], 'added'=>CONFIG['TT_DATE'],'msg'=>$msgg,'subject'=>"You have been promoted as " . $new_classname . ""]);
            unset($res, $arr, $res_classname, $arr_classname,$new_classname);
        }

        // auto demote to leecher
        $users_query = "SELECT id,username,modcomment FROM `users` WHERE class <? AND uploaded / downloaded <";
        $res = DB::run($users_query,  [4, $minratio]);
        foreach ($res as $arr) {
            $username = $arr['username'];
            $modcomment = $arr['modcomment'];
            $modcomment2 = date("d-M-Y") . " - Has been demoted by System to Leecher \n";
            $modcomment = $modcomment2 . "" . $modcomment;
            $modcom = $modcomment;
            $users_query = "UPDATE users SET class = 1, modcomment = CONCAT($modcom,modcomment) WHERE id = $arr[id]";
            DB::run($users_query);
            $msgg = 'You were automatically demoted to [b]Leecher[/b]. That happened because your ratio dropped below [b]" . $minratio . "[/b]';
            MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$arr['id'], 'added'=>CONFIG['TT_DATE'],'msg'=>$msgg,'subject'=> 'You have been demoted to Leecher']);
            $log_query = "INSERT INTO `log` (added, txt) VALUES (?,?)";
            DB::run($log_query,
                    [CONFIG['TT_DATE'], "<a href=/account-details.php?id=$arr[id]><b>$username</b></a> has been demoted by System to <b>Leecher</b> class"]);
        }
        
        // auto promote to class 3
        $users_query = "SELECT id, username FROM users WHERE (class = 1 || class = 2) AND warned = 'no' AND added < $delay2 AND uploaded >= $gigs AND uploaded >= downloaded";
        $res = DB::run($users_query);
        foreach ($res as $arr) {
            $groups_query = "SELECT level FROM `groups` WHERE group_id=? LIMIT 1";
            $res_classname = DB::run($groups_query, [3])->fetch(PDO::FETCH_ASSOC);
            if ($res_classname->rowCount() == 1) {
                $arr_classname = $res_classname;
                $new_classname = "$arr_classname[level]";
            }
            $username = $arr['username'];
            $users_query = "UPDATE users SET class=? WHERE id=?";
            DB::run($users_query, [3, $arr['id']]);
            $msgg = '[b]Congratulations[/b], you were automatically promoted to [b]Power User[/b] class. Please note that if your ratio drops below [b]" . $minratio . "[/b] at any time,  you will be demoted to [b]Leecher[/b]';
            MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$arr['owner'], 'added'=>CONFIG['TT_DATE'],'msg'=>$msgg,'subject'=> '[b]Congratulations[/b]']);
            unset($res, $arr, $res_classname, $arr_classname,$new_classname);
        }

        // uploader mod test
        $torrents_query = "SELECT torrents.owner, COUNT(*) AS counta 
                          FROM torrents 
                          INNER JOIN users ON (torrents.owner=users.id) 
                          WHERE users.class < 2  AND users.donated = 0 AND torrents.banned = 'no' AND torrents.added > DATE_SUB(NOW(), INTERVAL 15 DAY) 
                          GROUP BY torrents.owner";
        $query = DB::run($torrents_query);
        foreach ($query as $UP){
            if ($UP['counta'] > 3){
                $users_query = "UPDATE users SET class=? WHERE id=?";
                DB::run($users_query, [CONFIG['_POWERUSER'], $UP['owner']]);
                $subject = 'Automatic Promotion To Uploader Status';
                $msg = 'Hello you did 1 upload, you are promoted to Uploader, Bravo !!! ';
                MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$UP['owner'], 'added'=>CONFIG['TT_DATE'], 'subject'=> $subject,'msg'=>$msg, 'unread'=>'yes', 'location'=>'in']);
            }
        }

        // Uploader demote if he did not upload 1 torrent over 2 week
        foreach ($query as $up){
            $torrents_query = "SELECT name, added, DATE_SUB(NOW(), INTERVAL 15 DAY) AS date_expiration FROM torrents WHERE owner = $up[id]";
            $query2 = DB::run($torrents_query);
            foreach ($query2 as $up2) {
                if ($up2["added"] > $up2["date_expiration"]){
                    $nbre = 0 + 1;
                } else {
                    $nbre = 0;
                }
            }
        }
        if ($nbre < 1) {
            $users_query = "UPDATE users SET class=? WHERE id=?";
            DB::run($users_query, [2, $up['id']]);
            $subject = 'Automatic Downgrading to Member Status';
            $msg = 'You have not uploaded in the last fortnight so you have been demoted from uploader.';
            MessagesLogic::newinsert(['sender'=>0, 'receiver'=>$up['id'], 'added'=>CONFIG['TT_DATE'], 'subject'=> $subject,'msg'=>$msg, 'unread'=>'yes', 'location'=>'in']);
        }
    }

}