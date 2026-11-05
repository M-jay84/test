<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Ip;
use Bootstrap\Libs\Auth;
use Bootstrap\Libs\TTMail;
use Bootstrap\Models\BansLogic;
use Bootstrap\Models\MessagesLogic;
use PDO;

/*
* Account Data
*/
class AccountsLogic
{
    // Login If User Allowed
/**
 * Handle user login
 * Returns ['error' => '...'] on failure
 * On success it sets the session/cookie and calls message()
 */
public static function login(array $input): array
{
    // CSRF
    /*
    if (function_exists('csrf_verify') && !csrf_verify($input['csrf_token'] ?? '')) {
        return ['error' => 'Invalid CSRF token'];
    }
        */

    // Captcha
    captcha_response($input['g-recaptcha-response'] ?? '');

    $username = Input1('username');
    $password = Input1('password');
    $remember = !empty($input['remember']);   // checkbox

    if ($username === '' || $password === '') {
        return ['error' => LANG['MISSING_FORM_DATA']];
    }

    $sql = DB::run(
        "SELECT id, username, password, secret, passkey, status, enabled, class, role_id
         FROM users 
         WHERE username = ?",
        [$username]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$sql || !password_verify($password, $sql['password'])) {
        return ['error' => LANG['LOGIN_INCORRECT']];
    }

    if ($sql['status'] === 'pending') {
        return ['error' => LANG['ACCOUNT_PENDING']];
    }

    if ($sql['enabled'] === 'no') {
        return ['error' => LANG['ACCOUNT_DISABLED']];
    }

    // Success
    session_regenerate_id(true);

    // Short session always, long cookie only if "Remember me" is checked
    Auth::loginUser($sql, $remember ? 90 : 0);

    // Update last_login
    DB::run(
        "UPDATE users SET last_login = ? WHERE id = ?",
        [CONFIG['TT_DATE'], $sql['id']]
    );

    message(LANG['SUCCESS'], URLROOT);
}

    // Check invite Signup
    public static function checkinvite()
    {
        // Check Invite
        $invite = (isset($_GET['invite'])) ? $_GET['invite'] : 0;
        $secret = (isset($_GET['secret'])) ? $_GET['invite'] : 0;
        $invite_row = 0;

        if (!intval($invite) || strlen($secret) != 20) {
            if (CONFIG['INVITEONLY']) {
                message(URLROOT, "<center>" . LANG["INVITE_ONLY_MSG"] . "<br></center>");
            }
        } else {
            $invite_query = "SELECT id FROM `users` WHERE id = ? AND secret=?";
            $invite_row = DB::run($invite_query, [$invite,$secret])->fetch(PDO::FETCH_ASSOC);

            if (!$invite_row) {
                message(URLROOT, LANG["INVITE_ONLY_NOT_FOUND"] . "" . (CONFIG['SIGNUPTIMEOUT'] / 86400) . "days.");
            }
        }
        return $invite_row;
    }

    // Update User pass & secret
    public static function signupUser()
    {
        // Check Input
        captcha_response($_POST['g-recaptcha-response'] ?? '');
        $data = [
            'passagain' => Input1('passagain'),
            'invite' => Input1('invite', 'int'),
            'username' => Input1('wantusername'),
            'email' => Input1('email'),
            'password' => Input1('wantpassword'),
            'country' => Input1('country', 'int'),
            'gender' => Input1('gender'),
            'client' => Input1('client'),
            'age' => Input1('age', 'int'),
            'secret' => Input1('type'),
        ];
        
        // Check User Invite
        if (strlen($data['secret']) == 20 || !is_numeric($data['invite'])) {
            $users_query = "SELECT id FROM `users` WHERE id = ? AND secret=?";
            $invite_row1 = DB::run($users_query, [$data['invite'],$data['secret']])->fetch(PDO::FETCH_ASSOC);
            $invite_row = (int) $invite_row1['id'] ?? 0;
        }

        // Check User Input
        $message = "";

        if (empty($data['password']) || (empty($data['email']) && !$invite_row) || empty($data['username'])) {
            $message = LANG["DONT_LEAVE_ANY_FIELD_BLANK"];
        } elseif (strlen($data['username']) > 50) {
            $message = sprintf(LANG["USERNAME_TOO_LONG"], 16);
        } elseif ($data['password'] != $data['passagain']) {
            $message = LANG["PASSWORDS_NOT_MATCH"];
        } elseif (strlen($data['password']) < 6) {
            $message = sprintf(LANG["PASS_TOO_SHORT_2"], 6);
        } elseif (strlen($data['password']) > 16) {
            $message = sprintf(LANG["PASS_TOO_LONG_2"], 16);
        } elseif ($data['password'] == $data['username']) {
            $message = LANG["PASS_CANT_MATCH_USERNAME"];
        } elseif (!preg_match('/^[a-z0-9 .\-]+$/i', $data['username'])) {
            $message = LANG["INVALID_NAME"];
        } elseif ($data['invite_row'] = false && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $message = LANG["INVALID_EMAIL"];
        }

        if ($message == "") {
            // If NOT Invite Check
            if (!$invite_row) {
                // Check Max Users
                $users_query = "SELECT count(id) FROM `users`";
                $numsitemembers = DB::run($users_query)->fetchColumn();
                if ($numsitemembers >= CONFIG['MAXUSERS']) {
                    $msg = LANG["SITE_FULL_LIMIT_MSG"] . number_format(CONFIG['MAXUSERS']) . " " . LANG["SITE_FULL_LIMIT_REACHED_MSG"] . " " . number_format($numsitemembers) . " members";
                    message(URLROOT, $msg);
                }

                // Check Email Not Banned
                $message = BansLogic::checkEmail($data['email']);
            }

            // Check Username NOT In Use
            $users_query = "SELECT id FROM `users` WHERE username=?";
            $count =DB::run($users_query, [$data['username']])->fetchColumn();
            if ($count != 0) {
                $message = sprintf(LANG["USERNAME_INUSE_S"], $data['username']);
            }

            // Generate Secret
            $data['secret'] = mksecret();

            // Hash Password
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

            // Make Passkey
            $rand = array_sum(explode(" ", microtime()));
            $passkey = md5($data['username'] . $rand . $data['secret'] . ($rand * mt_rand()));
            $data['passkey'] = $passkey;
        }

        // Check Message Error
        if ($message != "") {
            message('authorize/login', $message);
        }

        // No Message So Proceed
        if ($message == "") {
            // Invited User
            if ($invite_row) {
                $users_query = "UPDATE users SET username=? AND password=? AND class=? AND secret=? AND status=? AND added=? AND passkey=?
                 WHERE id=?";
                DB::run($users_query, [$data['username'], $data['password'], 1, $data['secret'], 'confirmed', CONFIG['TT_DATE'], $data['passkey'], $invite_row]
                );

                // Welcome PM
                MessagesLogic::newinsert(['sender' => 0, 'receiver' => $invite_row, 'added' => CONFIG['TT_DATE'], 'subject' => 'Welcome', 'msg' => CONFIG['WELCOMEPM_MSG'], 'unread' => 'yes', 'location' => 'in']);

                // Post Shout
                $shoutbox_query = "INSERT INTO shoutbox (userid, date, user, message) VALUES (?, ?, ?, ?)";
                DB::run($shoutbox_query, [0, CONFIG['TT_DATE'], 'System', "new user: " . $data['username'] . " has joined."]);

                // Invite Activated
                message('authorize/login', LANG["ACCOUNT_ACTIVATED"]);
            }

            // Check To Confirm
            if (CONFIG['CONFIRMEMAIL'] || CONFIG['ACONFIRM']) {
                $data['status'] = 'pending';
            } else {
                $data['status'] = 'confirmed';
            }

            // Set Class
            if ($numsitemembers == '0') {
                $data['class'] = '7';
            } else {
                $data['class'] = '1';
            }

            // Standard Inputs
            $data['added'] = CONFIG['TT_DATE'];
            $data['last_login'] = CONFIG['TT_DATE'];
            $data['last_access'] = CONFIG['TT_DATE'];
            $data['stylesheet'] = CONFIG['DEFAULTTHEME'];
            $data['language'] = CONFIG['DEFAULTLANG'];
            $data['ip'] = Ip::getIP();

            // Insert User
            $users_query = "INSERT INTO users (username,password,secret,email,status,added,last_login,last_access,age,country,gender,client, 
                 stylesheet,language,class,ip,passkey) VALUES (?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            DB::run($users_query, [$data['username'], $data['password'], $data['secret'], $data['email'], 
                $data['status'], CONFIG['TT_DATE'], CONFIG['TT_DATE'], CONFIG['TT_DATE'],
                 $data['age'], $data['country'], $data['gender'], $data['client'], 
                 CONFIG['DEFAULTTHEME'], CONFIG['DEFAULTLANG'], $data['class'], Ip::getIP(), $data['passkey']]);

            $id = DB::lastInsertId();

            // Post Shout
            $shoutbox_query = "INSERT INTO shoutbox (userid, date, user, message) VALUES (?, ?, ?, ?)";
            DB::run($shoutbox_query, [0, CONFIG['TT_DATE'], 'System', "new user: " . $data['username'] . " has joined."]);
            // Welcome PM
            MessagesLogic::newinsert(['sender' => 0, 'receiver' => $id, 'added' => CONFIG['TT_DATE'], 'subject' => 'Welcome', 'msg' => CONFIG['WELCOMEPM_MSG'], 'unread' => 'yes', 'location' => 'in']);

            // Admin Confirm
            if (CONFIG['ACONFIRM']) {
                $body = LANG["YOUR_ACCOUNT_AT"] . " " . CONFIG['SITENAME'] . " " . LANG["HAS_BEEN_CREATED_YOU_WILL_HAVE_TO_WAIT"] . "\n\n" . CONFIG['SITENAME'] . " " . LANG["ADMIN"];
            } else {
                $body = LANG["YOUR_ACCOUNT_AT"] . " " . CONFIG['SITENAME'] . " " . LANG["HAS_BEEN_APPROVED_EMAIL"] . "\n\n	" . URLROOT . "/profile/confirmemail/$id/$data[secret]\n\n" . LANG["HAS_BEEN_APPROVED_EMAIL_AFTER"] . "\n\n	" . LANG["HAS_BEEN_APPROVED_EMAIL_DELETED"] . "\n\n" . URLROOT . " " . LANG["ADMIN"];
            }

            // Confirm Account
            if (CONFIG['CONFIRMEMAIL'] || CONFIG['ACONFIRM']) {
                $TTMail = new TTMail();
                $TTMail->Send($data['email'], "Your " . CONFIG['SITENAME'] . " User Account", $body, "", "-f" . CONFIG['SITEEMAIL'] . "");
                message('authorize/login', LANG["A_CONFIRMATION_EMAIL_HAS_BEEN_SENT"] . " (" . htmlspecialchars($data['email']) . "). " . LANG["ACCOUNT_CONFIRM_SENT_TO_ADDY_REST"] . " <br/ >");
            } else {
                message('authorize/login', LANG["ACCOUNT_ACTIVATED"]);
            }
        }
    }
    /**
     * Handle full user registration (normal + invite)
     * Returns ['error' => '...'] on failure
     * On success it calls message() and exits (same behaviour as before)
     */
    public static function register(array $input): array
    {
        // CSRF
        /*
        if (function_exists('csrf_verify') && !csrf_verify($input['csrf_token'] ?? '')) {
            return ['error' => 'Invalid CSRF token'];
        }
        */

        // Captcha
        captcha_response($input['g-recaptcha-response'] ?? '');

        // Collect & sanitise
        $data = [
            'passagain' => Input1('passagain'),
            'invite'    => Input1('invite', 'int'),
            'username'  => Input1('wantusername'),
            'email'     => Input1('email'),
            'password'  => Input1('wantpassword'),
            'country'   => Input1('country', 'int'),
            'gender'    => Input1('gender'),
            'client'    => Input1('client'),
            'age'       => Input1('age', 'int'),
            'secret'    => Input1('secret'),
        ];

        // ---------- Invite check ----------
        $invite_row = 0;
        if (!empty($data['invite']) && strlen($data['secret']) === 20) {
            $row = DB::run(
                "SELECT id FROM users WHERE id = ? AND secret = ?",
                [$data['invite'], $data['secret']]
            )->fetch(PDO::FETCH_ASSOC);

            $invite_row = (int) ($row['id'] ?? 0);
        }

        // ---------- Validation ----------
        if (empty($data['password']) || (empty($data['email']) && !$invite_row) || empty($data['username'])) {
            return ['error' => LANG['DONT_LEAVE_ANY_FIELD_BLANK']];
        }
        if (strlen($data['username']) > 25) {
            return ['error' => sprintf(LANG['USERNAME_TOO_LONG'], 25)];
        }
        if ($data['password'] !== $data['passagain']) {
            return ['error' => LANG['PASSWORDS_NOT_MATCH']];
        }
        if (strlen($data['password']) < 6) {
            return ['error' => sprintf(LANG['PASS_TOO_SHORT_2'], 6)];
        }
        if (strlen($data['password']) > 72) {
            return ['error' => sprintf(LANG['PASS_TOO_LONG_2'], 72)];
        }
        if ($data['password'] === $data['username']) {
            return ['error' => LANG['PASS_CANT_MATCH_USERNAME']];
        }
        if (!preg_match('/^[a-z0-9 .\-]+$/i', $data['username'])) {
            return ['error' => LANG['INVALID_NAME']];
        }
        if (!$invite_row && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return ['error' => LANG['INVALID_EMAIL']];
        }

        // ---------- Extra checks (non-invite) ----------
        $numsitemembers = 0;

        if (!$invite_row) {
            $numsitemembers = (int) DB::run("SELECT COUNT(id) FROM users")->fetchColumn();

            if ($numsitemembers >= CONFIG['MAXUSERS']) {
                $msg = LANG['SITE_FULL_LIMIT_MSG'] . number_format(CONFIG['MAXUSERS']) . ' '
                     . LANG['SITE_FULL_LIMIT_REACHED_MSG'] . ' ' . number_format($numsitemembers) . ' members';
                return ['error' => $msg];
            }

            $banMsg = BansLogic::checkEmail($data['email']);
            if ($banMsg) {
                return ['error' => $banMsg];
            }
        }

        // Username taken?
        $count = DB::run(
            "SELECT id FROM users WHERE username = ?",
            [$data['username']]
        )->fetchColumn();

        if ($count) {
            return ['error' => sprintf(LANG['USERNAME_INUSE_S'], $data['username'])];
        }

        // ---------- Generate secret + passkey + hash ----------
        $data['secret']   = mksecret();
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $rand = array_sum(explode(' ', microtime()));
        $data['passkey'] = md5($data['username'] . $rand . $data['secret'] . ($rand * mt_rand()));

        // ---------- Invite path ----------
        if ($invite_row) {
            DB::run(
                "UPDATE users SET
                    username = ?, password = ?, class = ?, secret = ?,
                    status = ?, added = ?, passkey = ?
                 WHERE id = ?",
                [
                    $data['username'],
                    $data['password'],
                    1,
                    $data['secret'],
                    'confirmed',
                    CONFIG['TT_DATE'],
                    $data['passkey'],
                    $invite_row
                ]
            );

            self::sendWelcomePM($invite_row);
            self::postShout($data['username']);

            message('authorize/login', LANG['ACCOUNT_ACTIVATED']);
        }

        // ---------- Normal signup path ----------
        $status = (CONFIG['CONFIRMEMAIL'] || CONFIG['ACONFIRM']) ? 'pending' : 'confirmed';
        $class  = ($numsitemembers === 0) ? 7 : 1;

        DB::run(
            "INSERT INTO users
                (username, password, secret, email, status, added, last_login, last_access,
                 age, country, gender, client, stylesheet, language, class, ip, passkey)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['username'],
                $data['password'],
                $data['secret'],
                $data['email'],
                $status,
                CONFIG['TT_DATE'],
                CONFIG['TT_DATE'],
                CONFIG['TT_DATE'],
                $data['age'],
                $data['country'],
                $data['gender'],
                $data['client'],
                CONFIG['DEFAULTTHEME'],
                CONFIG['DEFAULTLANG'],
                $class,
                Ip::getIP(),
                $data['passkey']
            ]
        );

        $id = (int) DB::lastInsertId();

        self::postShout($data['username']);
        self::sendWelcomePM($id);

        // Confirmation email
        if (CONFIG['ACONFIRM']) {
            $body = LANG['YOUR_ACCOUNT_AT'] . ' ' . CONFIG['SITENAME'] . ' '
                  . LANG['HAS_BEEN_CREATED_YOU_WILL_HAVE_TO_WAIT'] . "\n\n"
                  . CONFIG['SITENAME'] . ' ' . LANG['ADMIN'];
        } else {
            $body = LANG['YOUR_ACCOUNT_AT'] . ' ' . CONFIG['SITENAME'] . ' '
                  . LANG['HAS_BEEN_APPROVED_EMAIL'] . "\n\n"
                  . URLROOT . "/profile/confirmemail/$id/{$data['secret']}\n\n"
                  . LANG['HAS_BEEN_APPROVED_EMAIL_AFTER'] . "\n\n"
                  . LANG['HAS_BEEN_APPROVED_EMAIL_DELETED'] . "\n\n"
                  . URLROOT . ' ' . LANG['ADMIN'];
        }

        if (CONFIG['CONFIRMEMAIL'] || CONFIG['ACONFIRM']) {
            $mail = new TTMail();
            $mail->Send(
                $data['email'],
                'Your ' . CONFIG['SITENAME'] . ' User Account',
                $body,
                '',
                '-f' . CONFIG['SITEEMAIL']
            );

            message(
                'authorize/login',
                LANG['A_CONFIRMATION_EMAIL_HAS_BEEN_SENT'] . ' (' . htmlspecialchars($data['email']) . '). '
                . LANG['ACCOUNT_CONFIRM_SENT_TO_ADDY_REST'] . ' <br>'
            );
        }

        message('authorize/login', LANG['ACCOUNT_ACTIVATED']);
    }

    // ------------------------------------------------------------------
    // Small helpers (keep the main method clean)
    // ------------------------------------------------------------------

    private static function sendWelcomePM(int $receiverId): void
    {
        DB::run(
            "INSERT INTO messages (sender, receiver, added, subject, msg, unread, location)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                0,
                $receiverId,
                CONFIG['TT_DATE'],
                'Welcome',
                CONFIG['WELCOMEPM_MSG'],
                'yes',
                'in'
            ]
        );
    }

    private static function postShout(string $username): void
    {
        DB::run(
            "INSERT INTO shoutbox (userid, date, user, message) VALUES (?, ?, ?, ?)",
            [0, CONFIG['TT_DATE'], 'System', 'new user: ' . $username . ' has joined.']
        );
    }

}