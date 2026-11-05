<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

/*
* Countries Table Data
*/
class CountriesLogic
{
    // Country Flag //todo view
    public static function fetchRow($country, $pic = 0)
    { 
        $countries_query = "SELECT name,flagpic FROM `countries` WHERE id=? LIMIT 1";
        $carr = DB::run($countries_query, [$country])->fetch(PDO::FETCH_ASSOC);
        if ($carr) {
            $country = $pic == 1 ? "<img src='" . URLROOT . "/page/theme/default/images/languages/$carr[flagpic]' title='" . htmlspecialchars($carr['name']) . "' alt='" . htmlspecialchars($carr['name']) . "' />" : htmlspecialchars($carr['name']);
        } else {
            $country = $pic == 1 ? "<img src='" . URLROOT . "/page/theme/default/images/languages/unknown.webp' alt='Unknown' />" : "<b>Unknown</b>";
        }
        return $country;
    }

    //Country Dropdown
    public static function dropdown($country = 0)
    {
        $data = array();
        $countries_query = "SELECT id, name FROM `countries` ORDER BY name";
        $stmt = DB::run($countries_query);
        foreach ($stmt as $row) {
            $data[] = $row;
        }
        $countries = '';
        if (!$country) {
            $countries .= "<option value='12'>United Kindom</option>\n";
        }
        foreach ($data as $row) {
            $countries .= "<option value=\"" . $row["id"] . "\"";
            if ($row["id"] == $country) {
                $countries .= " selected=\"selected\"";
            }
            $countries .= ">" . htmlspecialchars($row["name"]) . "</option>\n";
        }
    
        return $countries;
    }
}