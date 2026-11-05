<?php
declare(strict_types=1);

/**
 * =============================================================================
 * LEGACY FUNCTIONS
 * =============================================================================
 *
 * Older M-BiTsy helpers still used by browse, forums, comments, BBCode, etc.
 *
 * DO NOT put new core helpers here.
 * New code belongs in:
 *   - helper_functions.php  (url, redirect, flash, csrf, e, post, …)
 *   - style_functions.php   (theme, forms, alerts, blocks)
 *   - language_functions.php
 *   - router_functions.php
 *
 * This file may still use:
 *   URLROOT, CONFIG, LANG / __(), $_SESSION, DB
 *
 * Prefer for new pages:
 *   url(), redirect(), __('KEY'), Auth::…, e()
 *
 * =============================================================================
 */

use Bootstrap\Libs\DB;
use PDO;

// Optional: only if that class exists in your tree
// use Bootstrap\Models\TmdbLogic;

// =============================================================================
// 1. SECRETS / RANDOM
// =============================================================================

/**
 * Create a random alphanumeric string (invite secrets, user secrets, etc.).
 *
 * @param int $len  Length of the string (default 20)
 */
function mksecret(int $len = 20): string
{
    $chars = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
    $max   = count($chars) - 1;
    $str   = '';

    for ($i = 0; $i < $len; $i++) {
        $str .= $chars[mt_rand(0, $max)];
    }

    return $str;
}

// =============================================================================
// 2. EMOJI MAP (BBCode / shoutbox picker)
// =============================================================================

/**
 * Emoji character => short name (used by textbbcode / shoutbbcode).
 * Global so older code can use: global $emoji;
 */
$emoji = [
    '😊' => 'smile',
    '😞' => 'sad',
    '😉' => 'wink',
    '🤩' => 'grinstar',
    '😐' => 'plain',
    '😕' => 'confused',
    '🤓' => 'glasses',
    '😬' => 'eek',
    '😎' => 'cool',
    '😢' => 'crying',
    '😘' => 'kiss',
    '😇' => 'angel',
    '😈' => 'devilish',
    '🤣' => 'roflmao',
    '😴' => 'sleep',
    '🥰' => 'love',
    '💩' => 'poop',
    '😲' => 'shocked',
    '😡' => 'mad',
    '🙄' => 'rolleyes',
    '🤢' => 'sick',
    '😂' => 'crylaugh',
    '🔥' => 'fire',
    '🦄' => 'unicorn',
    '😺' => 'cat',
    '😀' => 'grin',
    '👍' => 'thumbsup',
    '👎' => 'thumbsdown',
    '👌' => 'ok',
    '🙈' => 'monkey',
    '🆘' => 'help',
    '⚠'  => 'warn',
    '💣' => 'bomb',
    '💡' => 'idea',
];

// =============================================================================
// 3. TIMEZONES
// =============================================================================

/**
 * Offset (minutes) => [label, PHP timezone id]
 * Used by profile timezone dropdown and user_timezone_date().
 */
$tzs = [
    '-720' => ['(GMT - 12:00 hours) Enitwetok, Kwajalien', 'Pacific/Kwajalein'],
    '-660' => ['(GMT - 11:00 hours) Midway Island, Samoa', 'Pacific/Samoa'],
    '-600' => ['(GMT - 10:00 hours) Hawaii', 'Etc/GMT+10'],
    '-540' => ['(GMT - 9:00 hours) Alaska', 'America/Anchorage'],
    '-480' => ['(GMT - 8:00 hours) Pacific Time (US & Canada)', 'America/Los_Angeles'],
    '-420' => ['(GMT - 7:00 hours) Mountain Time (US & Canada)', 'America/Denver'],
    '-360' => ['(GMT - 6:00 hours) Central Time (US & Canada), Mexico City', 'America/Chicago'],
    '-300' => ['(GMT - 5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito', 'America/New_York'],
    '-240' => ['(GMT - 4:00 hours) Atlantic Time (Canada), Caracas, La Paz', 'America/Guadeloupe'],
    '-210' => ['(GMT - 3:30 hours) Newfoundland', 'America/St_Johns'],
    '-180' => ['(GMT - 3:00 hours) Brazil, Buenos Aires, Georgetown, Falkland Isl.', 'Etc/GMT+3'],
    '-120' => ['(GMT - 2:00 hours) Mid-Atlantic, Ascention Isl., St Helena', 'Atlantic/St_Helena'],
    '-60'  => ['(GMT - 1:00 hour) Azores, Cape Verde Islands', 'Atlantic/Azores'],
    '0'    => ['(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia', 'Europe/London'],
    '60'   => ['(GMT + 1:00 hour) Amsterdam, Berlin, Copenhagen, Madrid, Paris, Rome', 'Europe/Rome'],
    '120'  => ['(GMT + 2:00 hours) Kaliningrad, South Africa, Warsaw', 'Europe/Kaliningrad'],
    '180'  => ['(GMT + 3:00 hours) Baghdad, Riyadh, Moscow, Nairobi', 'Africa/Nairobi'],
    '210'  => ['(GMT + 3:30 hours) Tehran', 'Asia/Tehran'],
    '240'  => ['(GMT + 4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi', 'Asia/Tbilisi'],
    '270'  => ['(GMT + 4:30 hours) Kabul', 'Asia/Kabul'],
    '300'  => ['(GMT + 5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent', 'Asia/Tashkent'],
    '330'  => ['(GMT + 5:30 hours) Bombay, Calcutta, Madras, New Delhi', 'Asia/Calcutta'],
    '360'  => ['(GMT + 6:00 hours) Almaty, Dhaka, Khulna', 'Asia/Dhaka'],
    '420'  => ['(GMT + 7:00 hours) Bangkok, Hanoi, Jakarta', 'Asia/Bangkok'],
    '480'  => ['(GMT + 8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei', 'Asia/Taipei'],
    '540'  => ['(GMT + 9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk', 'Asia/Tokyo'],
    '570'  => ['(GMT + 9:30 hours) Adelaide, Darwin', 'Australia/Adelaide'],
    '600'  => ['(GMT + 10:00 hours) Melbourne, Papua New Guinea, Sydney, Vladivostok', 'Australia/Sydney'],
    '660'  => ['(GMT + 11:00 hours) Magadan, New Caledonia, Solomon Islands', 'Asia/Magadan'],
    '720'  => ['(GMT + 12:00 hours) Auckland, Wellington, Fiji, Marshall Island', 'Pacific/Auckland'],
];

/**
 * Convert a DB timestamp to the visitor’s timezone (session tzoffset in minutes).
 *
 * @param mixed $timestamp  Unix int or datetime string
 * @param bool  $return_int If true, return adjusted unix time
 */
function user_timezone_date($timestamp, bool $return_int = false)
{
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime((string) $timestamp);
    }

    $offsetMin = (int) ($_SESSION['tzoffset'] ?? 0);
    $timestamp = (int) $timestamp + ($offsetMin * 60);

    if ($return_int) {
        return $timestamp;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

/** HTML <option> list for timezone dropdown. */
function timeZoneDropDown($tzoffset): string
{
    global $tzs;

    $tz = '';
    ksort($tzs, SORT_NUMERIC);

    foreach ($tzs as $key => $val) {
        $sel = ((string) $tzoffset === (string) $key) ? " selected='selected'" : '';
        $tz .= '<option value="' . e((string) $key) . '"' . $sel . '>' . $val[0] . "</option>\n";
    }

    return $tz;
}

/**
 * Add/subtract from a date or unix time using PHP date_modify syntax.
 * Example: modify('date', '2024-01-01', '+7 days')
 */
function modify(string $type, $target, string $amount): string
{
    if ($type === 'date') {
        $date = date_create((string) $target);
    } else {
        $date = date_create();
        date_timestamp_set($date, (int) $target);
    }

    date_modify($date, $amount);
    return date_format($date, 'Y-m-d H:i:s');
}

/** Human “time ago” string from a unix timestamp. */
function get_elapsed_time(int $ts): string
{
    $mins  = (int) floor((CONFIG['TT_TIME'] - $ts) / 60);
    $hours = (int) floor($mins / 60);
    $mins -= $hours * 60;
    $days  = (int) floor($hours / 24);
    $hours -= $days * 24;
    $weeks = (int) floor($days / 7);
    $days -= $weeks * 7;

    if ($weeks > 0) {
        return $weeks . ' wk' . ($weeks > 1 ? 's' : '');
    }
    if ($days > 0) {
        return $days . ' day' . ($days > 1 ? 's' : '');
    }
    if ($hours > 0) {
        return $hours . ' hr' . ($hours > 1 ? 's' : '');
    }
    if ($mins > 0) {
        return $mins . ' min' . ($mins > 1 ? 's' : '');
    }

    return '< 1 min';
}

// =============================================================================
// 4. SIZES / STRINGS / RATIO COLOURS
// =============================================================================

/** Format bytes as B / kB / MB / GB … */
function mksize($s, int $precision = 2): string
{
    $suf = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $s   = (float) $s;

    for ($i = 1, $x = 0; $i <= count($suf); $i++, $x++) {
        if ($s < pow(1024, $i) || $i === count($suf)) {
            return number_format($s / pow(1024, $x), $precision) . ' ' . $suf[$x];
        }
    }

    return '0 B';
}

/** Truncate a string and add "..." if longer than $car characters. */
function CutName(string $vTxt, int $car): string
{
    if (strlen($vTxt) > $car) {
        return substr($vTxt, 0, $car) . '...';
    }
    return $vTxt;
}

/** Parse strings like "1.5 GB" into bytes. */
function strtobytes(string $str): float
{
    $str = trim($str);
    if (!preg_match('!^([\d\.]+)\s*(\w\w)?$!', $str, $matches)) {
        return 0;
    }

    $num    = (float) $matches[1];
    $suffix = strtolower($matches[2] ?? 'b');

    return match ($suffix) {
        'tb' => $num * 1099511627776,
        'gb' => $num * 1073741824,
        'mb' => $num * 1048576,
        'kb' => $num * 1024,
        default => $num,
    };
}

/** Colour HTML for a ratio number. */
function get_ratio_color($ratio): string
{
    $r = (string) $ratio;
    if ($r === '---') {
        return "<font color='FFFFFF'>{$r}</font>";
    }
    $n = (float) $ratio;
    if ($n < 0.5) {
        return "<font color='#fc0313'>{$r}</font>";
    }
    if ($n < 1) {
        return "<font color='#fc6203'>{$r}</font>";
    }
    if ($n < 1.5) {
        return "<font color='#ebc334'>{$r}</font>";
    }
    if ($n < 2) {
        return "<font color='#1a7d09'>{$r}</font>";
    }
    return "<font color='#0d6efd'>{$r}</font>";
}

/** Escape for HTML (legacy name; prefer e() in new code). */
function encodehtml(string $s, bool $linebreaks = true): string
{
    $s = str_replace(['&', '<'], ['&amp;', '&lt;'], $s);
    if ($linebreaks) {
        $s = nl2br($s);
    }
    return $s;
}

// =============================================================================
// 5. INPUT / CAPTCHA (legacy – prefer post() + csrf_verify in new pages)
// =============================================================================
/** Comma-separated list of integer IDs from a POST array field. */
function multiIds(string $value): string
{
    $ids = $_POST[$value] ?? null;
    if (!$ids || !is_array($ids)) {
        redirect('home', __('MISSING_FORM_DATA'), 'warning');
    }

    $int_array = array_map('intval', $ids);
    return implode(', ', $int_array);
}

/** Google reCAPTCHA v2 check when CONFIG['CAPTCHA_ON'] is true. */
function captcha_response($captcha): void
{
    if (empty(CONFIG['CAPTCHA_ON'])) {
        return;
    }

    if (!$captcha) {
        redirect('authorize/login', __('CAPTCHA'), 'warning');
    }

    $verifyResponse = @file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret='
        . urlencode((string) CONFIG['CAPTCHA_SECRET'])
        . '&response=' . urlencode((string) $captcha)
    );

    $responseData = json_decode((string) $verifyResponse);
    if (empty($responseData->success)) {
        redirect('authorize/login', __('CAPTCHA'), 'warning');
    }
}

/** HTML widget for reCAPTCHA (empty string if captcha off). */
function captcha_html(): string
{
    if (empty(CONFIG['CAPTCHA_ON'])) {
        return '';
    }

    return "<center><div class='g-recaptcha' data-theme='light' data-sitekey='"
         . e((string) CONFIG['CAPTCHA_KEY'])
         . "'></div></center>";
}

// =============================================================================
// 6. USERS / DISPLAY NAMES
// =============================================================================

/**
 * Coloured username link (class colour, donated star, warned, disabled).
 */
function coloredname($id): string
{
    $row = DB::run(
        "SELECT u.id, u.username, u.donated, u.warned, u.enabled, g.Color
         FROM users u
         INNER JOIN `groups` g ON g.group_id = u.class
         WHERE u.id = ?",
        [(int) $id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return __('SYSTEM');
    }

    $name = e($row['username']);
    $star = ((float) $row['donated'] > 0)
        ? " <i class='fa fa-star' style='color:orange' title='" . e(__('DONATED')) . "'></i>"
        : '';
    $warn = (($row['warned'] ?? '') === 'yes')
        ? " <img src='" . e(url('page/theme/default/images/misc/warn.webp')) . "' alt='warn'>"
        : '';
    $disabled = (($row['enabled'] ?? '') === 'no')
        ? " <img src='" . e(url('page/theme/default/images/misc/disabled.webp')) . "' alt='disabled'>"
        : '';

    $color = e($row['Color'] ?? 'ffffff');

    return "<a href='" . e(url('profile/view/' . $row['id'])) . "'><font color='#{$color}'>{$name}{$star}{$warn}{$disabled}</font></a>";
}

/** Group level name for a class id. */
function get_user_class_name($i): string
{
    $row = DB::run('SELECT level FROM `groups` WHERE group_id = ?', [(int) $i])
        ->fetch(PDO::FETCH_ASSOC);
    return (string) ($row['level'] ?? '');
}

// =============================================================================
// 7. BBCODE (parse stored text for display)
// =============================================================================

/**
 * Convert BBCode + auto-links into safe HTML for display.
 * Always run user content through this (or htmlspecialchars) before echo.
 */
function format_comment(string $text): string
{
    $s = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Auto-link plain URLs
    $s = preg_replace(
        "/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i",
        "\\1<a href='\\2'>\\2</a>",
        $s
    ) ?? $s;

    $s = preg_replace("/\[\*\]/", '<li>', $s) ?? $s;
    $s = preg_replace("#\[list\](.+)\[/list\]#isU", "<ul style='margin-left:20px'>$1</ul>", $s) ?? $s;
    $s = preg_replace("#\[center\](.+)\[/center\]#isU", '<center>$1</center>', $s) ?? $s;
    $s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", '<b>$1</b>', $s) ?? $s;
    $s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", '<i>$1</i>', $s) ?? $s;
    $s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", '<u>$1</u>', $s) ?? $s;
    $s = preg_replace("#\[s\](.+)\[/s\]#isU", "<span style='text-decoration:line-through'>$1</span>", $s) ?? $s;
    $s = preg_replace("#\[url\]((?:ftp|https?)://.*?)\[/url\]#i", "<a href='$1'>$1</a>", $s) ?? $s;
    $s = preg_replace("#\[url=((?:ftp|https?)://.*?)\](.*?)\[/url\]#i", "<a href='$1'>$2</a>", $s) ?? $s;
    $s = preg_replace(
        "#\[img\]((?:ftp|https?)://[a-z0-9._/-]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\[/img\]#isU",
        "<img src='$1' class='scaleA' alt='' />",
        $s
    ) ?? $s;
    $s = preg_replace("#\[code\](.+)\[/code\]#isU", "<b>Code:</b><pre><code>$1</code></pre>", $s) ?? $s;

    while (preg_match("#\[quote\](.+)\[/quote\]#isU", $s)) {
        $s = preg_replace(
            "#\[quote\](.+)\[/quote\]#isU",
            "<blockquote><strong>Quote:</strong></blockquote><div class='scaleA' style='padding:5px;border:1px dotted'>$1</div>",
            $s
        ) ?? $s;
    }
    while (preg_match("#\[quote=(.+)\](.+)\[/quote\]#isU", $s)) {
        $s = preg_replace(
            "#\[quote=(.+)\](.+)\[/quote\]#isU",
            "<blockquote><strong>$1:</strong></blockquote><div class='scaleA' style='padding:5px;border:1px dotted'>$2</div>",
            $s
        ) ?? $s;
    }

    $s = preg_replace("#\[color=([a-zA-Z]+)\](.+)\[/color\]#isU", "<font color='$1'>$2</font>", $s) ?? $s;
    $s = preg_replace("#\[color=\#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})\](.+)\[/color\]#isU", "<font color='#$1'>$2</font>", $s) ?? $s;
    $s = preg_replace("#\[size=([1-7])\](.+)\[/size\]#isU", "<font size='$1'>$2</font>", $s) ?? $s;
    $s = preg_replace("#\[font=([a-zA-Z].*?)\](.+)\[/font\]#isU", "<font face='$1'>$2</font>", $s) ?? $s;
    $s = preg_replace("#\[df\](.+)\[/df\]#isU", "<div class='marquee'>$1</div>", $s) ?? $s;
    $s = preg_replace("/\[align=([a-zA-Z]+)\](.+?)\[\/align\]/is", '<div style="text-align:$1">$2</div>', $s) ?? $s;
    $s = preg_replace("/\[hr\]/i", '<hr />', $s) ?? $s;

    $s = nl2br($s);
    $s = str_replace('  ', ' &nbsp;', $s);

    return $s;
}

// =============================================================================
// 8. TORRENT / TAG / HEALTH HELPERS
// =============================================================================

/** Health icon index 0–10 from seeder/leecher counts. */
function health(int $leechers, int $seeders): int
{
    if (($leechers === 0 && $seeders === 0) || ($leechers > 0 && $seeders === 0)) {
        return 0;
    }
    if ($seeders > $leechers) {
        return 10;
    }

    $ratio = $seeders / $leechers * 100;
    if ($ratio < 15) {
        return 1;
    }
    if ($ratio < 25) {
        return 2;
    }
    if ($ratio < 35) {
        return 3;
    }
    if ($ratio < 45) {
        return 4;
    }
    if ($ratio < 55) {
        return 5;
    }
    if ($ratio < 65) {
        return 6;
    }
    if ($ratio < 75) {
        return 7;
    }
    if ($ratio < 85) {
        return 8;
    }
    if ($ratio < 95) {
        return 9;
    }
    return 10;
}

/** HTML badges for torrent tags. */
function get_tags($id): string
{
    $tag = DB::run(
        'SELECT type1, type2, type3 FROM tagtorrent WHERE torrentid = ?',
        [(int) $id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$tag) {
        return '';
    }

    $html = '';
    foreach (['type1', 'type2', 'type3'] as $key) {
        if (!empty($tag[$key])) {
            $t = e($tag[$key]);
            $html .= " <a href='" . e(url('tag/' . $tag[$key])) . "'><span class='badge border ttbtn'>{$t}</span></a>";
        }
    }
    return $html;
}

// =============================================================================
// 9. NAV / PAGINATION (still used by older pages)
// =============================================================================

/** CSS class for active menu button (rough match on current URL). */
function activelink(string $page, string $message = ''): string
{
    $url = (string) ($_GET['url'] ?? '');
    if ($message !== '' && $message === $page) {
        return 'ttbtnactive';
    }
    return ($url === $page) ? 'ttbtnactive' : 'btn btn-sm ttbtn';
}

/**
 * Build pagination HTML + SQL LIMIT clause.
 *
 * @return array{0:string,1:string}  [html, "LIMIT x, y"]
 */
function pagination(int $rowsperpage, int $countresult, string $url): array
{
    $pagetotal = (int) max(1, ceil($countresult / max(1, $rowsperpage)));

    $currentpage = isset($_GET['page']) ? trim((string) $_GET['page']) : '0';
    if ($currentpage === 'last') {
        $page = $pagetotal;
    } elseif ((int) $currentpage > 0) {
        $page = (int) $currentpage;
    } else {
        $page = 0;
    }

    $offset = max(0, ($page * $rowsperpage) - $rowsperpage);
    $limit  = "LIMIT {$offset}, {$rowsperpage}";

    $adjacents = 3;
    if ($pagetotal <= (1 + ($adjacents * 2))) {
        $start = 1;
        $end   = $pagetotal;
    } elseif (($page - $adjacents) > 1) {
        if (($page + $adjacents) < $pagetotal) {
            $start = $page - $adjacents;
            $end   = $page + $adjacents;
        } else {
            $start = $pagetotal - (1 + ($adjacents * 2));
            $end   = $pagetotal;
        }
    } else {
        $start = 1;
        $end   = 1 + ($adjacents * 2);
    }

    $link = '';
    if ($pagetotal > 1) {
        $link .= '<ul class="pagination pagination-sm justify-content-left">';
        $link .= "<li class='page-item " . ($page <= 1 ? 'tt_disabled' : '') . "'><a class='page-link' href='{$url}page=1'>&lt;&lt;</a></li>";
        $link .= "<li class='page-item " . ($page <= 1 ? 'tt_disabled' : '') . "'><a class='page-link' href='{$url}page=" . max(1, $page - 1) . "'>&lt;</a></li>";
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i === $page) ? 'active' : '';
            $link .= "<li class='page-item {$active}'><a class='page-link' href='{$url}page={$i}'>{$i}</a></li>";
        }
        $link .= "<li class='page-item " . ($page >= $pagetotal ? 'tt_disabled' : '') . "'><a class='page-link' href='{$url}page=" . min($pagetotal, $page + 1) . "'>&gt;</a></li>";
        $link .= "<li class='page-item " . ($page >= $pagetotal ? 'tt_disabled' : '') . "'><a class='page-link' href='{$url}page={$pagetotal}'>&gt;&gt;</a></li>";
        $link .= '</ul>';
    }

    return [$link, $limit];
}

// =============================================================================
// 10. LARGE UI FUNCTIONS (legacy feature pages)
// =============================================================================
//
// Used by: browse, torrent detail, forums, comments, shoutbox, stats, profile.
// New pages (login/signup/demos) do NOT need these.
// When you rewrite a feature, stop calling its function, then delete it.
// =============================================================================

/**
 * Full BBCode toolbar + emoji picker + textarea.
 * Used on: comments, forum posts, news, requests, etc.
 *
 * @param string $form     HTML form name/id (for SmileIT JS)
 * @param string $name     Textarea name/id
 * @param string $content  Existing text (edit mode)
 */
function textbbcode(string $form, string $name, string $content = ''): void
{
    // JS that inserts [b]…[/b] etc. into the textarea
    require 'page/theme/default/js/BBTag.js';
    ?>
    <div class="row justify-content-md-center">
    <div class="col-12 text-center">
    <?php
    // --- BBCode buttons ---
    print("<i class='p-1 fa fa-bold' style='height:20px;width:20px;' onclick=\"bbcomment('[b]', '[/b]')\" title='Bold'></i>");
    print("<i class='p-1 fa fa-italic' style='height:20px;width:20px;' onclick=\"bbcomment('[i]', '[/i]')\" title='Italic'></i>");
    print("<i class='p-1 fa fa-underline' style='height:20px;width:20px;' onclick=\"bbcomment('[u]', '[/u]')\" title='Underline'></i>");
    print("<i class='p-1 fa fa-strikethrough' style='height:20px;width:20px;' onclick=\"bbcomment('[s]', '[/s]')\" title='Strike'></i>");
    print("<i class='p-1 fa fa-list' style='height:20px;width:20px;' onclick=\"bbcomment('[list]', '[/list]')\" title='List'></i>");
    print("<i class='p-1 fa fa-quote-right' style='height:20px;width:20px;' onclick=\"bbcomment('[quote]', '[/quote]')\" title='Quote'></i>");
    print("<i class='p-1 fa fa-code' style='height:20px;width:20px;' onclick=\"bbcomment('[code]', '[/code]')\" title='Code'></i>");
    print("<i class='p-1 fa fa-link' style='height:20px;width:20px;' onclick=\"bbcomment('[url]', '[/url]')\" title='Link'></i>");
    print("<i class='p-1 fa fa-bolt' style='height:20px;width:20px;' onclick=\"bbcomment('[df]', '[/df]')\" title='Scroller'></i>");
    print("<i class='p-1 fa fa-arrow-left' style='height:20px;width:20px;' onclick=\"bbcomment('[align=left]','[/align]')\" title='Align Left'></i>");
    print("<i class='p-1 fa fa-align-center' style='height:20px;width:20px;' onclick=\"bbcomment('[align=center]','[/align]')\" title='Align Center'></i>");
    print("<i class='p-1 fa fa-arrow-right' style='height:20px;width:20px;' onclick=\"bbcomment('[align=right]','[/align]')\" title='Align Right'></i>");
    print("<i class='p-1 fa fa-picture-o' style='height:20px;width:20px;' onclick=\"bbcomment('[img]', '[/img]')\" title='Image'></i>");
    print("<i class='p-1 fa fa-video-camera' style='height:20px;width:20px;' onclick=\"bbcomment('[video]', '[/video]')\" title='Video'></i>");
    print("<i class='p-1 fa fa-ban' style='height:20px;width:20px;' onclick=\"bbcomment('[hide]','[/hide]')\" title='Hide'></i>");
    print("<a href='https://imgur.com/upload' target='_blank'><img src='" . e(url('page/theme/default/images/misc/bbcode_imgur.webp')) . "' class='p-1' style='margin-bottom:4px;height:20px;width:20px;' title='Upload Image' alt='' /></a>");
    print("<a href='https://www.youtube.com' target='_blank'><img src='" . e(url('page/theme/default/images/misc/bbcode_youtube.webp')) . "' class='p-1' style='margin-bottom:4px;height:20px;width:20px;' title='YouTube' alt='' /></a>");

    // Toggle emoji panel
    print("<a data-bs-toggle='collapse' data-bs-target='#collapseExample' aria-expanded='false'><img class='p-1' style='margin-bottom:4px' src='" . e(url('page/theme/default/images/misc/grin.webp')) . "' alt='' /></a>&nbsp;");

    // Colour dropdown
    print("<select name='color' onChange='bbcouleur(this.value);' title='Colour'>");
    print("<option value='0'>Colour</option>");
    $colours = [
        '#000000' => 'Black', '#686868' => 'Grey', '#708090' => 'Dark Grey', '#C0C0C0' => 'Light Grey',
        '#FFFFFF' => 'White', '#FFFFE0' => 'Beech', '#880000' => 'Dark Red', '#B82428' => 'Light Red',
        '#FF0000' => 'Red', '#FF1490' => 'Dark Pink', '#FF68B0' => 'Pink', '#FFC0C8' => 'Light Pink',
        '#FF4400' => 'Dark Orange', '#FF6448' => 'Redish Orange', '#FFA800' => 'Orange', '#FFD800' => 'Dark Yellow',
        '#cfbc38' => 'Yellow', '#FF00FF' => 'Light Purple', '#C01480' => 'Dark Purple', '#B854D8' => 'Dark Violet',
        '#D8A0D8' => 'Light Violet', '#000080' => 'Darkest Blue', '#0000FF' => 'Dark Blue', '#2090FF' => 'Blue',
        '#00BCFF' => 'Light Blue', '#B0E0E8' => 'Faint Blue', '#A02828' => 'Brown', '#F0A460' => 'Brown Creme',
        '#D0B488' => 'Light Brown', '#B8B868' => 'Brown/Green', '#008000' => 'Dark Green', '#30CC30' => 'Green',
        '#00FF80' => 'Light Green', '#98FC98' => 'Light Lime', '#98CC30' => 'Dark Lime', '#40E0D0' => 'Turquoise',
        '#20B4A8' => 'Aquarium',
    ];
    foreach ($colours as $hex => $label) {
        print("<option value='{$hex}' style='BACKGROUND-COLOR:{$hex}'>{$label}</option>");
    }
    print('</select>');

    // Font family
    print("<select name='font' onChange='bbfont(this.value);' title='Style'>");
    print("<option value='0'>Style</option>");
    $fonts = ['Arial', 'Comic Sans MS', 'Trebuchet MS', 'Courier New', 'Georgia', 'Impact',
        'Lucida Sans Unicode', 'Microsoft Sans Serif', 'Tahoma', 'Times New Roman', 'Verdana',
        'Palatino Linotype', 'Ravie', 'WESTERN', 'Amerika', 'Goudy Old Style', 'Papyrus', 'Brush Script MT'];
    foreach ($fonts as $f) {
        print("<option value='{$f}' style='font-family:{$f}'>{$f}</option>");
    }
    print('</select>');

    // Size 1–7
    print("<select name='size' onchange='bbsize(this.value);' title='Size'>");
    print("<option value='0'>Size</option>");
    for ($i = 1; $i <= 7; $i++) {
        print("<option value='{$i}'>{$i}x</option>");
    }
    print('</select>');
    ?>
    </div>
    </div>

    <div class="container">
    <div class="row justify-content-md-center">
    <div class="collapse" id="collapseExample">
    <div class="text-center">
    <?php
    global $emoji;
    foreach ($emoji as $code => $title) {
        echo '<a title="' . e($title) . '" onclick="SmileIT(\'' . $code . '\',\'' . e($form) . '\',\'' . e($name) . '\')">' . $code . '</a> ';
    }
    ?>
    </div>
    </div>
    </div>
    </div>

    <div class="row justify-content-md-center">
    <div class="col-10" style="width:100%;">
        <textarea class="form-control" id="<?= e($name) ?>" name="<?= e($name) ?>" rows="13"><?= e($content) ?></textarea>
    </div>
    </div>

    <div class="row justify-content-md-center">
    <div class="col-10">
        <center>
            <input type="reset" class="btn btn-sm ttbtn" value="Refresh" />
            <input type="button" class="btn btn-sm ttbtn" value="Preview" onClick="visualisation()" />
        </center><br>
        <div id="previsualisation"></div><br>
    </div>
    </div>
    <?php
}

/**
 * Compact BBCode bar + single-line input for the shoutbox.
 */
function shoutbbcode(string $form, string $name, string $content = ''): void
{
    require 'page/theme/default/js/BBTag.js';
    ?>
    <div class="container">
    <div class="row justify-content-md-center">
    <div class="col-12 d-flex justify-content-center">
    <?php
    print("<i class='p-1 fa fa-bold' style='height:20px;width:20px;' onclick=\"bbcomment('[b]', '[/b]')\" title='Bold'></i>");
    print("<i class='p-1 fa fa-italic' style='height:20px;width:20px;' onclick=\"bbcomment('[i]', '[/i]')\" title='Italic'></i>");
    print("<i class='p-1 fa fa-underline' style='height:20px;width:20px;' onclick=\"bbcomment('[u]', '[/u]')\" title='Underline'></i>");
    print("<i class='p-1 fa fa-strikethrough' style='height:20px;width:20px;' onclick=\"bbcomment('[s]', '[/s]')\" title='Strike'></i>");
    print("<i class='p-1 fa fa-list' style='height:20px;width:20px;' onclick=\"bbcomment('[list]', '[/list]')\" title='List'></i>");
    print("<i class='p-1 fa fa-quote-right' style='height:20px;width:20px;' onclick=\"bbcomment('[quote]', '[/quote]')\" title='Quote'></i>");
    print("<i class='p-1 fa fa-code' style='height:20px;width:20px;' onclick=\"bbcomment('[code]', '[/code]')\" title='Code'></i>");
    print("<i class='p-1 fa fa-link' style='height:20px;width:20px;' onclick=\"bbcomment('[url]', '[/url]')\" title='Link'></i>");
    print("<i class='p-1 fa fa-picture-o' style='height:20px;width:20px;' onclick=\"bbcomment('[img]', '[/img]')\" title='Image'></i>");
    print("<i class='p-1 fa fa-bolt' style='height:20px;width:20px;' onclick=\"bbcomment('[df]', '[/df]')\" title='Scroller'></i>");
    print("<i class='p-1 fa fa-arrow-left' style='height:20px;width:20px;' onclick=\"bbcomment('[align=left]','[/align]')\" title='Align Left'></i>");
    print("<i class='p-1 fa fa-align-center' style='height:20px;width:20px;' onclick=\"bbcomment('[align=center]','[/align]')\" title='Align Center'></i>");
    print("<i class='p-1 fa fa-arrow-right' style='height:20px;width:20px;' onclick=\"bbcomment('[align=right]','[/align]')\" title='Align Right'></i>");
    print("<a href='https://imgur.com/upload' target='_blank' class='p-1' title='Upload Image'><img src='" . e(url('page/theme/default/images/misc/bbcode_imgur.webp')) . "' style='height:20px;width:20px;' alt='' /></a>");
    print("<a href='https://www.youtube.com' target='_blank' class='p-1' title='YouTube'><img src='" . e(url('page/theme/default/images/misc/bbcode_youtube.webp')) . "' style='height:20px;width:20px;' alt='' /></a>");
    print("<a data-bs-toggle='collapse' data-bs-target='#collapseExample'><img style='margin-bottom:16px;height:20px;width:20px;' src='" . e(url('page/theme/default/images/smilies/grin.webp')) . "' alt='' /></a>&nbsp;");
    echo '<a href="' . e(url('shoutbox/history')) . '"><small><b>' . e(__('HISTORY')) . '</b></small></a>&nbsp;';

    print("<select name='color' style='height:24px;width:100px;' onChange='bbcouleur(this.value);' title='Colour'>");
    print("<option value='0'>Colour</option>");
    print("<option value='#000000' style='BACKGROUND-COLOR:#000000'>Black</option>");
    print("<option value='#FF0000' style='BACKGROUND-COLOR:#FF0000'>Red</option>");
    print("<option value='#00BCFF' style='BACKGROUND-COLOR:#00BCFF'>Light Blue</option>");
    print("<option value='#30CC30' style='BACKGROUND-COLOR:#30CC30'>Green</option>");
    print('</select>');
    ?>
    </div>

    <div class="container">
    <div class="row justify-content-md-center">
    <div class="collapse" id="collapseExample">
    <div class="text-center">
    <?php
    global $emoji;
    foreach ($emoji as $code => $title) {
        echo '<a title="' . e($title) . '" onclick="SmileIT(\'' . $code . '\',\'' . e($form) . '\',\'' . e($name) . '\')">' . $code . '</a> ';
    }
    ?>
    </div>
    </div>
    </div>
    </div>

    <div class="col-md-11">
        <input id="message" class="form-control" type="text" name="<?= e($name) ?>" value="<?= e($content) ?>">
    </div>
    <div class="col-md-1">
        <center><input type="submit" name="submit" value="<?= e(__('SUBMIT')) ?>" class="btn btn-sm ttbtn" /></center>
    </div>
    </div>
    </div>
    <?php
}

/**
 * Render a list of comments (torrent / news / request).
 * Expects a PDOStatement already executed.
 *
 * @param PDOStatement $res
 * @param string       $type  'torrent' | 'news' | 'req' | …
 */
function commenttable($res, string $type = ''): void
{
    while ($row = $res->fetch(PDO::FETCH_LAZY)) {
        $postername = coloredname($row['user']);
        if ($postername === __('SYSTEM') || $postername === 'System') {
            $title = $avatar = $usersignature = $userdownloaded = $useruploaded = '';
            $privacylevel = 'strong';
            $userratio = '---';
        } else {
            $privacylevel   = $row['privacy'] ?? '';
            $avatar         = htmlspecialchars((string) ($row['avatar'] ?? ''));
            $title          = format_comment((string) ($row['title'] ?? ''));
            $usersignature  = stripslashes(format_comment((string) ($row['signature'] ?? '')));
            $userdownloaded = mksize($row['downloaded'] ?? 0);
            $useruploaded   = mksize($row['uploaded'] ?? 0);
            $userratio      = ($row['downloaded'] ?? 0) > 0
                ? number_format(($row['uploaded'] ?? 0) / $row['downloaded'], 2)
                : '---';
        }

        if (!$avatar) {
            $avatar = url('page/theme/default/images/misc/default_avatar.webp');
        }

        $commenttext = format_comment((string) ($row['text'] ?? ''));

        $edit = '';
        if (
            ($type === 'torrent' && ($_SESSION['edit_torrents'] ?? '') === 'yes')
            || ($type === 'news' && ($_SESSION['edit_news'] ?? '') === 'yes')
            || ($type === 'req')
            || ((int) ($_SESSION['id'] ?? 0) === (int) $row['user'] && !empty($_SESSION['id']))
        ) {
            $edit = '<a href="#" data-bs-toggle="modal" data-bs-target="#editModal' . (int) $row['id'] . '"><i class="fa fa-pencil" title="' . e(__('EDIT')) . '"></i></a>';
        }

        $delete = '';
        if (
            ($type === 'torrent' && ($_SESSION['delete_torrents'] ?? '') === 'yes')
            || ($type === 'news' && ($_SESSION['delete_news'] ?? '') === 'yes')
            || $type === 'req' || $type === 'id'
        ) {
            $delete = '<a href="' . e(url('comment/delete/' . $row['id'])) . '"><i class="fa fa-trash" title="' . e(__('DEL')) . '"></i></a>';
        }
        ?>
        <div class="row p-4">
            <div class="col-2">
                <?php if (($_SESSION['edit_users'] ?? '') !== 'yes' && $privacylevel === 'strong') : ?>
                    <a href="<?= e(url('profile/view/' . $row['user'])) ?>"><b><?= $postername ?></b></a>
                    <?= $title ?><br>
                    <img width="80" height="80" src="<?= e($avatar) ?>" alt="" /><br>
                    <i class="fa fa-upload"></i> <?= $useruploaded ?><br>
                    <i class="fa fa-download"></i> <?= $userdownloaded ?><br>
                    <?= e(__('RATIO')) ?>: <?= get_ratio_color($userratio) ?><br>
                <?php else : ?>
                    <a href="<?= e(url('profile/view/' . $row['user'])) ?>"><b><?= e(__('PRIVATE')) ?></b></a><br>
                    <img width="80" height="80" src="<?= e(url('page/theme/default/images/misc/default_avatar.webp')) ?>" alt="" /><br>
                <?php endif; ?>
            </div>
            <div class="col-10">
                <?= e(__('POSTED')) ?>: <?= e(user_timezone_date($row['added'])) ?>
                <a id="comment<?= (int) $row['id'] ?>"></a><br>
                <div class="p-2"><?= $commenttext ?></div>
                <hr>
                <?= $usersignature ?>
            </div>
            <div class="col-12">
                <?= $edit ?> <?= $delete ?>
                <a href="#" data-bs-toggle="modal" data-bs-target="#reportModal"><i class="fa fa-flag" title="<?= e(__('REPORT')) ?>"></i></a>
            </div>
        </div>

        <form method="post" action="<?= e(url('comment/submit/' . $row['id'] . '/' . $type)) ?>">
            <div class="modal fade" id="editModal<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <input type="hidden" name="comment_id" value="<?= (int) $row['id'] ?>" />
                            <?php textbbcode('submit', 'text', (string) $row['text']); ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= e(__('CLOSE')) ?></button>
                            <button type="submit" class="btn btn-sm ttbtn"><?= e(__('SAVE')) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }
}

/**
 * Main torrent list table (browse / search / user uploads).
 * Column set comes from CONFIG['TORRENTTABLE_COLUMNS'].
 *
 * @param iterable $res  Rows (array or PDO result)
 * @param string   $columns
 */
function torrenttable($res, string $columns = ''): void
{
    if ($columns === '') {
        $columns = (string) (CONFIG['TORRENTTABLE_COLUMNS'] ?? 'category,name,dl,size,seeders,leechers');
    }

    // Wait-time for low ratio members (optional)
    $wait = 0;
    if (
        !empty(CONFIG['MEMBERSONLY_WAIT'])
        && !empty(CONFIG['MEMBERSONLY'])
        && in_array((string) ($_SESSION['class'] ?? ''), explode(',', (string) (CONFIG['CLASS_WAIT'] ?? '1')), true)
    ) {
        $gigs = (($_SESSION['uploaded'] ?? 0) / (1024 * 1024 * 1024));
        $userratio = (($_SESSION['downloaded'] ?? 0) > 0)
            ? number_format(($_SESSION['uploaded'] ?? 0) / $_SESSION['downloaded'], 1)
            : '---';
        if ($userratio < CONFIG['RATIOA'] || $gigs < CONFIG['GIGSA']) {
            $wait = (int) CONFIG['A_WAIT'];
        } elseif ($userratio < CONFIG['RATIOB'] || $gigs < CONFIG['GIGSB']) {
            $wait = (int) CONFIG['B_WAIT'];
        } elseif ($userratio < CONFIG['RATIOC'] || $gigs < CONFIG['GIGSC']) {
            $wait = (int) CONFIG['C_WAIT'];
        } elseif ($userratio < CONFIG['RATIOD'] || $gigs < CONFIG['GIGSD']) {
            $wait = (int) CONFIG['D_WAIT'];
        }
    }

    $cols = array_map('trim', array_map('strtolower', explode(',', $columns)));

    echo '<div class="table-responsive"><table class="table table-striped"><thead><tr>';
    foreach ($cols as $col) {
        match ($col) {
            'category'  => print('<th>' . e(__('TYPE')) . '</th>'),
            'name'      => print('<th>' . e(__('NAME')) . '</th>'),
            'dl'        => print('<th>' . e(__('DL')) . '</th>'),
            'magnet'    => print('<th>' . e(__('MAGNET')) . '</th>'),
            'uploader'  => print('<th>' . e(__('UPLOADER')) . '</th>'),
            'tube'      => print('<th>' . e(__('YOUTUBE')) . '</th>'),
            'tmdb'      => print('<th>' . e(__('TMDB')) . '</th>'),
            'comments'  => print('<th>' . e(__('COMM')) . '</th>'),
            'nfo'       => print('<th>' . e(__('NFO')) . '</th>'),
            'size'      => print('<th>' . e(__('SIZE')) . '</th>'),
            'completed' => print('<th>' . e(__('C')) . '</th>'),
            'seeders'   => print('<th>' . e(__('S')) . '</th>'),
            'leechers'  => print('<th>' . e(__('L')) . '</th>'),
            'health'    => print('<th>' . e(__('HEALTH')) . '</th>'),
            'external'  => !empty(CONFIG['ALLOWEXTERNAL']) ? print('<th>' . e(__('L/E')) . '</th>') : null,
            'added'     => print('<th>' . e(__('ADDED')) . '</th>'),
            'speed'     => print('<th>' . e(__('SPEED')) . '</th>'),
            'wait'      => $wait ? print('<th>' . e(__('WAIT')) . '</th>') : null,
            'rating'    => print('<th>' . e(__('RATINGS')) . '</th>'),
            'admin'     => (($_SESSION['class'] ?? 0) == 7) ? print('<th>' . e(__('STAFF')) . '</th>') : null,
            default     => null,
        };
    }
    echo '</tr></thead>';

    foreach ($res as $row) {
        $id = (int) $row['id'];
        echo '<tr>';
        foreach ($cols as $col) {
            switch ($col) {
                case 'category':
                    echo '<td>';
                    if (!empty($row['cat_name'])) {
                        echo '<center><a href="' . e(url('browse?cat=' . $row['category'])) . '">';
                        if (!empty($row['cat_pic'])) {
                            echo '<i class="fa ' . e($row['cat_pic']) . '" style="font-size:22px;" title="' . e($row['cat_name']) . '"></i>';
                        } else {
                            echo e(($row['cat_parent'] ?? '') . ': ' . $row['cat_name']);
                        }
                        echo '</a></center>';
                    } else {
                        echo '-';
                    }
                    echo '</td>';
                    break;

                case 'name':
                    $smallname = e(CutName((string) $row['name'], 40));
                    $type = '';
                    if (strtotime((string) $row['added']) >= strtotime('yesterday')) {
                        $type .= " <b><font color='#ff0000'>(" . e(__('NEW')) . "!)</font></b>";
                    }
                    if (($row['freeleech'] ?? 0) == 1) {
                        $type .= " <i class='fa fa-sun-o' title='" . e(__('FREELEECH')) . "'></i>";
                    }
                    if (($row['vip'] ?? '') === 'yes') {
                        $type .= " <i class='fa fa-user-secret' title='" . e(__('VIP')) . "'></i>";
                    }
                    if (($row['sticky'] ?? '') === 'yes') {
                        $type .= " <i class='fa fa-thumb-tack' title='" . e(__('STICKY')) . "'></i>";
                    }
                    echo '<td nowrap="nowrap"><a href="' . e(url("torrent/{$id}/hit")) . '"><b>' . $smallname . '</b></a>' . $type;
                    echo get_tags($id) . '</td>';
                    break;

                case 'dl':
                    echo '<td><a href="' . e(url("download/{$id}")) . '"><i class="fa fa-download" style="font-size:22px;color:green" title="' . e(__('DOWNLOADED')) . '"></i></a></td>';
                    break;

                case 'magnet':
                    // Simplified: full thanks/force logic stays in magnetlink() for detail page
                    if (($row['external'] ?? '') === 'yes') {
                        $href = 'magnet:?xt=urn:btih:' . rawurlencode((string) $row['info_hash'])
                              . '&dn=' . rawurlencode((string) $row['name'])
                              . '&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com';
                        echo '<td><a href="' . e($href) . '"><i class="fa fa-magnet fa-2x" style="font-size:23px;color:green"></i></a></td>';
                    } else {
                        echo '<td><i class="fa fa-home" style="font-size:23px;color:green" title="' . e(__('LOCAL')) . '"></i></td>';
                    }
                    break;

                case 'uploader':
                    echo '<td>';
                    if (
                        (($row['anon'] ?? '') === 'yes' || ($row['privacy'] ?? '') === 'strong')
                        && (int) ($_SESSION['id'] ?? 0) !== (int) ($row['owner'] ?? 0)
                        && ($_SESSION['edit_torrents'] ?? '') !== 'yes'
                    ) {
                        echo 'Anonymous';
                    } elseif (!empty($row['username'])) {
                        echo '<a href="' . e(url('profile/view/' . $row['owner'])) . '">' . coloredname($row['owner']) . '</a>';
                    } else {
                        echo 'Unknown';
                    }
                    echo '</td>';
                    break;

                case 'tube':
                    echo !empty($row['tube'])
                        ? '<td><a href="' . e($row['tube']) . '"><i class="fa fa-youtube-play" style="font-size:32px;color:#ff2c2c"></i></a></td>'
                        : '<td>-</td>';
                    break;

                case 'tmdb':
                    if (!empty($row['imdb'])) {
                        echo '<td><a href="' . e($row['imdb']) . '"><img src="' . e(url('page/theme/default/images/misc/imdb.webp')) . '" width="28" height="25" alt="" /></a></td>';
                    } elseif (!empty($row['tmdb'])) {
                        echo '<td><a href="' . e($row['tmdb']) . '"><img src="' . e(url('page/theme/default/images/misc/tmdb1.webp')) . '" width="28" height="25" alt="" /></a></td>';
                    } else {
                        echo '<td>-</td>';
                    }
                    break;

                case 'comments':
                    echo '<td><a href="' . e(url("comment/view/{$id}/torrent")) . '"><b>' . (int) ($row['comments'] ?? 0) . '</b></a></td>';
                    break;

                case 'nfo':
                    echo (($row['nfo'] ?? '') === 'yes')
                        ? '<td><i class="fa fa-file-text-o" title="' . e(__('NFO')) . '"></i></td>'
                        : '<td>-</td>';
                    break;

                case 'size':
                    echo '<td style="min-width:110px;">' . mksize($row['size'] ?? 0) . '</td>';
                    break;

                case 'completed':
                    echo '<td><font color="orange"><b>' . number_format((int) ($row['times_completed'] ?? 0)) . '</b></font></td>';
                    break;

                case 'seeders':
                    echo '<td><font color="green"><b>' . number_format((int) ($row['seeders'] ?? 0)) . '</b></font></td>';
                    break;

                case 'leechers':
                    echo '<td><font color="#ff0000"><b>' . number_format((int) ($row['leechers'] ?? 0)) . '</b></font></td>';
                    break;

                case 'health':
                    $h = health((int) ($row['leechers'] ?? 0), (int) ($row['seeders'] ?? 0));
                    echo '<td><img src="' . e(url("page/theme/default/images/health/health_{$h}.webp")) . '" alt="" /></td>';
                    break;

                case 'external':
                    if (!empty(CONFIG['ALLOWEXTERNAL'])) {
                        echo '<td>' . e((($row['external'] ?? '') === 'yes') ? __('E') : __('L')) . '</td>';
                    }
                    break;

                case 'added':
                    echo '<td style="min-width:110px;">' . e(get_elapsed_time((int) user_timezone_date($row['added'], true))) . '</td>';
                    break;

                case 'speed':
                    echo '<td>--</td>';
                    break;

                case 'wait':
                    if ($wait) {
                        $elapsed = (int) floor((CONFIG['TT_TIME'] - strtotime((string) $row['added'])) / 3600);
                        if ($elapsed < $wait && ($row['external'] ?? '') !== 'yes') {
                            echo '<td>' . number_format($wait - $elapsed) . ' h</td>';
                        } else {
                            echo '<td>--</td>';
                        }
                    }
                    break;

                case 'rating':
                    $average = (int) ceil((float) ($row['rating'] ?? 0));
                    echo $average
                        ? '<td><img src="' . e(url("page/theme/default/images/rating/{$average}.webp")) . '" alt="rating" /></td>'
                        : '<td>--</td>';
                    break;

                case 'admin':
                    if (($_SESSION['class'] ?? 0) == 7) {
                        echo '<td><input type="checkbox" name="ids[]" value="' . $id . '" /></td>';
                    }
                    break;
            }
        }
        echo '</tr>';
    }

    echo '</table></div><br />';
}

/** HTML table: top countries ranking. */
function topcountry(array $data): string
{
    $html = "<div class='table-responsive'><table class='table table-striped'><thead><tr>";
    $html .= '<th>Rank</th><th>Flag</th><th>Country</th></tr></thead><tbody>';
    $num = 0;
    foreach ($data as $a) {
        $num++;
        $flag = !empty($a['flagpic'])
            ? '<img src="' . e(url('page/theme/default/images/languages/' . $a['flagpic'])) . '" alt="" />'
            : '<img src="' . e(url('page/theme/default/images/languages/unknown.webp')) . '" alt="" />';
        $name = !empty($a['name']) ? '<b>' . e($a['name']) . '</b>' : '<b>Unknown</b>';
        $html .= "<tr><td>{$num}</td><td>{$flag}</td><td>{$name}</td></tr>";
    }
    $html .= '</tbody></table></div>';
    return $html;
}

/** HTML table: top torrents ranking. */
function toptorrent(array $data): string
{
    $html = "<div class='table-responsive'><table class='table table-striped'><thead><tr>";
    $html .= '<th>Rank</th><th>Name</th><th>Done</th><th>Traffic</th><th>Seed</th><th>Leech</th><th>Total</th><th>Ratio</th></tr></thead><tbody>';
    $num = 0;
    foreach ($data as $a) {
        $num++;
        $ratio = ($a['leechers'] ?? 0) > 0
            ? number_format(($a['seeders'] ?? 0) / $a['leechers'], 1)
            : '---';
        $html .= '<tr>';
        $html .= "<td>{$num}</td>";
        $html .= '<td><a href="' . e(url('torrent/' . $a['id'] . '/hit')) . '"><b>' . e($a['name']) . '</b></a></td>';
        $html .= '<td>' . number_format((int) ($a['times_completed'] ?? 0)) . '</td>';
        $html .= '<td>' . mksize($a['data'] ?? 0) . '</td>';
        $html .= '<td>' . number_format((int) ($a['seeders'] ?? 0)) . '</td>';
        $html .= '<td>' . number_format((int) ($a['leechers'] ?? 0)) . '</td>';
        $html .= '<td>' . ((int) ($a['leechers'] ?? 0) + (int) ($a['seeders'] ?? 0)) . '</td>';
        $html .= '<td>' . get_ratio_color($ratio) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';
    return $html;
}

/** HTML table: top users ranking. */
function topuser(array $data): string
{
    $html = "<div class='table-responsive'><table class='table table-striped'><thead><tr>";
    $html .= '<th>Rank</th><th>User</th><th>Uploaded</th><th>UL speed</th><th>Downloaded</th><th>DL speed</th><th>Ratio</th><th>Joined</th></tr></thead><tbody>';
    $num = 0;
    foreach ($data as $a) {
        $num++;
        $ratio = ($a['downloaded'] ?? 0) > 0
            ? number_format(($a['uploaded'] ?? 0) / $a['downloaded'], 1)
            : '---';
        $html .= '<tr>';
        $html .= "<td>{$num}</td>";
        $html .= '<td><a href="' . e(url('profile/view/' . $a['userid'])) . '"><b>' . coloredname($a['userid']) . '</b></a></td>';
        $html .= '<td>' . mksize($a['uploaded'] ?? 0) . '</td>';
        $html .= '<td>' . mksize($a['upspeed'] ?? 0) . '/s</td>';
        $html .= '<td>' . mksize($a['downloaded'] ?? 0) . '</td>';
        $html .= '<td>' . mksize($a['downspeed'] ?? 0) . '/s</td>';
        $html .= '<td>' . get_ratio_color($ratio) . '</td>';
        $html .= '<td>' . e(date('d-M-Y', strtotime((string) ($a['added'] ?? 'now')))) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';
    return $html;
}

/** Forum top bar: search + new posts + mark read + breadcrumb. */
function forumheader(string $location = ''): void
{
    echo "<div class='rounded p-2'>";
    echo "<i class='fa fa-search'></i>&nbsp;<a href='" . e(url('forum/search')) . "'>" . e(__('SEARCH')) . '</a>&nbsp;';
    echo '<b>' . e(__('FORUM_CONTROL')) . '</b> &middot; ';
    echo "<a href='" . e(url('forum/unread')) . "'>" . e(__('FORUM_NEW_POSTS')) . '</a> &middot; ';
    echo "<a href='" . e(url('forum/unread/true')) . "'>" . e(__('FORUM_MARK_READ')) . '</a>';
    echo '</div><br>';
    echo "<div class='rounded p-1'>" . e(__('YOU_ARE_IN')) . ": <a href='" . e(url('forum/view')) . "'>" . e(__('FORUM')) . '</a> / <b>' . $location . '</b></div><br>';
}

/** Mark all topics as read for the current user. */
function catch_up(): void
{
    if (empty($_SESSION['id'])) {
        return;
    }
    $userid = (int) $_SESSION['id'];
    $res = DB::run('SELECT id, lastpost FROM forum_topics');
    foreach ($res as $arr) {
        $topicid = (int) $arr['id'];
        $postid  = (int) $arr['lastpost'];
        $r = DB::run(
            'SELECT id, lastpostread FROM forum_readposts WHERE userid = ? AND topicid = ?',
            [$userid, $topicid]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            DB::run(
                'INSERT INTO forum_readposts (userid, topicid, lastpostread) VALUES (?,?,?)',
                [$userid, $topicid, $postid]
            );
        } elseif ((int) $r['lastpostread'] < $postid) {
            DB::run(
                'UPDATE forum_readposts SET lastpostread = ? WHERE id = ?',
                [$postid, $r['id']]
            );
        }
    }
}

/** Last post id in a forum, or 0. */
function get_forum_last_post(int $forumid): int
{
    $res = DB::run(
        'SELECT lastpost FROM forum_topics WHERE forumid = ? ORDER BY lastpost DESC LIMIT 1',
        [$forumid]
    )->fetchColumn();
    return $res ? (int) $res : 0;
}

/** Index block: latest forum topics table. */
function latestforumposts(int $sub_id = 0): void
{
    ?>
    <div class="table-responsive tt_block">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Latest Topic</th>
                    <th>Replies</th>
                    <th>Views</th>
                    <th>Author</th>
                    <th>Last Post</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $for = DB::run('SELECT * FROM forum_topics ORDER BY lastpost DESC LIMIT 8');
            if ($for->rowCount() === 0) {
                echo '<tr><td colspan="5">No Latest Topics</td></tr>';
            }
            foreach ($for as $topicarr) {
                $forum = DB::run(
                    'SELECT name, minclassread, guest_read FROM forum_forums WHERE id = ?',
                    [$topicarr['forumid']]
                )->fetch(PDO::FETCH_ASSOC);
                if (!$forum) {
                    continue;
                }
                $canRead = (($_SESSION['class'] ?? 0) >= ($forum['minclassread'] ?? 0))
                    || (($forum['guest_read'] ?? '') === 'yes');
                if (!$canRead) {
                    continue;
                }
                $posts = (int) DB::run(
                    'SELECT COUNT(id) FROM forum_posts WHERE topicid = ?',
                    [$topicarr['id']]
                )->fetchColumn();
                $replies = max(0, $posts - 1);
                $arr = DB::run(
                    'SELECT added, userid FROM forum_posts WHERE topicid = ? ORDER BY id DESC LIMIT 1',
                    [$topicarr['id']]
                )->fetch(PDO::FETCH_ASSOC);
                ?>
                <tr>
                    <td><b><a href="<?= e(url('post/view/' . $topicarr['id'] . '/last/#last')) ?>"><?= stripslashes(encodehtml($topicarr['subject'])) ?></a></b></td>
                    <td><?= $replies ?></td>
                    <td><?= (int) $topicarr['views'] ?></td>
                    <td><center><?= coloredname($topicarr['userid']) ?></center></td>
                    <td>
                        <small>
                            by <b><?= coloredname($arr['userid'] ?? 0) ?></b><br>
                            <?= e(user_timezone_date($arr['added'] ?? CONFIG['TT_DATE'])) ?>
                        </small>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Build last-post preview + read/unread icon for a forum.
 * @return array{img:string,lastpost:string}
 */
function lastpostdetails(int $lastpostid): array
{
    $post = DB::run(
        'SELECT added, topicid, userid FROM forum_posts WHERE id = ?',
        [$lastpostid]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        return ['img' => 'folder', 'lastpost' => '<span class="small">No Posts</span>'];
    }

    $lasttopicid = (int) $post['topicid'];
    $topic = DB::run('SELECT subject FROM forum_topics WHERE id = ?', [$lasttopicid])->fetch(PDO::FETCH_ASSOC);
    $lasttopic = stripslashes(htmlspecialchars((string) ($topic['subject'] ?? '')));
    $lastposter = coloredname($post['userid']);
    $lastpostdate = user_timezone_date($post['added']);

    $lastpost = "<small><a href='" . e(url("post/view/{$lasttopicid}/last/#last")) . "'><b>"
              . e(CutName($lasttopic, 10)) . "</b></a> by {$lastposter}<br />{$lastpostdate}</small>";

    $img = "<i class='fa fa-file-text tticon-red' title='UnRead'></i>";
    if (!empty($_SESSION['id'])) {
        $a = DB::run(
            'SELECT COUNT(id) FROM forum_readposts WHERE userid = ? AND topicid = ?',
            [$_SESSION['id'], $lasttopicid]
        )->fetchColumn();
        if ($a) {
            $img = "<i class='fa fa-file-text' title='Read'></i>";
        }
    }

    return ['img' => $img, 'lastpost' => $lastpost];
}

/** Staff tools under a topic: rename, move, lock, sticky, delete. */
function modoptions(int $topicid, string $subject, int $forumid, $locked, $sticky): void
{
    ?>
    <div class="tt_blockcenter rounded p-2">
        <p class="text-center"><?= e(__('FORUMS_MOD_OPTIONS')) ?></p>
        <form method="post" action="<?= e(url('topic/rename/' . $topicid)) ?>">
            <p class="text-center"><?= e(__('RENAME')) ?></p>
            <input class="form-control fullwidth" type="text" name="subject" value="<?= e(stripslashes($subject)) ?>" />
            <p class="text-center"><input type="submit" class="btn btn-sm ttbtn" value="<?= e(__('SUBMIT')) ?>" /></p>
        </form><br>
        <form method="post" action="<?= e(url('topic/move/' . $topicid)) ?>">
            <p class="text-center"><?= e(__('MOVE')) ?></p>
            <select name="forumid" class="form-select">
            <?php
            $res = DB::run('SELECT id, name, minclasswrite FROM forum_forums ORDER BY name');
            foreach ($res as $arr) {
                if ((int) $arr['id'] === $forumid) {
                    continue;
                }
                if (($_SESSION['class'] ?? 0) < ($arr['minclasswrite'] ?? 0)) {
                    continue;
                }
                echo '<option value="' . (int) $arr['id'] . '">' . e($arr['name']) . '</option>';
            }
            ?>
            </select>
            <p class="text-center"><input type="submit" class="btn btn-sm ttbtn" value="<?= e(__('SUBMIT')) ?>" /></p>
        </form>
        <p class="text-center">
        <?php if ($locked) : ?>
            <?= e(__('LOCKED')) ?>: <a href="<?= e(url("topic/action/{$forumid}/{$topicid}/unlock")) ?>"><i class="fa fa-unlock tticon-red"></i></a>
        <?php else : ?>
            <?= e(__('LOCKED')) ?>: <a href="<?= e(url("topic/action/{$forumid}/{$topicid}/lock")) ?>"><i class="fa fa-lock tticon"></i></a>
        <?php endif; ?>
            Delete: <a href="<?= e(url('topic/delete/' . $topicid)) ?>"><i class="fa fa-trash tticon-red"></i></a>
        <?php if ($sticky) : ?>
            <?= e(__('STICKY')) ?>: <a href="<?= e(url("topic/action/{$forumid}/{$topicid}/unsticky")) ?>"><i class="fa fa-exclamation tticon-red"></i></a>
        <?php else : ?>
            <?= e(__('STICKY')) ?>: <a href="<?= e(url("topic/action/{$forumid}/{$topicid}/sticky")) ?>"><i class="fa fa-exclamation tticon"></i></a>
        <?php endif; ?>
        </p>
    </div>
    <?php
}

/**
 * Load poster sidebar data for a forum post.
 * @return array<string,mixed>
 */
function posterdetails(int $userid): array
{
    $forumposts = (int) DB::run('SELECT COUNT(id) FROM forum_posts WHERE userid = ?', [$userid])->fetchColumn();
    $arr2 = DB::run('SELECT * FROM users WHERE id = ?', [$userid])->fetch(PDO::FETCH_ASSOC);

    if (!$arr2) {
        return [
            'by' => 'Deluser', 'title' => 'Deleted Account', 'privacylevel' => 'strong',
            'usersignature' => '', 'userdownloaded' => '0', 'useruploaded' => '0',
            'avatar' => url('page/theme/default/images/misc/default_avatar.webp'), 'forumposts' => 0,
            'userratio' => '---', 'usercountry' => 'unknown', 'quotename' => '',
        ];
    }

    $avatar = htmlspecialchars((string) ($arr2['avatar'] ?? ''));
    if ($avatar === '') {
        $avatar = url('page/theme/default/images/misc/default_avatar.webp');
    }

    $userdownloaded = mksize($arr2['downloaded'] ?? 0);
    $useruploaded   = mksize($arr2['uploaded'] ?? 0);
    $userratio = ($arr2['downloaded'] ?? 0) > 0
        ? number_format(($arr2['uploaded'] ?? 0) / $arr2['downloaded'], 1)
        : '---';

    if (($arr2['privacy'] ?? '') === 'strong' && ($_SESSION['control_panel'] ?? '') !== 'yes') {
        $useruploaded = $userdownloaded = $userratio = '---';
    }

    $usercountry = 'unknown';
    if (!empty($arr2['country'])) {
        $c = DB::run('SELECT * FROM countries WHERE id = ? LIMIT 1', [$arr2['country']])->fetch(PDO::FETCH_ASSOC);
        $usercountry = $c ?: 'unknown';
    }

    $donated = (float) ($arr2['donated'] ?? 0);
    $by = "<a href='" . e(url('profile/view/' . $userid)) . "'>" . coloredname($userid) . '</a>'
        . ($donated > 0 ? " <i class='fa fa-star' style='color:orange'></i>" : '');

    return [
        'by' => $by,
        'title' => format_comment((string) ($arr2['title'] ?? '')),
        'privacylevel' => $arr2['privacy'] ?? '',
        'usersignature' => stripslashes(format_comment((string) ($arr2['signature'] ?? ''))),
        'userdownloaded' => $userdownloaded,
        'useruploaded' => $useruploaded,
        'avatar' => $avatar,
        'forumposts' => $forumposts,
        'userratio' => $userratio,
        'usercountry' => $usercountry,
        'quotename' => $arr2['username'] ?? '',
    ];
}

/** Profile sub-navigation buttons. */
function usermenu(int $id, string $page = ''): void
{
    $messageactive = ($page === 'messages') ? 'ttbtnactive' : 'btn btn-sm ttbtn';
    ?>
    <p>
        <a href="<?= e(url('profile/view/' . $id)) ?>"><button type="button" class="<?= e(activelink('profile')) ?>"><?= e(__('PROFILE')) ?></button></a>
        <?php if ((int) ($_SESSION['id'] ?? 0) === $id) : ?>
            <a href="<?= e(url('message/mailbox')) ?>"><button type="button" class="<?= e($messageactive) ?>"><?= e(__('MESSAGES')) ?></button></a>
        <?php else : ?>
            <a href="<?= e(url('message/create/' . $id)) ?>"><button type="button" class="<?= e(activelink('message/create/' . $id)) ?>"><?= e(__('PM')) ?></button></a>
        <?php endif; ?>
    </p>
    <?php
}

/** Torrent detail sub-navigation. */
function torrentmenu(int $id, string $external = 'no'): void
{
    echo '<a href="' . e(url('torrent/' . $id)) . '"><button type="button" class="' . e(activelink('torrent')) . '">' . e(__('BACK')) . '</button></a> ';
    if ($external !== 'yes') {
        echo '<a href="' . e(url('peer/view/torrent/' . $id)) . '"><button type="button" class="' . e(activelink('peer/view/torrent/' . $id)) . '">' . e(__('PEERS')) . '</button></a>';
    }
    echo '<br><br>';
}

/**
 * Format one PM row for mailbox lists.
 * @return array{sender:string,receiver:string,subject:string,unread:string,added:string}
 */
function msgdetails(string $type, array $arr = []): array
{
    $sid = (int) ($_SESSION['id'] ?? 0);

    if ((int) ($arr['sender'] ?? 0) === $sid) {
        $sender = 'Yourself';
    } elseif (!empty($arr['sender'])) {
        $sender = '<a href="' . e(url('profile/view/' . $arr['sender'])) . '">' . coloredname($arr['sender']) . '</a>';
    } else {
        $sender = __('SYSTEM');
    }

    if ((int) ($arr['receiver'] ?? 0) === $sid) {
        $receiver = 'Yourself';
    } elseif (!empty($arr['receiver'])) {
        $receiver = '<a href="' . e(url('profile/view/' . $arr['receiver'])) . '">' . coloredname($arr['receiver']) . '</a>';
    } else {
        $receiver = __('SYSTEM');
    }

    $subject = '<a href="' . e(url('message/index/' . $arr['id'] . '/' . $type)) . '"><b>' . format_comment((string) ($arr['subject'] ?? '')) . '</b></a>';
    $unread  = (($arr['unread'] ?? '') === 'yes')
        ? "<i class='fa fa-file-text tticon-red' title='" . e(__('UNREAD')) . "'></i>"
        : "<i class='fa fa-file-text' title='" . e(__('READ')) . "'></i>";

    return [
        'sender'   => $sender,
        'receiver' => $receiver,
        'subject'  => $subject,
        'unread'   => $unread,
        'added'    => user_timezone_date($arr['added'] ?? CONFIG['TT_DATE']),
    ];
}

/**
 * Build display fields for a user post/comment row.
 * @return array<string,mixed>
 */
function userpostdetails(array $row, ?string $type = null): array
{
    $postername = coloredname($row['id'] ?? $row['user'] ?? 0);
    $avatar = htmlspecialchars((string) ($row['avatar'] ?? ''));
    if ($avatar === '') {
        $avatar = url('page/theme/default/images/misc/default_avatar.webp');
    }

    $commenttext = !empty($row['body'])
        ? format_comment((string) $row['body'])
        : format_comment((string) ($row['text'] ?? ''));

    return [
        'postername'      => $postername,
        'title'           => format_comment((string) ($row['title'] ?? '')),
        'avatar'          => $avatar,
        'usersignature'   => stripslashes(format_comment((string) ($row['signature'] ?? ''))),
        'userdownloaded'  => mksize($row['downloaded'] ?? 0),
        'useruploaded'    => mksize($row['uploaded'] ?? 0),
        'privacylevel'    => $row['privacy'] ?? '',
        'userratio'       => (($row['downloaded'] ?? 0) > 0)
            ? number_format(($row['uploaded'] ?? 0) / $row['downloaded'], 1)
            : '---',
        'commenttext'     => $commenttext,
        'edit'            => '',
        'delete'          => '',
        'editcomment'     => '',
        'deletecomment'   => '',
    ];
}

/** Echo uploader name or “Anonymous” on torrent pages. */
function anon(array $data): void
{
    if (($data['anon'] ?? '') === 'yes' && empty($data['owned'])) {
        echo e(__('ANON'));
    } elseif (!empty($data['username'])) {
        echo '<a href="' . e(url('profile/view/' . $data['owner'])) . '">' . coloredname($data['owner']) . '</a>';
    } else {
        echo e(__('SYSTEM'));
    }
}

/** Echo magnet / thanks controls on torrent detail (legacy rules). */
function magnetlink(array $data): void
{
    if (
        empty(CONFIG['FORCETHANKS'])
        && ($data['external'] ?? '') === 'yes'
    ) {
        $href = 'magnet:?xt=urn:btih:' . rawurlencode((string) $data['info_hash'])
              . '&dn=' . rawurlencode((string) ($data['filename'] ?? $data['name'] ?? ''));
        echo '&nbsp;<a href="' . e($href) . '"><i class="fa fa-magnet" style="color:green"></i></a>&nbsp;';
        return;
    }
    // Full force-thanks path kept simple for migration — expand when torrent page is ported
    if (($data['external'] ?? '') === 'yes') {
        $href = 'magnet:?xt=urn:btih:' . rawurlencode((string) $data['info_hash'])
              . '&dn=' . rawurlencode((string) ($data['filename'] ?? $data['name'] ?? ''));
        echo '&nbsp;<a href="' . e($href) . '"><i class="fa fa-magnet" style="color:green"></i></a>&nbsp;';
    }
}

/** Show NFO file contents for a torrent id (from NFOS_PATH). */
function displaynfo(int $id): void
{
    if (!defined('NFOS_PATH')) {
        echo e(__('NO_NFO_ERROR'));
        return;
    }
    $path = NFOS_PATH . "/{$id}.nfo";
    if (!is_file($path)) {
        echo e(__('NO_NFO_ERROR'));
        return;
    }
    $nfo = file_get_contents($path);
    echo "<p class='text-center'><b>NFO:</b></p>";
    echo "<textarea class='form-control' style='min-width:700px;height:700px;' readonly='readonly'>"
       . e(stripslashes((string) $nfo))
       . '</textarea>';
}

/** Embed a local file as a data: URI (rare use). */
function data_uri(string $file, string $mime): string
{
    $contents = file_get_contents($file);
    return 'data:' . $mime . ';base64,' . base64_encode((string) $contents);
}