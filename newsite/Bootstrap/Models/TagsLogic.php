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

public static function insertOrUpdate(): never
{
    $name   = input_string('name');
    $colour = input_string('colour');
    $id     = input_int('id');

    if ($name === '') {
        redirect('tag/admin', __('MISSING_FORM_DATA'), 'warning');
    }

    if ($id < 1) {
        DB::run('INSERT INTO tags (name, colour) VALUES (?, ?)', [$name, $colour]);
    } else {
        DB::run('UPDATE tags SET name = ?, colour = ? WHERE id = ?', [$name, $colour, $id]);
    }
    redirect('tag/admin', __('SUCCESS'), 'success');
}

public static function delete(): never
{
    $ids = multiIds('ids');
    if ($ids === '' || $ids === []) {
        redirect('tag/admin', __('NOTHING_SELECTED'), 'warning');
    }
    DB::run("DELETE FROM tags WHERE id IN ($ids)");
    redirect('tag/admin', __('DEL_COMPLETE'), 'success');
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