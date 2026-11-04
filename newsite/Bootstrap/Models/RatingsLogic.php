<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use PDO;

/*
* Ratings Table Data
*/
class RatingsLogic
{
    // Show Rating
    public static function ratingTor($id, $userid, $rating = 0)
    {
        if (!$rating) {
            $rating = "<b>" . LANG["RATINGS"] . ":</b> <b>Not Yet Rated</b><br>";
        } else {
            $average = ceil($rating);
            $rating = "<img src=\"" . URLROOT . "/page/images/rating/$average.webp\" alt=\"rating: $average/5\" title=\"rating: $average/5\" /></a><br>";
        }
        if ($userid) {
            $ratings = [
                5 => LANG["COOL"],
                4 => LANG["PRETTY_GOOD"],
                3 => LANG["DECENT"],
                2 => LANG["PRETTY_BAD"],
                1 => LANG["SUCKS"],
            ];
            $reports_query = "SELECT rating, added FROM `reports` WHERE torrent=? AND user=?";
            $res = DB::run($reports_query, [$id, $userid])->fetch(PDO::FETCH_ASSOC);
            $row = $res;
            if ($row) {
                $rating .= "<i>(" . LANG["YOU_RATED"] . " \"" . $row["rating"] . " - " . $ratings[$row["rating"]] . "\")</i>";
            } else {
                $rating .= "<form style=\"display:inline;\" method=\"post\" action=\"" . URLROOT . "/rating/$id\"><input type=\"hidden\" name=\"id\" value=\"$id\" />";
                $rating .= "<select name=\"rating\" class='ttbtn'>";
                $rating .= "<option value=\"0\">(" . LANG["ADD_RATING"] . ")</option>";
                foreach ($ratings as $k => $v) {
                    $rating .= "<option value=\"$k\">$k - $v</option>";
                }
                $rating .= "</select>";
                $rating .= "<input type=\"submit\" class='btn btn-sm ttbtn' value=\"" . LANG["VOTE"] . "\" />";
                $rating .= "</form>";
            }
        }

        return $rating;
    }

    // Add Rating
    public static function addRating($id, $userid)
    {
        // Check User Input
        $rating = Input1('rating', 'int');

        if ($rating <= 0 || $rating > 5) {
            message(("torrent/$id"), LANG["INVAILD_RATING"]);
        }
        
        $ratings_query = "INSERT INTO `ratings` (torrentd, user, rating, added) VALUES (?,?,?,?)";
        $res = DB::run($ratings_query, [$id, $userid , $rating, CONFIG['TT_DATE']]);
        if ($res) {
            if ($res->errorCode() == 1062) {
                message(("torrent/$id"), LANG["YOU_ALREADY_RATED_TORRENT"]);
            } else {
                message(("torrent/$id"), LANG["ACTION_FAILED"]);
            }
        }
        
        $torrents_query = "UPDATE torrents SET numratings = numratings + 1, ratingsum = ratingsum + $rating WHERE id = $id";
        DB::run($torrents_query);
        message(("torrent/$id"), LANG["RATING_THANK"]);
    }
    
}