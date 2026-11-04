<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Cache;
use Bootstrap\Libs\Thumbnail;
use PDO;

/*
* TMDB Table Data
*/
class TmdbLogic
{
    public static function api_link($item_id, $type = 'imdb')
    {
        // Type check tv, movie or default imdb
        if ($type == 'tv') {
            $url = "https://api.themoviedb.org/3/tv/$item_id?language=en-US&api_key=" . CONFIG['_TMDBAPIKEY'] . "&append_to_response=credits";
        } elseif ($type == 'film') {
            $url = "https://api.themoviedb.org/3/movie/$item_id?language=en-US&api_key=" . CONFIG['_TMDBAPIKEY'] . "&append_to_response=credits";
        } else {
            $url = "https://www.omdbapi.com/?i=tt$item_id&apikey=" . CONFIG['_OMDBAPIKEY'] . "";
        }

        // cURL
        $ch = curl_init();
        $params = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
        ];
        $curlOptions = $params;
        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);
        curl_close($ch);

        $unfilteredResults =  json_decode($result);

        foreach ($unfilteredResults as $key => $value) {
            $data[$key] = $value;
        }

        if (is_array($data['genres'])) {
            $name = '';
            foreach ($data['genres'] as $key) {
                $name .= $key->name . ',';
            }
        }

        $x = 0;
        if (is_array($data['credits']->cast)) {
            $actors = '';
            foreach ($data['credits']->cast as $key) {
                $x++;
                $actors .= $key->name . ',';
                if ($x == 5) {
                    break;
                }
            }
        }

        $_data = [];

        $_data['Title'] = empty($data['Title']) ? $data['original_title'] ?? $data['original_name'] : $data['Title'];
        $_data['Rated'] = empty($data['Rated']) ? $data['vote_average'] : $data['Rated'];
        $_data['Released'] = empty($data['Released']) ? $data['release_date'] ?? $data['first_air_date'] : $data['Released'];
        $_data['Runtime'] = empty($data['Runtime']) ? $data['runtime'] ?? $data['episode_run_time'][0] : $data['Runtime'];
        $_data['Genre'] = empty($data['Genre']) ? $name : $data['Genre'];
        $_data['Actors'] = empty($data['Actors']) ? $actors : $data['Actors'];
        $_data['Plot'] = empty($data['Plot']) ? $data['overview'] : $data['Plot'];
        $_data['Language'] = empty($data['Language']) ? $data['original_language'] : $data['Language'];
        $_data['Poster'] = empty($data['Poster']) ? $data['poster_path'] : $data['Poster'];

        //echo '<pre>'.print_r($_data, true).'</pre>';

        return $_data;
    }

    public static function createMDB($torrent_id, $item_id, $type = 'imdb')
    {
        $tmdb_query = "SELECT count(id) FROM `tmdb` WHERE torrentid=? ";
        $total = DB::run($tmdb_query, [$torrent_id])->fetchColumn();
        if ($total == 0) {
            $data = self::api_link($item_id, $type);
            $tmdb_query = "INSERT INTO `tmdb` (id_tmdb,title,duration,genre,plot,actor,trailer,date,
                image,type,torrentid) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            DB::run($tmdb_query,
            [$item_id, $data['Title'], $data['Runtime'],
            $data['genre'], $data['Plot'], $data['Actors'],
            $data['trailer'], $data['Released'],
            $data['Poster'], $type, $torrent_id]);

            if ($type == 'tv') {
                $link = "https://image.tmdb.org/t/p/w200" . $data['Poster'] . "";
                $destination = IMAGE_PATH . "/tv.$item_id.jpg";
            } elseif ($type == 'film') {
                $link = "https://image.tmdb.org/t/p/w200" . $data['Poster'] . "";
                $destination = IMAGE_PATH . "/film.$item_id.jpg";
            } else {
                $link = $data['Poster'];
                $destination = IMAGE_PATH . "/imdb.$item_id.jpg";
            }

            Thumbnail::create()->copyImage($link, $destination);
            $torrents_query = "UPDATE torrents SET poster = ? WHERE id=?";
            DB::run($torrents_query, [$link, $torrent_id]);
        }

        if ($type == 'tv') {
            $path = "tv" . $item_id;
        } elseif ($type == 'film') {
            $path = "film" . $item_id;
        } else {
            $path = "imdb" . $item_id;
        }

        $TTCache = new Cache();
        $expires = 1728000; // Cache time in seconds
        if (($_data = $TTCache->Get($path, $expires)) === false) {
            // Get Data From DB
            $tmdb_query = "SELECT * FROM tmdb WHERE id_tmdb = ?";
            $_data = DB::run($tmdb_query, [$item_id])->fetch(PDO::FETCH_ASSOC);
            $rows = array();
            foreach ($_data as $row) {
                $rows[] = $row;
            }
            // Set Data In Cache
            $TTCache->Set($path, $_data, $expires);
        }

        // Image
        if ($type == 'tv') {
            $_data['link'] =  IMAGE_PATH . "/tv.$item_id.webp";
        } elseif ($type == 'film') {
            $_data['link'] = IMAGE_PATH . "/film.$item_id.webp";
        } else {
            $_data['link'] = IMAGE_PATH . "/imdb.$item_id.webp";
        }      

        return $_data;
    }

    // Get Image Or Poster
    public static function getImage($row)
    {
        if ($row["imdb"] != '') {
            $img =  $row["poster"];
        } elseif ($row["tmdb"] != '') {
            $img =  $row["poster"];
        } elseif ($row["image1"] != '') {
            $img = data_uri(IMAGE_PATH . "/" . $row["image1"], $row['image1']);
        } elseif ($row["image2"] != '') {
            $img = data_uri(IMAGE_PATH . "/" . $row["image2"], $row['image2']);
        } else {
            $img = "" . URLROOT . "/page/images/misc/default_avatar.webp";
        }

        return $img;
    }

}