<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Thumbnail;
use Bootstrap\Libs\Validate;
use Bootstrap\Libs\Torrents\Parse;
use Bootstrap\Models\LogsLogic;
use Bootstrap\Models\ShoutboxLogic;
use Bootstrap\Models\TorrentsLogic;
use Bootstrap\Models\TorrentTagLogic;

/*
* Users Table Data
*/
class UploadLogic
{
    public $current_user;
    
    // Fetch Torrent Details
    public static function torrent($current_user)
    {
     // Check Form Data.
     if (!isset($_POST['type'], $_POST['name'], $_FILES['torrent'])) {
        $message = LANG['MISSING_FORM_DATA'];
    }
    // Upload File
    if (($num = $_FILES['torrent']['error'])) {
        message('upload', LANG["UPLOAD_ERR[$num]"]);
    }
    // Get Name
    if (!($fname = $_FILES["torrent"]["name"])) {
        $message = LANG["EMPTY_FILENAME"];
    }
    // Check File Name
    if (!preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $fname)) {
        $message = LANG["UPLOAD_INVALID_FILENAME"];
    }
    // Check File Torrent
    if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches)) {
        $message = LANG["NOT_TORRENT"];
    }
    // Check NFO
    $nfo = 'no';
    if ($_FILES['nfo']['size'] != 0) {
        $nfofile = $_FILES['nfo'];
        if ($nfofile['name'] == '') {
            $message = LANG["NO_NFO_UPLOADED"];
        }
        if (!preg_match('/^(.+)\.nfo$/si', $nfofile['name'], $fmatches)) {
            $message = LANG["UPLOAD_NOT_NFO"];
        }
        if ($nfofile['size'] == 0) {
            $message = LANG["NO_NFO_SIZE"];
        }
        if ($nfofile['size'] > 65535) {
            $message = LANG["NFO_UPLOAD_SIZE"];
        }
        $nfofilename = $nfofile['tmp_name'];
        if (($num = $_FILES['nfo']['error'])) {
            $message = LANG["UPLOAD_ERR[$num]"];
        }
        $nfo = 'yes';
    }
    // Check User Inputs
    $descr = Input1('descr') ?? LANG["UPLOAD_NO_DESC"];
    $vip = Input1('vip', 'int');
    $free = Input1('free', 'int');

    $tube = Input1('tube');
    $tmdb = Input1('tmdb');
    $imdb = Input1('imdb');
    $anon = (isset($_POST['anonycheck'])) ? 'yes' : 'no';
    $langid = Input1('language', 'int');
    $catid = Input1('type', 'int');
    // Check Cat Selected
    if (!intval($catid)) {
        $message = LANG["UPLOAD_NO_CAT"];
    }
    // Shorten Name
    if (!empty($_POST['name'])) {
        $name = Input1('name');
    }
    // If Message Show
    if ($message) {
        message('upload', $message);
    }

    // No Message So Continue
    if (!$message) {
        // Torrent / NFO Dir
        $torrent_dir = TORRENT_PATH;
        $nfo_dir = NFOS_PATH;

        // Move Torrent File
        if (!move_uploaded_file($_FILES["torrent"]["tmp_name"], "$torrent_dir/$fname")) {
            message('upload', LANG["ERROR"] . ": " . LANG["UPLOAD_COULD_NOT_BE_COPIED"] . " $torrent_dir - $fname");
        }

        // Parse Torrent File
        $tor = Parse::torr("$torrent_dir/$fname");

        // Check External
        $announce_urls = explode(",", URLROOT . CONFIG['ANNOUNCELIST']);
        if (!in_array($tor['announce'], $announce_urls)){
            $external='yes';
        }else{
            $external='no';
        }
        if (!CONFIG['ALLOWEXTERNAL'] && $external == 'yes') {
            $message = LANG["UPLOAD_NO_TRACKER_ANNOUNCE"];
        }
        if ($external === 'yes') {
            $announcelist = serialize($tor["announce-list"]);
        } else {
            $announcelist = serialize([$tor["announce"]]);
        }

    }

    // Error So Delete File
    if ($message) {
        unlink("$torrent_dir/$fname");
        message('upload', $message);
    }

    // Check Name
    if ($name == "") {
        $name = $tor['name'];
    }
    $name = str_replace(".torrent", "", $name);
    $name = str_replace("_", " ", $name);

    // Upload Images
    $allowed_types = CONFIG['ALLOWEDIMAGETYPES'];
    $inames = array();
    for ($x = 0; $x < 2; $x++) {
        if (!($_FILES['image' . $x]['name'] == "")) {
            $y = $x + 1;
            if ($_FILES['image$x']['size'] > CONFIG['IMAGEMAXFILESIZE']) {
                message('upload', LANG["INVAILD_FILE_SIZE_IMAGE"]);
            }
            $uploaddir = IMAGE_PATH;
            $ifile = $_FILES['image' . $x]['tmp_name'];
            $im = getimagesize($ifile);
            if (!$im[2]) {
                message('upload', sprintf(LANG["INVALID_IMAGE"]));
            }
            if (!array_key_exists($im['mime'], $allowed_types)) {
                message('upload', LANG["INVALID_FILETYPE_IMAGE"]);
            }
            $_query = "SHOW TABLE STATUS LIKE 'torrents'";
            $row = DB::run($_query)->fetch();
            $next_id = $row['Auto_increment'];
            $ifilename = $next_id . $x . $allowed_types[$im['mime']];
            Thumbnail::create()->copyImage($ifile, $uploaddir . $ifilename);
            $inames[] = $next_id . $x . '.webp';
        }
    }

    // Insert Torrent
    try {
        $torrents_query = "INSERT INTO `torrents`
            (filename, owner, name, vip, descr, image1, image2, category, 
            tube, added, info_hash, size, numfiles, save_as,
            announceannounce, external, nfo, torrentlang, anon, last_action, freeleech,
            tmdb, imdb, announcelist, filelist, info_hash2)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        DB::run($torrents_query,
            [$fname, $current_user->id, $name, $vip, $descr, $inames[0], $inames[1], $catid, 
            $tube,CONFIG['TT_DATE'], $tor['hash'], $tor['length'], (int) $tor['ttfilecount'], $fname,
            $tor['announce'], $external, $nfo,$langid, $anon, CONFIG['TT_DATE'], $free,
            $tmdb, $imdb, $announcelist, $tor['files'], $tor['hash2']]);
    } catch (\PDOException $e) {
        TorrentsLogic::deleteTorrent(DB::lastInsertId(), $current_user->id);
        rename("$torrent_dir/$fname", "$torrent_dir/duplicate.torrent"); // todo
        message('upload', LANG['ACTION_FAILED']);
    }

    $id = DB::lastInsertId();

    // Rename To INT
    rename("$torrent_dir/$fname", "$torrent_dir/$id.torrent");

    // Tags Mod
    $tags = $_POST['tags'];
    if ($tags) {
        $$torrenttag_query = "INSERT INTO `torrenttag` (torrentid, type1, type2, type3) VALUES (?,?,?,?)";
        DB::run($torrenttag_query, [$id, $tags[0], $tags[1], $tags[2]]);
    }

    // Move NFO
    if ($nfo == 'yes') {
        move_uploaded_file($nfofilename, "$nfo_dir/$id.nfo");
    }

    // Log Upload
    LogsLogic::write(sprintf(LANG["TORRENT_UPLOADED"], htmlspecialchars($name), $current_user->username));
    
    // Shout Torrent
    $msg_shout = "New Torrent: [url=" . URLROOT . "/torrent/" . $id . "]" . $name . "[/url] has been uploaded " . ($anon == 'no' ? "by [color=".$current_user->Color."]" . $current_user->username . "[/color]" : "") . "";
    $shoutbox_query = "INSERT INTO `shoutbox` (userid, date,user, message) VALUES (?,?,?,?)";
    DB::run($shoutbox_query, [0, CONFIG['TT_DATE'], 'System', $msg_shout]);

    //Uploaded ok message
    if ($external == 'no') {
        $message = sprintf(LANG["TORRENT_UPLOAD_LOCAL"], $name, $id, $id);
    } else {
        $message = sprintf(LANG["TORRENT_UPLOAD_EXTERNAL"], $name, $id);
        // Scrape External
        Parse::ScrapeId($id, $tor["announce-list"], $tor['hash']);
    }

    message("torrent/$id", $message);
	}
}