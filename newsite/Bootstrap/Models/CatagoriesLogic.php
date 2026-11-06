<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\TorrentsLogic;

/*
* Catagories Table Data
*/
class CatagoriesLogic
{

    // Admin Delete Catagorie
    public static function deleteAndUpdateTorrent($id, $sure = 0)
    {
        if ($sure) {
            // Move Any Torrents to Other Cat & Delete Cat
            $newcatid = Input1('newcat', 'int');
            $torrents_query = "UPDATE torrents SET category = ? WHERE category=?";
            DB::run($torrents_query, [$newcatid, $id]); //move torrents to a new cat
            $categories_query = "DELETE FROM `categories` WHERE id = ?";
            DB::run($categories_query, [$id]);
            message('admincategorie', LANG["DEL_COMPLETE"]);
        } else {
            // Select Cat 2 Move Kept Torrent Too
            message('admincategorie', 
                "<form method='post' action='" .  URLROOT . "/categorie/delete/$id/sure'>                                 
                <div class='tt_blockcenter rounded'>
                <b>Category ID to move all Torrent To: </b>
                <input type='text' name='newcat' /> (Cat ID)<br><br>
                <p class='text-center'>
                    <input type='submit' class='btn btn-sm ttbtn' value='".LANG["SUBMIT"]."' />
                </div>
                </p>", 30);
        }

    }

    // Admin Delete Catagorie
    public static function getAll()
    {
        $categories_query = "SELECT * FROM `categories` ORDER BY parent_cat ASC, sort_index ASC";
        $cats = DB::run($categories_query);
        return $cats;
    }

    // Admin Add/Edit Catagorie
    public static function insertOrUpdate()
    {
        $parent_cat = Input1('parent_cat');
        $name = Input1('name');
        $sort_index = Input1('sort_index');
        $image = Input1('image');
        $id = Input1('id');

        // Check Correct Input
        if ($parent_cat == "" || $name == "") {
            message('admincategorie/add', LANG["MISSING_FORM_DATA"]);
        }
        
        if (!$id) {
            // Insert Categorie

            $categories_query = "INSERT INTO `categories` (name,parent_cat,sort_index,image) VALUES (?,?,?,?)";
            $ins =  DB::run($categories_query,
            [$name, $parent_cat, $sort_index, $image]);

            if ($ins) {
                message('admincategorie', LANG["SUCCESS"]);
            } else {
                message('admincategorie/add', LANG["ACTION_FAILED"]);
            }
        } else {
            // Update
            $categories_query = "UPDATE categories SET parent_cat = ? AND name = ? AND sort_index = ?  AND image = ? WHERE id=?";
            DB::run($categories_query, [$parent_cat, $name, $sort_index, $image, $id]);
            message('admincategorie', LANG["SUCCESS"]);
        }
    }

    // Get Parent
    public static function getCatByParent()
    {
        $categories_query = "SELECT distinct parent_cat FROM categories ORDER BY parent_cat";
        $row = DB::run($categories_query);
        return $row;
    }

    // List Genres
    public static function genreList()
    {
        $ret = array();
        $categories_query = "SELECT id, name, parent_cat FROM `categories` ORDER BY parent_cat ASC, sort_index ASC";
        $res = DB::run($categories_query);
        foreach ($res as $row) {
            $ret[] = $row;
        }
        return $ret;
    }

    // Catagories Dropdown
    public static function dropdown($category = 0)
    {
        $data = self::genreList();
        $catdropdown = '';
        if (!$category) {
            $catdropdown .= "<option value='0'>Catagorie</option>\n";
        }
        foreach ($data as $row) {
            $catdropdown .= "<option value=\"" . $row["id"] . "\"";
            if ($row["id"] == $category) {
                $catdropdown .= " selected=\"selected\"";
            }
            $catdropdown .= ">" . htmlspecialchars($row["parent_cat"]) . ": " . htmlspecialchars($row["name"]) . "</option>\n";
        }
    
        return $catdropdown;
    }

}