<?php
declare(strict_types=1);

/**
 * Site configuration.
 *
 * Access as: CONFIG['SITENAME'], CONFIG['_MODERATOR'], etc.
 * Keep secrets out of public git when you go live (env file later is ideal).
 */
define('CONFIG', [

    // -----------------------------------------------------------------
    // Core site
    // -----------------------------------------------------------------
    'SITENAME'     => 'M-BiTsy',
    'SITEEMAIL'    => 'something@email.com',
    'SITE_SECRET'  => 'ea0b3fb4cd074531bf108798b329eadd', // change on live
    'SITE_ONLINE'  => true,
    'OFFLINEMSG'   => 'Site is down for a little while',
    'CHARSET'      => 'utf-8',
    'DEFAULTLANG'  => 'english',
    'DEFAULTTHEME' => 'default', // must match page/theme/{name}

    // Request-time stamps (set once per request)
    'TT_TIME' => time(),
    'TT_DATE' => date('Y-m-d H:i:s'),

    // -----------------------------------------------------------------
    // Tracker / announce
    // -----------------------------------------------------------------
    'ANNOUNCELIST' => '/announce.php',
    'PASSKEYURL'   => '/announce.php?passkey=%s',
    'ALLOWEXTERNAL'=> true,
    'PEERLIMIT'    => 10000,
    'ANNOUNCEINTERVAL' => 900,

    // -----------------------------------------------------------------
    // Membership / access
    // -----------------------------------------------------------------
    'MEMBERSONLY'       => false,
    'MEMBERSONLY_WAIT'  => true,
    'UPLOADERSONLY'     => false,
    'INVITEONLY'        => false,
    'ENABLEINVITES'     => true,
    'CONFIRMEMAIL'      => false,
    'ACONFIRM'          => false,
    'ACCOUNTMAX'        => 1,
    'MAXUSERS'          => 20000,
    'MAXUSERSINVITE'    => 50000,
    'SIGNUPTIMEOUT'     => 259200, // seconds
    'WELCOMEPM_MSG'     => 'Thank you for registering at our tracker! Please remember to keep your ratio at 1.00 or greater',

    // User class thresholds (group ids)
    '_VIP'            => 3,
    '_UPLOADER'       => 4,
    '_MODERATOR'      => 5,
    '_ADMINISTRATOR'  => 7,

    // -----------------------------------------------------------------
    // Security
    // -----------------------------------------------------------------
    'CAPTCHA_ON'     => false,
    'CAPTCHA_KEY'    => 'googlecaptchakey',
    'CAPTCHA_SECRET' => 'googlecaptchasecret',
    'CSRF_Token'     => false, // app uses csrf_* helpers either way
    'IPCHECK'        => false,

    // -----------------------------------------------------------------
    // Uploads / images
    // -----------------------------------------------------------------
    'ANONYMOUSUPLOAD'   => false,
    'UPLOADRULES'       => 'You should also include a .nfo file wherever possible<br />Try to make sure your torrent is well-seeded for at least 24 hours<br />Do not re-release material that is still active',
    'USE_WEBP'          => true,
    'IMAGEMAXFILESIZE'  => 524288, // 512 KB
    'ALLOWEDIMAGETYPES' => [
        'image/gif'  => '.gif',
        'image/jpeg' => '.jpg',
        'image/jpg'  => '.jpg',
        'image/png'  => '.png',
    ],

    // -----------------------------------------------------------------
    // Browse / UI
    // -----------------------------------------------------------------
    // Pages where sidebars can be hidden
    'ISURL' => ['authorise/login', 'authorise/logout', 'authorise/signup', 'authorise/contact', 'authorise/recover', 'authorise/recover/confirm'],

    // Columns shown on torrent tables
    'TORRENTTABLE_COLUMNS' => 'category,name,dl,magnet,uploader,tube,tmdb,comments,nfo,size,completed,seeders,leechers,health,external,added,speed,wait,rating,admin',

    'PopularCats' => [1],
    'MovieCats'   => ['Film'],
    'SerieCats'   => ['TV'],

    'FORUMS'                   => true,
    'FORUMS_GUESTREAD'         => true,
    'FORUMONINDEX'             => true,
    'LATESTFORUMPOSTONINDEX'   => true,
    'SHOUTBOX'                 => true,
    'NEWSON'                   => true,
    'DISCLAIMERON'             => true,
    'YOU_TUBE'                 => true,
    'FORCETHANKS'              => false,
    'ALLOWLIKES'               => true,

    // -----------------------------------------------------------------
    // TMDB / OMDB
    // -----------------------------------------------------------------
    '_TMDBAPIKEY' => 'Place_Key',
    '_TMDBLANG'   => 'en',
    '_TMDBDEBUG'  => false,
    '_TMDBswitch' => true,
    '_OMDBAPIKEY' => 'Place_Key',
    '_OMDBswitch' => true,

    // -----------------------------------------------------------------
    // Freeleech / bonus / economy
    // -----------------------------------------------------------------
    'FREELEECHGBON'  => false,
    'FREELEECHGB'    => 8589934592,
    'CURRENCYSYMBOL' => '£',
    'BONUSPERTIME'   => 0.1,
    'ADDBONUS'       => 3600,

    // -----------------------------------------------------------------
    // Ratio wait rules (download wait by class/stats)
    // -----------------------------------------------------------------
    'CLASS_WAIT' => 1,
    'GIGSA'  => 1,  'RATIOA' => 0.5,  'A_WAIT' => 24,
    'GIGSB'  => 3,  'RATIOB' => 0.65, 'B_WAIT' => 12,
    'GIGSC'  => 5,  'RATIOC' => 0.8,  'C_WAIT' => 6,
    'GIGSD'  => 7,  'RATIOD' => 0.95, 'D_WAIT' => 2,

    // -----------------------------------------------------------------
    // Ratio warnings
    // -----------------------------------------------------------------
    'RATIOWARNENABLE'     => true,
    'RATIOWARNMINRATIO'   => 0.4,
    'RATIOWARN_MINGIGS'   => 4,
    'RATIOWARN_DAYSTOWARN'=> 14,

    // -----------------------------------------------------------------
    // Hit & Run
    // -----------------------------------------------------------------
    'HNR_ON'       => false,
    'HNR_DEADLINE' => 7,
    'HNR_SEEDTIME' => 48,
    'HNR_WARN'     => 5,
    'HNR_STOP_DL'  => 15,
    'HNR_BAN'      => 50,

    // -----------------------------------------------------------------
    // Cache / cleanup / retention
    // -----------------------------------------------------------------
    'CACHE_TYPE'    => 'disk', // disk | memcache
    'MEMCACHE_HOST' => 'localhost',
    'MEMCACHE_PORT' => 11211,
    'AUTOCLEANINTERVAL'     => 600,
    'MAXDEADTORRENTTIMEOUT' => 21600,
    'LOGCLEAN'              => 28, // days

    // -----------------------------------------------------------------
    // Mail
    // -----------------------------------------------------------------
    'mail_type'      => 'php', // php | smtp
    'mail_smtp_host' => 'localhost',
    'mail_smtp_port' => 25,
    'mail_smtp_ssl'  => false,
    'mail_smtp_auth' => false,
    'mail_smtp_user' => '',
    'mail_smtp_pass' => '',
]);