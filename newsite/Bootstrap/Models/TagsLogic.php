<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use PDO;

/*
*  Tags Table Data
*/
class TagsLogic
{
  // Get Tag Names
  public static function getAll()
  {
    $tags_query = "SELECT id, name, colour FROM `tags` GROUP BY `name`";
    $stmt = DB::run($tags_query);
    return $stmt;
  }

  // Admin Add/Update Tag
  public static function insertOrUpdate()
  {
    $name = Input1('name');
    $colour = Input1('colour');
    $id = Input1('id', 'int');

    if (!$name) {
      message('admintag', LANG["MISSING_FORM_DATA"]);
    }

    if (!$id) {
      $tags_query = "INSERT INTO tags (name,colour) VALUES (?,?)";
      DB::run($tags_query, [$name, $colour]);
      message('admintag', LANG['SUCCESS']);
    } else {
      $tags_query = "UPDATE tags SET name = ? AND colour = ? WHERE id=?";
      DB::run($tags_query, [$name,$colour,$id]);
      message('admintag', LANG['SUCCESS']);
    }
    
    message('admintag', LANG["ACTION_COMPLETE"]);
  }

  // Admin Delete Tag
  public static function delete()
  {
    // Check User Input
    $ids = multiIds('ids');
    $tags_query = "DELETE FROM `tags` WHERE `id` IN (?)";
    DB::run($tags_query, [$ids]);
    message('admintag', LANG["DEL_COMPLETE"]);
  }

  // Search By Tag
  public static function search($name)
  {
    // Get Tag Data
    $tagtorrent_query = "SELECT torrentid FROM tagtorrent WHERE type1 = ? OR type2 = ? OR type3 = ?";
    $stmt = DB::run($tagtorrent_query, [$name, $name, $name])->fetchAll();
    if (count($stmt) == 0) {
      message(URLROOT, LANG["NOTHING_FOUND"]);
    }

    // String Of IDS
    $array = array();
    foreach ($stmt as $item) {
      array_push($array, $item['torrentid']);
    }
    $ids = implode(",", $array);
    $prefix = ',';
    if (substr($ids, 0, strlen($prefix)) == $prefix) {
      $ids = substr($ids, strlen($prefix));
    }

    // Get Torrent Data
    $torrents_query = "SELECT COUNT(*) FROM torrents WHERE torrents.id IN ($ids)";
    $count = DB::run($torrents_query)->fetchColumn();
    list($pager, $limit) = pagination(25, $count, URLROOT . "/tag/$name&amp;");

    $torrents_query = 
      "SELECT torrents.anon, torrents.seeders, torrents.tube, 
                    torrents.banned, torrents.poster, torrents.comments, torrents.leechers, torrents.info_hash, 
                    torrents.filename, torrents.nfo, torrents.last_action, 
                    torrents.numratings, torrents.name, torrents.tmdb, 
                    torrents.imdb, torrents.owner, torrents.save_as, torrents.descr, 
                    torrents.visible, torrents.size, torrents.added, torrents.views, 
                    torrents.hits, torrents.times_completed, torrents.id, torrents.type, 
                    torrents.external, torrents.image1, torrents.image2, torrents.announce, 
                    torrents.numfiles, torrents.freeleech, torrents.vip, torrents.category, torrents.sticky,
                    torrents.announcelist, torrents.filelist,
                    IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, 
                    torrents.numratings, 
                    categories.name AS cat_name, 
                    torrentlang.name AS lang_name, 
                    torrentlang.image AS lang_image, 
                    categories.parent_cat as cat_parent, 
                    users.username, users.privacy 
            FROM torrents 
            LEFT JOIN categories ON torrents.category = categories.id 
            LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id 
            LEFT JOIN users ON torrents.owner = users.id
            WHERE torrents.id IN ($ids) $limit";
    $stmt = DB::run($torrents_query)->fetchAll(PDO::FETCH_ASSOC);
    if (count($stmt) == 0) {
      message(URLROOT, LANG["NO_TORRENT_FOUND"]);
    }
    $array = [
      'stmt' => $stmt,
      'pager' => $pager,
      'count' => $count
    ];

    return $array;
  }
}