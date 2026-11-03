<?php

namespace Bootstrap\Libs;

use Bootstrap\Libs\Ip;
use Bootstrap\Libs\DB;
use PDO;
use Bootstrap\Libs\Torrents\Cleanup;

class Auth
{
    private static $user = null;
    private static $checked = false;

    public static function user(bool $autoclean = false): object
    {
        if (!self::$checked) {
            self::load($autoclean);
            self::$checked = true;
        }
        return self::$user;
    }

    public static function id(): int
    {
        return (int) (self::user()->id ?? 0);
    }

    public static function check(): bool
    {
        return (bool) (self::user()->loggedin ?? false);
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function isStaff(): bool
    {
        return (bool) (self::user()->isStaff ?? false);
    }

    // ------------------------------------------------------------------

    private static function load(bool $autoclean = false): void
    {
        self::ipBanned();

        if (!empty($_SESSION['id']) && !empty($_SESSION['loggedin'])) {
            self::loadFromSession();
        } else {
            self::attemptRememberMeLogin();
        }

        self::isClosed(self::$user->class ?? 0);

        if ($autoclean) {
            //Cleanup::autoClean();
        }
    }

    private static function loadFromSession(): void
    {
        $row = DB::run(
            "SELECT u.*, g.*
             FROM users u
             LEFT JOIN `groups` g ON u.class = g.group_id
             WHERE u.id = ? AND u.enabled = 'yes' AND u.status = 'confirmed'",
            [$_SESSION['id']]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            self::destroyCookies();
            self::setGuest();
            return;
        }

        self::hydrate($row);
    }

    private static function attemptRememberMeLogin(): void
    {
        $cookie = $_COOKIE['remember'] ?? '';
        if (!$cookie || strpos($cookie, ':') === false) {
            self::setGuest();
            return;
        }

        [$id, $hmac] = explode(':', $cookie, 2);
        $id = (int) $id;

        if ($id < 1 || strlen($hmac) !== 64) {
            self::setGuest();
            return;
        }

        // Make sure SITE_SECRET exists
        if (!defined('SITE_SECRET') || CONFIG['SITE_SECRET'] === '') {
            self::setGuest();
            return;
        }

        $row = DB::run(
            "SELECT u.*, g.*
             FROM users u
             LEFT JOIN `groups` g ON u.class = g.group_id
             WHERE u.id = ? AND u.enabled = 'yes' AND u.status = 'confirmed'",
            [$id]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['passkey'])) {
            self::destroyCookies();
            self::setGuest();
            return;
        }

        $expected = hash_hmac('sha256', $id . '|' . $row['passkey'], CONFIG['SITE_SECRET']);

        if (!hash_equals($expected, $hmac)) {
            // Uncomment the next line temporarily if you want to see why it fails
            // error_log("Remember cookie HMAC mismatch for user $id");
            self::destroyCookies();
            self::setGuest();
            return;
        }

        self::hydrate($row);
        self::updateLastAccess((int)$row['id']);
    }

    private static function hydrate(array $row): void
    {
        $role = ($row['role_id'] ?? 0) == 1
            ? ($row['group_role_id'] ?? 0)
            : ($row['role_id'] ?? 0);

        $perm = DB::run(
            "SELECT * FROM permissions WHERE permission_id = ?",
            [$role]
        )->fetch(PDO::FETCH_ASSOC);

        if ($perm) {
            $row = array_merge($row, $perm);
        }

        unset($row['password'], $row['secret']);

        $row['loggedin'] = true;
        $row['isStaff']  = ($row['class'] ?? 0) >= CONFIG['_MODERATOR'];

        $_SESSION = $row;
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = (int)$row['id'];

        self::$user = (object) $row;
    }

    private static function setGuest(): void
    {
        $_SESSION['id'] = 0;
        $_SESSION['class'] = 0;
        $_SESSION['loggedin'] = false;

        self::$user = (object) [
            'id'       => 0,
            'class'    => 0,
            'loggedin' => false,
            'isStaff'  => false,
        ];
    }

    private static function updateLastAccess(int $userId): void
    {
        $user = DB::run(
            "SELECT username, secret, passkey FROM users WHERE id = ?",
            [$userId]
        )->fetch(PDO::FETCH_ASSOC);

        if (empty($user['passkey'])) {
            $rand = array_sum(explode(' ', microtime()));
            $passkey = md5($user['username'] . $rand . $user['secret'] . ($rand * mt_rand()));
            DB::run(
                "UPDATE users SET passkey = ?, last_access = ?, ip = ?, page = ? WHERE id = ?",
                [$passkey, CONFIG['TT_DATE'], Ip::getIP(), $_SERVER['REQUEST_URI'] ?? '', $userId]
            );
            if (self::$user) {
                self::$user->passkey = $passkey;
            }
        } else {
            DB::run(
                "UPDATE users SET last_access = ?, ip = ?, page = ? WHERE id = ?",
                [CONFIG['TT_DATE'], Ip::getIP(), $_SERVER['REQUEST_URI'] ?? '', $userId]
            );
        }
    }

    // ------------------------------------------------------------------
    // Public API
    // ------------------------------------------------------------------

/**
 * Call after successful password login.
 * $days = 0  → only short PHP session
 * $days > 0  → also set long-lived cookie
 */
public static function loginUser(array $userRow, int $days = 0): void
{
    // Ensure passkey exists
    if (empty($userRow['passkey'])) {
        $rand = array_sum(explode(' ', microtime()));
        $passkey = md5($userRow['username'] . $rand . $userRow['secret'] . ($rand * mt_rand()));
        DB::run("UPDATE users SET passkey = ? WHERE id = ?", [$passkey, $userRow['id']]);
        $userRow['passkey'] = $passkey;
    }

    // Always set the current session
    self::$checked = false;
    self::hydrate($userRow);

    // Only create the long cookie when requested
    if ($days > 0) {
        self::createRememberCookie((int)$userRow['id'], $userRow['passkey'], $days);
    }
}

    public static function createRememberCookie(int $userId, string $passkey, int $days = 30): void
    {
        if (!defined('SITE_SECRET') || CONFIG['SITE_SECRET'] === '') {
            throw new \RuntimeException('SITE_SECRET is not defined');
        }

        $hmac  = hash_hmac('sha256', $userId . '|' . $passkey, CONFIG['SITE_SECRET']);
        $value = $userId . ':' . $hmac;

        setcookie('remember', $value, [
            'expires'  => time() + ($days * 86400),
            'path'     => '/',
            'secure'   => false,          // ← force false while testing on HTTP
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function destroyCookies(): void
    {
        setcookie('remember', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        setcookie('id', '', time() - 3600, '/');
        setcookie('password', '', time() - 3600, '/');

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        self::$user = null;
        self::$checked = false;
    }

    public static function ipBanned(): void
    {
        $ip = Ip::getIP();
        if ($ip !== '') {
            Ip::checkIpBan($ip);
        }
    }

    public static function isClosed($class = 0): void
    {
    if (!CONFIG['SITE_ONLINE']) {
        if ($class < CONFIG['_MODERATOR']) {
            if (function_exists('theme_header') && function_exists('theme_footer')) {
                theme_header('Closed');
                echo '<div class="alert alert-warning"><center>' . stripslashes(CONFIG['OFFLINEMSG']) . '</center></div>';
                theme_footer();
            } else {
                echo stripslashes(CONFIG['OFFLINEMSG']);
            }
            exit;
        }
        echo '<center>' . stripslashes(CONFIG['OFFLINEMSG']) . '</center>';
    }
    }
}