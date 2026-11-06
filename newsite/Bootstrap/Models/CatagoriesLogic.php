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

 public static function deleteAndUpdateTorrent(int $id, bool $sure = false): never
{
    if (!$sure) {
        redirect('categorie/delete/' . $id, __('CONFIRM_DELETE'), 'warning');
    }

    $newcatid = input_int('newcat');
    if ($newcatid < 1) {
        redirect('categorie/delete/' . $id, __('MISSING_FORM_DATA'), 'warning');
    }

    DB::run('UPDATE torrents SET category = ? WHERE category = ?', [$newcatid, $id]);
    DB::run('DELETE FROM categories WHERE id = ?', [$id]);
    redirect('categorie/view', __('DEL_COMPLETE'), 'success');
}

public static function getAll(): array
{
    return DB::run(
        'SELECT * FROM categories ORDER BY parent_cat ASC, sort_index ASC'
    )->fetchAll();
}

public static function insertOrUpdate(): never
{
    $parent_cat = input_string('parent_cat');
    $name       = input_string('name');
    $sort_index = input_string('sort_index');
    $image      = input_string('image');
    $id         = input_int('id');

    if ($parent_cat === '' || $name === '') {
        redirect('categorie/view', __('MISSING_FORM_DATA'), 'warning');
    }

    if ($id < 1) {
        DB::run(
            'INSERT INTO categories (name, parent_cat, sort_index, image) VALUES (?,?,?,?)',
            [$name, $parent_cat, $sort_index, $image]
        );
    } else {
        DB::run(
            'UPDATE categories SET parent_cat = ?, name = ?, sort_index = ?, image = ? WHERE id = ?',
            [$parent_cat, $name, $sort_index, $image, $id]
        );
    }

    redirect('categorie/view', __('SUCCESS'), 'success');
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