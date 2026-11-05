<?php
declare(strict_types=1);

namespace Bootstrap\Libs;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Ip;
use PDO;

/**
 * Authentication helper.
 *
 * Responsibilities:
 *  - Know who the current visitor is (member or guest)
 *  - Load member from PHP session or "remember me" cookie
 *  - Log in / log out
 *  - Optional site-closed gate for non-staff
 *
 * Typical usage (once per request, from init.php):
 *   Auth::user();
 *
 * Then anywhere:
 *   Auth::check()     // true if logged in
 *   Auth::id()        // user id or 0
 *   Auth::guest()     // opposite of check()
 *   Auth::isStaff()   // moderator+ 
 *   Auth::user()      // full user object
 */
class Auth
{
    /** Cached user object for this request (member or guest). */
    private static ?object $user = null;

    /** Whether load() has already run this request. */
    private static bool $checked = false;

    // ------------------------------------------------------------------
    // Public read API
    // ------------------------------------------------------------------

    /**
     * Return the current user object (loads once per request).
     *
     * @param bool $autoclean  Reserved for future cleanup jobs
     */
    public static function user(bool $autoclean = false): object
    {
        if (!self::$checked) {
            self::load($autoclean);
            self::$checked = true;
        }

        return self::$user;
    }

    /** Current user id, or 0 for guests. */
    public static function id(): int
    {
        return (int) (self::user()->id ?? 0);
    }

    /** True if a confirmed, enabled member is logged in. */
    public static function check(): bool
    {
        return (bool) (self::user()->loggedin ?? false);
    }

    /** True if visitor is not logged in. */
    public static function guest(): bool
    {
        return !self::check();
    }

    /** True if member class is moderator or higher. */
    public static function isStaff(): bool
    {
        return (bool) (self::user()->isStaff ?? false);
    }

    // ------------------------------------------------------------------
    // Boot / load
    // ------------------------------------------------------------------

    /**
     * Decide: banned IP? session member? remember-me cookie? guest?
     */
    private static function load(bool $autoclean = false): void
    {
        self::ipBanned();

        if (!empty($_SESSION['id']) && !empty($_SESSION['loggedin'])) {
            self::loadFromSession();
        } else {
            self::attemptRememberMeLogin();
        }

        // Site offline message for non-staff
        self::isClosed(self::$user->class ?? 0);

        if ($autoclean) {
            // Future: Cleanup::autoClean();
        }
    }

    /**
     * Re-load the member row from the database using session id.
     * If the account is gone/disabled, force guest.
     */
    private static function loadFromSession(): void
    {
        $row = DB::run(
            "SELECT u.*, g.*
             FROM users u
             LEFT JOIN `groups` g ON u.class = g.group_id
             WHERE u.id = ?
               AND u.enabled = 'yes'
               AND u.status = 'confirmed'",
            [$_SESSION['id']]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            self::destroyCookies();
            self::setGuest();
            return;
        }

        self::hydrate($row);
    }

    /**
     * Try long-lived "remember" cookie: id:hmac
     * HMAC is hash_hmac(sha256, id|passkey, CONFIG['SITE_SECRET']).
     */
    private static function attemptRememberMeLogin(): void
    {
        $cookie = $_COOKIE['remember'] ?? '';

        if ($cookie === '' || !str_contains($cookie, ':')) {
            self::setGuest();
            return;
        }

        [$id, $hmac] = explode(':', $cookie, 2);
        $id = (int) $id;

        // Basic shape checks
        if ($id < 1 || strlen($hmac) !== 64) {
            self::setGuest();
            return;
        }

        // Secret must exist in config (do not rely on a separate define)
        if (empty(CONFIG['SITE_SECRET'])) {
            self::setGuest();
            return;
        }

        $row = DB::run(
            "SELECT u.*, g.*
             FROM users u
             LEFT JOIN `groups` g ON u.class = g.group_id
             WHERE u.id = ?
               AND u.enabled = 'yes'
               AND u.status = 'confirmed'",
            [$id]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['passkey'])) {
            self::destroyCookies();
            self::setGuest();
            return;
        }

        $expected = hash_hmac(
            'sha256',
            $id . '|' . $row['passkey'],
            CONFIG['SITE_SECRET']
        );

        if (!hash_equals($expected, $hmac)) {
            self::destroyCookies();
            self::setGuest();
            return;
        }

        self::hydrate($row);
        self::updateLastAccess((int) $row['id']);
    }

    /**
     * Put safe user fields into session + static cache.
     *
     * Important: we MERGE into $_SESSION so we do not wipe
     * keys such as _csrf_token or _flash.
     */
    private static function hydrate(array $row): void
    {
        // Resolve permission role
        $role = ((int) ($row['role_id'] ?? 0) === 1)
            ? (int) ($row['group_role_id'] ?? 0)
            : (int) ($row['role_id'] ?? 0);

        $perm = DB::run(
            'SELECT * FROM permissions WHERE permission_id = ?',
            [$role]
        )->fetch(PDO::FETCH_ASSOC);

        if ($perm) {
            $row = array_merge($row, $perm);
        }

        // Never store secrets in the session
        unset($row['password'], $row['secret']);

        $row['loggedin'] = true;
        $row['isStaff']  = ((int) ($row['class'] ?? 0)) >= (int) (CONFIG['_MODERATOR'] ?? 0);

        // Merge — keep existing session keys (CSRF, flash, etc.)
        foreach ($row as $key => $value) {
            $_SESSION[$key] = $value;
        }
        $_SESSION['loggedin'] = true;
        $_SESSION['id']       = (int) $row['id'];

        self::$user = (object) $row;
    }

    /** Mark visitor as guest (id 0). */
    private static function setGuest(): void
    {
        $_SESSION['id']       = 0;
        $_SESSION['class']    = 0;
        $_SESSION['loggedin'] = false;

        self::$user = (object) [
            'id'       => 0,
            'class'    => 0,
            'loggedin' => false,
            'isStaff'  => false,
        ];
    }

    /**
     * Touch last_access / ip / page after remember-me login.
     * Creates a passkey if the account somehow has none.
     */
    private static function updateLastAccess(int $userId): void
    {
        $user = DB::run(
            'SELECT username, secret, passkey FROM users WHERE id = ?',
            [$userId]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return;
        }

        if (empty($user['passkey'])) {
            $rand    = microtime(true);
            $passkey = md5($user['username'] . $rand . $user['secret'] . ($rand * mt_rand()));

            DB::run(
                'UPDATE users SET passkey = ?, last_access = ?, ip = ?, page = ? WHERE id = ?',
                [$passkey, CONFIG['TT_DATE'], Ip::getIP(), $_SERVER['REQUEST_URI'] ?? '', $userId]
            );

            if (self::$user) {
                self::$user->passkey = $passkey;
            }
            return;
        }

        DB::run(
            'UPDATE users SET last_access = ?, ip = ?, page = ? WHERE id = ?',
            [CONFIG['TT_DATE'], Ip::getIP(), $_SERVER['REQUEST_URI'] ?? '', $userId]
        );
    }

    // ------------------------------------------------------------------
    // Public write API (login / logout / cookies)
    // ------------------------------------------------------------------

    /**
     * Call after a successful password check.
     *
     * @param array $userRow  Row from users table (must include id, etc.)
     * @param int   $days     0 = session only; >0 = also set remember cookie
     */
    public static function loginUser(array $userRow, int $days = 0): void
    {
        // Ensure passkey exists for future remember-me / tracker use
        if (empty($userRow['passkey'])) {
            $rand    = microtime(true);
            $passkey = md5($userRow['username'] . $rand . ($userRow['secret'] ?? '') . ($rand * mt_rand()));
            DB::run('UPDATE users SET passkey = ? WHERE id = ?', [$passkey, $userRow['id']]);
            $userRow['passkey'] = $passkey;
        }

        // Force a fresh hydrate on this request
        self::$checked = false;
        self::hydrate($userRow);
        self::$checked = true;

        if ($days > 0) {
            self::createRememberCookie((int) $userRow['id'], $userRow['passkey'], $days);
        }
    }

    /**
     * Long-lived cookie: "id:hmac" signed with CONFIG['SITE_SECRET'].
     */
    public static function createRememberCookie(int $userId, string $passkey, int $days = 30): void
    {
        if (empty(CONFIG['SITE_SECRET'])) {
            throw new \RuntimeException('CONFIG[SITE_SECRET] is not defined');
        }

        $hmac  = hash_hmac('sha256', $userId . '|' . $passkey, CONFIG['SITE_SECRET']);
        $value = $userId . ':' . $hmac;

        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        setcookie('remember', $value, [
            'expires'  => time() + ($days * 86400),
            'path'     => '/',
            'secure'   => $secure, // true on HTTPS live site
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Clear remember cookie + old legacy cookies + destroy PHP session.
     * Caller should session_start() again if they need flash afterwards.
     */
    public static function destroyCookies(): void
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        setcookie('remember', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // Legacy cookies from older code
        setcookie('id', '', time() - 3600, '/');
        setcookie('password', '', time() - 3600, '/');

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        self::$user    = null;
        self::$checked = false;
    }

    // ------------------------------------------------------------------
    // Guards
    // ------------------------------------------------------------------

    /** Block banned IPs (delegates to Ip class). */
    public static function ipBanned(): void
    {
        $ip = Ip::getIP();
        if ($ip !== '') {
            Ip::checkIpBan($ip);
        }
    }

    /**
     * If the site is offline, only staff may continue.
     * Everyone else gets a themed "closed" page and the script stops.
     */
    public static function isClosed(int|string $class = 0): void
    {
        if (!empty(CONFIG['SITE_ONLINE'])) {
            return;
        }

        $class = (int) $class;

        if ($class < (int) (CONFIG['_MODERATOR'] ?? 0)) {
            if (function_exists('theme_header') && function_exists('theme_footer')) {
                theme_header('Closed');
                echo '<div class="alert alert-warning"><center>'
                   . stripslashes((string) (CONFIG['OFFLINEMSG'] ?? 'Site is offline'))
                   . '</center></div>';
                theme_footer();
            } else {
                echo stripslashes((string) (CONFIG['OFFLINEMSG'] ?? 'Site is offline'));
            }
            exit;
        }

        // Staff still see a notice but can use the site
        echo '<center>' . stripslashes((string) (CONFIG['OFFLINEMSG'] ?? 'Site is offline')) . '</center>';
    }
}