<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use Bootstrap\Libs\Validate;

/*
* Invites Table Data
*/
class InvitesLogic
{
    // Admin Fetch All Pending Users
    public static function sendInvite($current_user)
    {
        // Check User Input
        $email = Input1('email', 'email');

        // Check Email Not Banned
        $message = BansLogic::checkEmail($email);
        if ($message) {
            message(URLROOT, $message);
        }

        // Set Invite
        $secret = mksecret();
        $username = "invite_" . mksecret(20);
        $users_query = "INSERT INTO `users` (username,secret,email,status,invited_by,added,stylesheet,language) VALUES (?,?,?,?,?,?,?,?)";
        DB::run($users_query, 
        [$username, $secret, $email, 'pending',$current_user->id, CONFIG['TT_DATE'], CONFIG['DEFAULTTHEME'], CONFIG['DEFAULTLANG']]);

        $invitees = "$id ".$current_user->invitees."";
        $users_query = "UPDATE users SET invites = invites - 1, invitees=? WHERE id = ?";
        DB::run($users_query, [$invitees,$current_user->id]);
        // Send Email
        $mess = strip_tags($_POST["mess"]);
        $names = CONFIG['SITENAME'];
        $links = URLROOT;
        $emailmain = CONFIG['SITEEMAIL'];
        
        $body = file_get_contents(VIEW_PATH . "/email/inviteuser.php");
        $body = str_replace("sitename%", $names, $body);
        $body = str_replace("%username%", $current_user->username, $body);
        $body = str_replace("%email%", $email, $body);
        $body = str_replace("%mess%", $mess, $body);
        $body = str_replace("%links%", $links, $body);
        $body = str_replace("%id%", $id, $body);
        $body = str_replace("%secret%", $secret, $body);

        $TTMail = new TTMail();
        $TTMail->Send($email, "$names user registration confirmation", $body, "", "-f$emailmain");
        message(URLROOT, LANG["A_CONFIRMATION_EMAIL_HAS_BEEN_SENT"] . " (" . htmlspecialchars($email) . "). " . LANG["ACCOUNT_CONFIRM_SENT_TO_ADDY_REST"] . " <br/ >");
    }

public static function InviteTree(int $user_id): array
{
    $rows = DB::run(
        'SELECT * FROM users WHERE status = ? AND invited_by = ? ORDER BY username',
        ['confirmed', $user_id]
    )->fetchAll(\PDO::FETCH_ASSOC);

    if ($rows === []) {
        redirect('profile/view/' . $user_id, __('USER_NOT_INVITED'), 'warning');
    }

    return [
        'rows'  => $rows,
        'num'   => count($rows),
        'title' => __('INVITES') . ' #' . $user_id,
    ];
}

    // Invite Form Submit
    public static function submitInvite()
    {
        // Check User Input
        $url = Input1('pending') == 0 ? '/invite/home' : '/invite/home/pending';
        $ids = multiIds('users');
        
        $users_query = "SELECT u.id, u.invited_by, i.invitees FROM users u LEFT JOIN users i ON u.invited_by = i.id WHERE u.status = 'pending' AND u.invited_by != '0' AND u.id IN ($ids)";
        $res = DB::run($users_query);
        foreach ($res as $row) {
            # We remove the invitee from the inviter and give them back there invite.
            $invitees = str_replace("$row[id] ", "", $row["invitees"]);
            $views = $row["invites"] + 1;
            $users_query = "UPDATE users SET invites=? AND invitees=? WHERE id=?";
            DB::run($users_query, [$views,$invitees,$row['invited_by']]);
            UsersLogic::deleteUser($row['id']);
        }

        return $url;
    }

    // Admin
    public static function pendingA()
    {
        // Pagination
        $users_query = "SELECT count(id) FROM `users` WHERE status = ? AND invited_by !=";
        $count = DB::run($users_query, ['pending',0])->fetchColumn();
        list($pager, $limit) = pagination(25, $count, URLROOT.'/invite/home/pending?');
        $users_query = "SELECT u.id, u.username, u.email, u.added, u.invited_by, i.username as inviter FROM users u LEFT JOIN users i ON u.invited_by = i.id WHERE u.status = 'pending' AND u.invited_by != '0' ORDER BY u.added DESC $limit";
        $stmt = DB::run($users_query);
        $array = ['invites'=>$stmt,'count'=>$count,'pager'=>$pager];
        
        return $array;
	}
	
    // Admin
    public static function inviteA()
    {
        // Pagination
        $users_query = "SELECT count(id) FROM `users` WHERE status=? AND invited_by !=?";
        $count = DB::run($users_query, ['confirmed',0])->fetchColumn();
        list($pager, $limit) = pagination(25, $count, URLROOT.'/invite/home?');
        $users_query = "SELECT u.id, u.username, u.email, u.added, u.last_access, u.class, u.invited_by, i.username as inviter FROM users u 
		LEFT JOIN users i ON u.invited_by = i.id 
		WHERE u.status = 'confirmed' AND u.invited_by != '0' ORDER BY u.added DESC $limit";
        $users = DB::run($users_query)->fetchAll();
        $array = ['invites'=>$users,'count'=>$count,'pager'=>$pager];
        return $array;
    }

}