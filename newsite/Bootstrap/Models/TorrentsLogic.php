<?php

namespace Bootstrap\Models;

use PDO;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\Thumbnail;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\CatagoriesLogic;
use Bootstrap\Models\LikesLogic;
use Bootstrap\Models\LogsLogic;
use Bootstrap\Models\MessagesLogic;
use Bootstrap\Models\RatingsLogic;
use Bootstrap\Models\ReportsLogic;
use Bootstrap\Models\SnatchedLogic;
use Bootstrap\Models\TagsLogic;
use Bootstrap\Models\TmdbLogic;
use Bootstrap\Models\TorrentTagLogic;
use Bootstrap\Models\TorrentLangLogic;

// Torrent Search
function search()
{
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $cats = (int) isset($_GET['cat']) ? $_GET['cat'] : 0;
    $incldead = (int) isset($_GET['incldead']) ? $_GET['incldead'] : 0;
    $freeleech = (int) isset($_GET['freeleech']) ? $_GET['freeleech'] : 0;
    $inclexternal = (int) isset($_GET['inclexternal']) ? $_GET['inclexternal'] : 0;
    $lang = (int) isset($_GET['language']) ? $_GET['language'] : 0;
    
    $url = "?"; // assign url
    $wherea = []; // assign conditions
    $params = []; // assign vars
    // browse
    $cats = (int) (isset($_GET['cat'])) ? $_GET['cat'] : 0;
    $parent_cat = (isset($_GET['parent_cat'])) ? $_GET['parent_cat'] : 0;

    if (!$keyword == '') {
        $keys = explode(" ", $keyword);
        foreach ($keys as $k) {
            $ssa[] = " torrents.name LIKE '%$k%' ";
        }
        $wherea[] = '(' . implode(' OR ', $ssa) . ')';
        $url .= "keyword=" . urlencode($keyword) . "&";
    }

    if (!$cats == 0) {
        $wherea[] = "category = $cats";
        $url .= "cat=" . urlencode($cats) . "&";
    }

    if (!$parent_cat == 0) {
        $wherea[] = "categories.parent_cat = $cats";
        $url .= "parent_cat=" . urlencode($cats) . "&";
    }

    if ($incldead == 1) {
        $url .= "incldead=1&";
    } elseif ($incldead == 2) {
        $params[] = 'no';
        $wherea[] = "visible = ?";
        $url .= "incldead=2&";
    } else {
        $params[] = 'yes';
        $wherea[] = "visible = ?";
    }

    if ($freeleech == 1) {
        $params[] = 0;
        $wherea[] = "freeleech = ?";
        $url .= "freeleech=1&";
    } elseif ($freeleech == 2) {
        $params[] = 1;
        $wherea[] = "freeleech = ?";
        $url .= "freeleech=2&";
    }

    if ($inclexternal == 1) {
        $params[] = 'no';
        $wherea[] = "external = ?";
        $url .= "inclexternal=1&";
    } elseif ($inclexternal == 2) {
        $params[] = 'yes';
        $wherea[] = "external = ?";
        $url .= "inclexternal=2&";
    }

    if ($lang) {
        $params[] = $lang;
        $wherea[] = "torrentlang = ?";
        $url .= "lang=" . urlencode($lang) . "&";
    }

    $where = implode(' AND ', $wherea);

    // browse
    $wherecatina = array();
    $wherecatin = "";
    $categories_query = "SELECT id FROM `categories`";
    $res = DB::run($categories_query);
    foreach ($res as $row) {
        if (isset($_GET["c$row[id]"])) {
            $wherecatina[] = $row["id"];
            $url .= "c$row[id]=1&";
        }
        $wherecatin = implode(", ", $wherecatina);
    }

    if ($wherecatin) {
        $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";
    }

    if ($where != '') {
        $where = 'WHERE ' . $where;
    }
    $sort = $_GET['sort'] ?? '';
    $order = $_GET['order'] ?? '';
    switch ($sort) {
        case 'id':$column = "id";
            break;
        case 'name':$column = "name";
            break;
        case 'comments':$column = "comments";
            break;
        case 'size':$column = "size";
            break;
        case 'times_completed':$column = "times_completed";
            break;
        case 'seeders':$column = "seeders";
            break;
        case 'leechers':$column = "leechers";
            break;
        case 'category':$column = "category";
            break;
        default:$column = "id";
            break;
    }

    switch ($order) {
        case 'asc':$ascdesc = "ASC";
            break;
        case 'desc':$ascdesc = "DESC";
            break;
        default:$ascdesc = "DESC";
            break;
    }

    $orderby = 'ORDER BY torrents.' . $column . ' ' . $ascdesc;
    $pagerlink = "sort=" . $column . "&amp;order=" . strtolower($ascdesc) . "&amp;";

    $sortmod = [
        'orderby' => $orderby, 'pagerlink' => $pagerlink,
        'column' => $column, 'by' => $ascdesc
    ];
    $orderby = 'ORDER BY torrents.' . $sortmod['column'] . ' ' . $sortmod['by'];
    $pagerlink = $sortmod['pagerlink'];

    $array = [
        'where'=>$where,
        'keyword'=>$keyword,
        'orderby'=>$orderby,
        'params'=>$params,
        'url'=>$url,
        'pagerlink'=>$pagerlink,
        'parent_cat' => $parent_cat,
        'wherecatina' => $wherecatina,
    ];
    return $array;
}

/*
* Torrent Table Data
*/
class TorrentsLogic
{
    // Fetch Torrent Details
    public static function updateset($updateset, $id)
    {
		$stmt = DB::updateset("torrents", $updateset, ['id' => $id]);
        return $stmt;
	}

    // Fetch Torrent Details
    public static function fetchIdDetails($id, $current_user, $action)
    {
        $stmt = array();
        $torrents_query = 
            "SELECT torrents.anon, torrents.seeders, torrents.tube, 
                    torrents.banned, torrents.leechers, torrents.info_hash, 
                    torrents.filename, torrents.nfo, torrents.last_action, 
                    torrents.numratings, torrents.name, torrents.tmdb, 
                    torrents.imdb, torrents.owner, torrents.save_as, torrents.descr, 
                    torrents.visible, torrents.size, torrents.added, torrents.views, 
                    torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.torrentlang,
                    torrents.external, torrents.image1, torrents.image2, torrents.announce, 
                    torrents.numfiles, torrents.freeleech, torrents.vip, torrents.category, torrents.sticky,
                    torrents.announcelist, torrents.filelist,
                    IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, 
                    torrents.numratings,
                    categories.id AS cat_id,
                    categories.name AS cat_name, 
                    torrentlang.name AS lang_name, 
                    torrentlang.image AS lang_image, 
                    categories.parent_cat as cat_parent, 
                    users.username, users.privacy 
            FROM torrents 
            LEFT JOIN categories ON torrents.category = categories.id 
            LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id 
            LEFT JOIN users ON torrents.owner = users.id 
            WHERE torrents.id=?";
        $stmt = DB::run($torrents_query,
            [$id])->fetch();
        if (!$stmt || ($stmt["banned"] == "yes")) {
            message(URLROOT, LANG["TORRENT_NOT_FOUND"]);
        }
        // Torrent
        $torrent = $stmt;
        // Name
        $name = $stmt["name"];
        $views = $stmt['views'];

        // TMDB
        $type = '';
        $image = '';
        if (!empty($stmt["imdb"])) {
            $item_id = $stmt["imdb"];
            $type = 'imdb';
        } elseif (!empty($stmt["tmdb"]) && in_array($stmt["cat_parent"], CONFIG['SerieCats'])) {
            $item_id = $stmt["tmdb"];
            $type = 'tv';
        } elseif (!empty($stmt["tmdb"]) && in_array($stmt["cat_parent"], CONFIG['MovieCats'])) {
            $item_id = $stmt["tmdb"];
            $type = 'film';
        }
        if ($type == 'tv' || $type == 'film' || $type == 'imdb') {
            $tmdb1 = TmdbLogic::createMDB($stmt['id'], $item_id, $type);
            $image = "<img src='" . data_uri($tmdb1['link'], $stmt['tmdb'] . '.webp') . "' height='250' width='100%' />";
        }

        // Image
        if ($image == "" && $stmt["image1"] != "") {
            $image = "<img src='" . data_uri(IMAGE_PATH . "/" . $stmt["image1"], $stmt['image1']) . "' height='250' width='100%' />";
        }

        /*
        // Video
        if (array_key_exists('video', $tmdb1)) {
            $video = $tmdb1['video'];
            unset($tmdb1['video']);
        }
        */
        // Thanks/Likes
        $thanks = LikesLogic::showThanks($stmt['id']);
        $likes = LikesLogic::showLikes($stmt);
        // Tags
        $$torrents_quertag_query = "SELECT type1, type2, type3 FROM `torrenttag` WHERE torrentid=?";
        $tag = DB::run($torrenttag_query, [$value])->fetch(PDO::FETCH_ASSOC);
        if ($tag) {
            $tags = get_tags($value);
        } else {
            $tags = '---';
        }
        // Html
        $htl['shortname'] = CutName(htmlspecialchars($stmt["name"]), 40);
        $html['taglist'] = TagsLogic::getAll();
        $html['catdrop'] = CatagoriesLogic::dropdown($stmt['cat_id']);
        $html['torrentlang'] == null ? 'unknown' : $stmt['torrentlang'];
        $html['ratings'] = RatingsLogic::ratingTor($stmt['id'], $data->id, $stmt['rating']);
        $html['externaltorrent'] = $stmt["external"] == 'yes' ? "<a type='#' class='model_form ttbtn btn-sm' data-bs-toggle='modal' data-bs-target='#trackerModal'>". LANG["TRACKERS"] ."</a>" : LANG['LOCAL'];
        $html['nfo'] = $stmt["nfo"] == "yes" ? "<a href='#' data-bs-toggle='modal' data-bs-target='#nfoModal'><i class='fa fa-file fa-1x' title='". LANG["NFO"] ."'></i></a>" : LANG['NOTHING_FOUND'];
        $html['langname'] = empty($stmt["lang_name"]) ? "Unknown/NA" : "<img src='" .url("page/theme/default/images/languages/$stmt[lang_image]") . "' alt='" . $stmt["lang_name"] . "' width='20' height='20'/>";
        $html['lang'] = TorrentLangLogic::langList($stmt['torrentlang']);
        $html['vip1'] = ($stmt["vip"] == "yes") ? 1 : 0;
        $html['freeleech1'] = $stmt["freeleech"] == 1 ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
        // Calculate local torrent speed test
        if ($stmt["leechers"] >= 1 && $stmt["seeders"] >= 1 && $stmt["external"] != 'yes') {
            $torrents_query = "SELECT (SUM(p.downloaded)) / (UNIX_TIMESTAMP('" . CONFIG['TT_DATE'] . "') - UNIX_TIMESTAMP(added)) AS totalspeed FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND p.torrent = '$stmt[id]' GROUP BY t.id ORDER BY added ASC LIMIT 15";
            $speedQ = DB::run($torrents_query);
            $a = $speedQ->fetch(PDO::FETCH_ASSOC);
            $html['totalspeed'] = mksize($a["totalspeed"]) . "/s";
        } else {
            $html['totalspeed'] = LANG["NO_ACTIVITY"];
        }

        // Scraper
        $ts = modify('date', $stmt['last_action'], '+2 day');
        $scraper = $ts > CONFIG['TT_DATE'] ? 
                    "<b>Stats Recently Updated</b>" : 
                    "<form action='" . URLROOT . "/scrape/external?id=" . $stmt['id'] . "' method='post'>
                    <button type='submit' class='btn ttbtn center-block' value=''>" . LANG["UPDATE_STATS"] . "</button>
                    </form>";

        $data = [
            'torrent' => $torrent,
            'name' => $name,
            'views' => $views,
            'tmdb1' => $tmdb1,
            'image' => $image,
            'video' => $video,
            'likes' => $likes,
            'thanks' => $thanks,
            'tags' => $tags,
            'html' => $html,
            'scraper' => $scraper
        ];

        if ($current_user->view_torrents != "yes" && CONFIG['MEMBERSONLY'] && $torrent['owner'] != $id) {
            message(URLROOT, LANG["NO_TORRENT_VIEW"]);
        }

        // Add Hit
        if ($action == 'hit')
        {
            $pvkey_var = "t_" . $id;
            $views = $_SESSION[$pvkey_var] == 1 ? $torrent["views"] + 0 : $torrent["views"] + 1;
            $_SESSION[$pvkey_var] = 1;
            $torrents_query = "UPDATE `torrents` SET views = ? WHERE id=?";
            DB::run($torrents_query, [$views, $id]); 
            header("Location: " . "torrent/$id", true, 302);//redirect(); todo
        }

        // Add Bump
        if ($action == 'bump') {
            $torrents_query = "UPDATE `torrents` SET added = ? WHERE id=?";
            DB::run($torrents_query, [CONFIG['TT_DATE'], $id]);
            message("torrent/$id", LANG["ACTION_COMPLETE"]);
        }

        return $data;
    }

    // Fetch Torrent Details
    public static function submitIdDetails($id, $current_user)
    {
        // Get Torrent Data
        $torrents_query = "SELECT owner,image2,image1 FROM `torrents` WHERE id=? ";
        $row = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);

        if ($current_user->control_panel == "no" && $current_user->id != $id) {
            message(URLROOT, LANG["NO_PERMISSION"]);
        }

        $updateset = array();

        // NFO
        $nfoaction = Input1('nfoaction');
                    if ($nfoaction == "update") {
                        $nfofile = $_FILES['nfofile'];
                        if (!$nfofile) {
                            die("No data " . var_dump($_FILES));
                        }
                        if ($nfofile['size'] > 65535) {
                            message("torrent/$id", "NFO is too big! Max 65,535 bytes.");
                        }
                        $nfofilename = $nfofile['tmp_name'];
                        if (@is_uploaded_file($nfofilename) && @filesize($nfofilename) > 0) {
                            @move_uploaded_file($nfofilename, NFOS_PATH . "/$id.nfo");
                            $updateset['nfo'] = 'yes';
                        } //success
                    } elseif ($nfoaction == "delete") {
                        unlink(NFOS_PATH . "/$id.nfo");
                        $updateset['nfo'] = 'no';
                    }
        
                    if (!empty($_POST["name"])) {
                        $updateset['name'] = $_POST["name"];
                    }
        
                    $updateset['tmdb'] = $_POST["tmdb"];
                    $updateset['imdb'] = $_POST["imdb"];
                    $updateset['descr'] = $_POST["descr"];
                    $updateset['category'] = (int) $_POST["type"];
                    $updateset['tube'] = $_POST['tube'] ?? '';
                    $updateset['vip'] = $_POST["vip"] ? "yes" : "no";
                    $updateset['anon'] = $_POST["anon"] ? "yes" : "no";
                    $updateset['torrentlang'] = (int) $_POST["language"] ?? 0;
        
                    if ($current_user->class > CONFIG['_MODERATOR']) {
                        $updateset['sticky'] = $_POST["sticky"] == 'yes' ? 'yes' : 'no';
                    }
        
                    if ($current_user->edit_torrents == 'yes') {
                        $updateset['freeleech'] = $_POST["freeleech"] ? 1 : 0;
                    }
        
                    if ($current_user->edit_torrents == 'yes') {
                        if ($_POST["banned"]) {
                            $updateset['banned'] = 'yes';
                            $_POST['visible'] = 'no';
                        } else {
                            $updateset['banned'] = 'no';
                        }
                    }
        
                    $updateset['visible'] = $_POST["visible"] ? "yes" : "no";
        
                    $tags = $_POST['tags'];
                    if ($tags) {
                        $torrenttag_query = "INSERT INTO `torrenttag` (torrentid, type1, type2, type3) VALUES (?,?,?,?)";
                        DB::run($torrenttag_query, [$id, $tags[0], $tags[1], $tags[2]]);
                    }
        
                    // Images
                    $img1action = Input1('img1action');
                    if ($img1action == "update") {
                        $updateset['image1'] = Thumbnail::create()->uploadimage(0, $row["image1"], $id);
                    }
        
                    if ($img1action == "delete") {
                        if ($row['image1']) {
                            unlink(IMAGE_PATH . "/$row[image1]");
                            $updateset['image1'] = '';
                        }
                    }
        
                    $img2action = Input1('img2action');
                    if ($img2action == "update") {
                        $updateset['image2'] = Thumbnail::create()->uploadimage(1, $row["image2"], $id);
                    }
        
                    if ($img2action == "delete") {
                        if ($row['image2']) {
                            unlink(IMAGE_PATH . "/$row[image2]");
                            $updateset['image2'] = '';
                        }
                    }
        
                    // Update
                    TorrentsLogic::updateset($updateset, $id);
                    LogsLogic::write("Torrent $id (" . htmlspecialchars($_POST["name"]) . ") was edited by " . $current_user->username . "");
                    message("torrent/$id", LANG['SUCCESS']);
                    


        return $data;
    }

    // Fetch Torrent Details
    public static function delete($current_user)
    {
        // Check User Input
        $torrentid = Input1('id', 'id');
        $delreason = Input1('delreason');
        $torrentname = Input1('torrentname');
        if (!$current_user->delete_torrents == 'yes') {
            message("torrent/$torrentid", LANG["NO__PERMISSION"]);
        }
        if (!intval($torrentid)) {
            message("torrent/delete?id=$torrentid", LANG["MISSING_ID"]);
        }
        if (!$delreason) {
            message("torrent/delete?id=$torrentid", LANG["MISSING_FORM_DATA"]);
        }

        // Delete Torrent
        $torrent_query = "SELECT name,owner FROM `torrents` WHERE id=?";
        $owner = DB::run($torrent_query, [$torrentid])->fetch(PDO::FETCH_ASSOC);
        TorrentsLogic::deleteTorrent($torrentid, $current_user->id);
        LogsLogic::write($current_user->username . " has deleted torrent: ID:$torrentid - " . htmlspecialchars($torrentname) . " - Reason: " . htmlspecialchars($delreason));
        unlink(TORRENT_PATH . "/$torrentid.torrent");

        if ($current_user->id=== $owner['owner']) {
            $msg_shout = "Your torrent " . $torrentname . " has been deleted by " . $current_user->username . "/nReason: " . $_POST["delreason"] . "";
            MessagesLogic::newinsert(['sender' => 0, 'receiver' => $owner['owner'], 'added' => CONFIG['TT_DATE'], 'subject' => 'Torrent Deleted', 'msg' => $msg_shout, 'unread' => 'yes', 'location' => 'in']);
        }

        message("peer/view/seeding/$owner[owner]", htmlspecialchars($torrentname) . " " . LANG["DEL_ERROR"]);
    }
    // Update Comments
    public static function updateComments($id, $var)
    {
        if ($var == 'sub') {
            $$torrents_query = "SELECT comments FROM `torrents` WHERE id=? ";
            $row = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);
            $torrents_query = "UPDATE torrents SET comments = $row[comments] - 1 WHERE id = $id";
            DB::run($torrents_query);
        } elseif ($var == 'add') {
            $torrents_query = "UPDATE torrents SET comments = comments + 1 WHERE id = $id";
            DB::run($torrents_query);
        }
    }

    // Function To Delete A Torrent
    public static function deleteTorrent($id, $current_userid)
    {
        $torrents_query = "SELECT image1,image2 FROM `torrents` WHERE id=? ";
        $row = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);
        foreach (explode(".", "peers.comments.ratings.files") as $x) {
            $torrents_query = "DELETE FROM $x WHERE torrent' = ?";
            DB::run($torrents_query, [$id]);
        }

        if (file_exists(TORRENT_PATH . "/$id.torrent")) {
            unlink(TORRENT_PATH . "/$id.torrent");
        }
        if ($row["image1"]) {
            unlink(IMAGE_PATH . "/" . $row["image1"]);
        }
        if ($row["image2"]) {
            unlink(IMAGE_PATH . "/" . $row["image2"]);
        }
        @unlink(NFOS_PATH . "/$id.nfo");
        $torrents_query = "DELETE FROM torrents WHERE id = ?";
        DB::run($torrents_query, [$id]);
        $reports_query = "DELETE FROM `reports` WHERE votedfor = ? AND type=?";
        DB::run($reports_query, [$id, 'torrent']);
        $snatched_query = "DELETE FROM snatched WHERE tid = ?";
        DB::run($snatched_query, [$id]);
        $likes_query = "DELETE FROM likes WHERE liked' = ? AND type=?";
        DB::run($likes_query, [$id, 'torrent']);
        
        LogsLogic::write("Torrent ID $id was deleted by ".$current_userid."");
    }

    // Browse Page
    public static function countBrowse()
    { 
       // Search Torrent ($_GET)
        $search = search();

        $catsquery = CatagoriesLogic::getCatByParent();
        $torrents_query = "SELECT COUNT(*) FROM torrents LEFT JOIN categories ON category = categories.id $search[where]";
        $count = DB::run($torrents_query, $search['params'])->fetchColumn();
        list($pagerbuttons, $limit) = pagination(25, $count, URLROOT . "/browse" . $search['url'] . $search['pagerlink']);

        $torrents_query = 
                    "SELECT torrents.id, torrents.anon, torrents.announce, torrents.category, torrents.sticky, 
                    torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, 
                    torrents.tube, torrents.tmdb, torrents.imdb, torrents.size, torrents.added, torrents.comments, torrents.numfiles, 
                    torrents.filename, torrents.owner, torrents.external, torrents.freeleech, 
                    categories.name AS cat_name,
                    categories.parent_cat AS cat_parent, 
                    categories.image AS cat_pic,
                    users.username, users.privacy, 
                    IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating 
                    FROM torrents 
                    LEFT JOIN categories 
                    ON category = categories.id 
                    LEFT JOIN users 
                    ON torrents.owner = users.id 
                    $search[where] 
                    $search[orderby] 
                    $limit";
                    $res = DB::run($torrents_query,
                    $search['params']
                );

        $cats = CatagoriesLogic::genreList();
        $lang = TorrentLangLogic::langList();
        $categories_query = "SELECT * FROM `categories` ORDER BY parent_cat, name";
        $cats = DB::run($categories_query);

        $title = $search['keyword'] == '' ? LANG["SEARCH"] :LANG["SEARCH_RESULTS_FOR"] . " \"" . htmlspecialchars($search['keyword']) . "\"";
                
        // Get Cat Data
        return array('title' => $title, 'res' => $res, 'pagerbuttons' => $pagerbuttons, 'catsquery' => $catsquery, 'count' => $count, 'search' => $search, 'cats' => $cats, 'lang' => $lang);
    }

    public static function reseed($id, $current_user)
    {
        if (isset($_COOKIE["reseed$id"])) {
            message(URLROOT, LANG["RESEED_ALREADY_ASK"]);
        }

        // Check Torrent
        $torrents_query = "SELECT owner,banned,external FROM `torrents` WHERE id=?";
        $row = DB::run($torrents_query, [$id])->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row["banned"] == "yes") {
            message(URLROOT, LANG["TORRENT_NOT_FOUND"]);
        } elseif ($row["external"] == "yes") {
            message(URLROOT, LANG["EXTERNAL"]);
        }

        // Send Message
        $res2 = SnatchedLogic::completed($id);
        $message = sprintf(LANG['RESEED_MESSAGE'], $current_user->username, URLROOT, $id);
        foreach ($res2 as $row2) {
            MessagesLogic::newinsert(['subject' => LANG["NEW_RESEED"], 'sender' => $current_user->id, 'receiver' => $row2['id'], 'added' => CONFIG['TT_DATE'], 'msg' => $message]);
        }
        if ($row["owner"] && $row["owner"] != $current_user->id) {
            MessagesLogic::newinsert(['subject' => 'Torrent Reseed Request', 'sender' => $current_user->id, 'receiver' => $row['owner'], 'added' => CONFIG['TT_DATE'], 'msg' => $message]);
        }

        setcookie("reseed$id", $id, CONFIG['TT_TIME']+60*60*24*30);
        message(URLROOT, LANG["RESEED_SENT"]);
    }
    
    // Search Torrent  todo blocks&views below
    public static function popular($tcat)
    {
        $torrents_query = "SELECT torrents.id, torrents.name, torrents.image1, torrents.image2, torrents.poster, torrents.tmdb, torrents.imdb, categories.name as cat_name, categories.parent_cat as cat_parent FROM torrents LEFT JOIN categories ON torrents.category=categories.id 
		WHERE banned = 'no' AND visible = 'yes' AND category IN ($tcat) ORDER BY torrents.seeders + torrents.leechers DESC LIMIT 10";
        $row = DB::run($torrents_query)->fetchAll();
        return $row;
    }

    // Get All User Array
    public static function getAll($where = false, $values = '', $pagination = false)
    {
        if (!$where) {
            $where = "WHERE torrents.visible = ?";
            $values = array('yes');
        }

        $torrents_query = "SELECT COUNT(*) FROM torrents $where";
        $count = DB::run($torrents_query, $values)->fetchColumn();
        if ($pagination) {
            list($pager, $limit) = pagination(25, $count, URLROOT . "/$pagination&amp;");
        } else {
            $limit = 'LIMIT 20';
            $pager = '';
        }
        $torrents_query = 
            "SELECT torrents.anon, torrents.seeders, torrents.tube, 
                    torrents.banned, torrents.leechers, torrents.info_hash, 
                    torrents.filename, torrents.nfo, torrents.last_action, 
                    torrents.numratings, torrents.name, torrents.tmdb,  torrents.comments,
                    torrents.imdb, torrents.owner, torrents.save_as, torrents.descr, 
                    torrents.visible, torrents.size, torrents.added, torrents.views, 
                    torrents.hits, torrents.times_completed, torrents.id, torrents.type, 
                    torrents.external, torrents.image1, torrents.image2, torrents.poster, torrents.announce, 
                    torrents.numfiles, torrents.freeleech, torrents.vip, torrents.category, torrents.sticky,
                    torrents.announcelist, torrents.filelist,
                    IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, 
                    torrents.numratings, 
                    categories.name AS cat_name, 
                    categories.image AS cat_pic,
                    torrentlang.name AS lang_name, 
                    torrentlang.image AS lang_image, 
                    categories.parent_cat as cat_parent, 
                    users.username, users.privacy 
            FROM torrents 
            LEFT JOIN categories ON torrents.category = categories.id 
            LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id 
            LEFT JOIN users ON torrents.owner = users.id
             $where $limit";
             
            $stmt = DB::run($torrents_query,
            $values
        )->fetchAll(PDO::FETCH_ASSOC);

        $array = [
            'stmt' => $stmt,
            'pager' => $pager,
            'count' => $count
        ];

        return $array;
    }

}