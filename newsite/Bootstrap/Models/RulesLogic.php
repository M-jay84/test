<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;

/*
* Rules Table Data
*/
class RulesLogic
{
    // Get Rules
    public static function gettAll()
    {
        $rules_query = "SELECT * FROM `rules` ORDER BY id";
        $stmt = DB::run($rules_query)->fetchAll();
        return $stmt;
    }

    // Admin Delete Rule
    public static function delete($id, $current_user_username)
    {
        $rules_query = "DELETE FROM rules WHERE id' = ?";
        DB::run($rules_query, [$id]);
        LogsLogic::write("Rules have been deleted by ".$current_user_username."");
        message('rule', LANG["SUCCESS"]);
    }

    // Admin Add/Update Rules
    public static function insertOrUpdate($current_user_username)
    {
        if (!empty($_POST)) {
            $id = Input1('id', 'int');
            $title = Input1('title');
            $text = Input1('text');
            $public = Input1('public');
            $class = Input1('classs');
            if ($id == 0) {
                $rules_query = "INSERT INTO `rules` (title, text, public, class) VALUES (?,?,?,?)";
                DB::run($rules_query, [$title, $text, $public, $class]);
            } else {
                $rules_query = "UPDATE rules SET title = ? AND text = ? AND public = ? AND class = ? WHERE id=?";
                DB::run($rules_query, [$title, $text, $public, $class, $id]);
            }
            LogsLogic::write("Rules have been changed by ".$current_user_username."");
            message('rule', LANG["SUCCESS"]);
        } else {
            message(URLROOT, LANG["MISSING_FORM_DATA"]);
        }
    }
}