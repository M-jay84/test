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

public static function delete(int $id, string $username): void
{
    DB::run('DELETE FROM rules WHERE id = ?', [$id]);
    LogsLogic::write('Rules have been deleted by ' . $username);
    redirect('rule', __('SUCCESS'), 'success');
}

public static function insertOrUpdate(string $username): void
{
    $id     = input_int('id', 0);
    $title  = input_string('title');
    $text   = input_string('text');
    $public = input_string('public');
    $class  = input_string('classs');

    if ($title === '' || $text === '') {
        redirect('rule', __('MISSING_FORM_DATA'), 'warning');
    }

    if ($id < 1) {
        DB::run(
            'INSERT INTO rules (title, text, public, class) VALUES (?, ?, ?, ?)',
            [$title, $text, $public, $class]
        );
    } else {
        DB::run(
            'UPDATE rules SET title = ?, text = ?, public = ?, class = ? WHERE id = ?',
            [$title, $text, $public, $class, $id]
        );
    }

    LogsLogic::write('Rules have been changed by ' . $username);
    redirect('rule', __('SUCCESS'), 'success');
}
}