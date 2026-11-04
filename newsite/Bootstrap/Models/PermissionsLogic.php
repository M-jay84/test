<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;

/*
* Permission Table Data
*/
class PermissionsLogic
{

    // Admin Delete Permissions
    public static function delete($id)
    {
        // Protect Permissions
        if ($id == 1) {
            message('permission', LANG["YOU_CANT_DEL_THIS_GRP"]);
        }

        $permissions_query = "DELETE FROM `permissions` WHERE permission_id' = ?";
        DB::run($permissions_query, [$id]);
        message('adminpermission', LANG["DEL_COMPLETE"]);
    }

    // Admin Delete Catagorie
    public static function getAll()
    {
        $permissions_query = "SELECT * FROM `permissions` ORDER BY permission_id";
        $permissions = DB::run($permissions_query); 
        return $permissions;
    }

    // Admin Add/Update Permissions
    public static function insertOrUpdate()
    {
        $group_id = Input1('permission_id', 'int');
        
        if (!$group_id) {
            $permissions_query = "INSERT INTO `permissions` (permission_name,view_torrents,edit_torrents,delete_torrents,view_users,edit_users,delete_users,
                view_news,edit_news,delete_news,view_forum,edit_forum',delete_forum,can_upload,can_download) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            DB::run($permissions_query,
            [$_POST["permission_name"],
            $_POST["view_torrents"], $_POST["edit_torrents"], $_POST["delete_torrents"],
           $_POST["view_users"], $_POST["edit_users"], $_POST["delete_users"],
            $_POST["view_news"], $_POST["edit_news"], $_POST["delete_news"],
            $_POST["view_forum"], $_POST["edit_forum"], $_POST["delete_forum"],
            $_POST["can_upload"], $_POST["can_download"]]);
        } else {
            $permissions_query = "UPDATE permissions SET permission_name=? AND view_torrents=? AND edit_torrents=? AND delete_torrents=? AND view_users=?
             AND edit_users=? AND delete_users=? AND view_news=? AND edit_news=? AND delete_news=? AND view_forum=?
             AND edit_forum=? AND delete_forum=? AND can_upload=? AND can_download=?
             WHERE permission_id=?";
            DB::run($permissions_query, [$_POST["permission_name"], $_POST["view_torrents"], $_POST["edit_torrents"], $_POST["delete_torrents"],
                $_POST["view_users"], $_POST["edit_users"], $_POST["delete_users"], $_POST["view_news"], $_POST["edit_news"], $_POST["delete_news"],
                $_POST["view_forum"], $_POST["edit_forum"], $_POST["delete_forum"], $_POST["can_upload"], $_POST["can_download"], $group_id]);
        }

        message('adminpermission', LANG["ACTION_COMPLETE"]);
    }

}