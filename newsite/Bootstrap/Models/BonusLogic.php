<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;
use Bootstrap\Models\SnatchedLogic;
use PDO;

/*
* Bonus Table Data
*/
class BonusLogic
{

    public static function interval()
    {
        $interval = floor(CONFIG['ADDBONUS'] / 60);
        return $interval;
    }
    

public static function getAll(): array
{
    return DB::run('SELECT * FROM bonus ORDER BY id ASC')->fetchAll();
}

public static function getById($current_user): never
{
    $id  = input_int('id');
    $row = DB::run('SELECT * FROM bonus WHERE id = ?', [$id])->fetch(\PDO::FETCH_ASSOC);

    if (!$row || (float) $current_user->seedbonus < (float) $row['cost']) {
        redirect('bonus/view', __('NO_BONUS'), 'warning');
    }

    DB::run(
        'UPDATE users SET seedbonus = seedbonus - ? WHERE id = ?',
        [$row['cost'], $current_user->id]
    );

    switch ($row['type']) {
        case 'invite':
            DB::run('UPDATE users SET invites = invites + ? WHERE id = ?', [$row['value'], $current_user->id]);
            break;
        case 'traffic':
            DB::run('UPDATE users SET uploaded = uploaded + ? WHERE id = ?', [$row['value'], $current_user->id]);
            break;
        case 'HnR':
            $uid = (int) $current_user->id;
            $res = (int) DB::run(
                'SELECT COUNT(id) FROM snatched WHERE uid = ? AND hnr = ?',
                [$uid, 'yes']
            )->fetchColumn();
            if ($res === 0) {
                redirect('bonus/view', __('NO_HNR'), 'warning');
            }
            LogsLogic::write('A HnR for user ' . $uid . ' has been removed');
            DB::run(
                'UPDATE snatched SET last_time = ?, hnr = ? WHERE uid = ? AND hnr = ?',
                [129600, 'no', $uid, 'yes']
            );
            break;
        case 'VIP':
            $vipuntil = !empty($current_user->vipuntil)
                ? date('Y-m-d H:i:s', strtotime((string) $current_user->vipuntil) + (30 * 86400))
                : date('Y-m-d H:i:s', (int) CONFIG['TT_TIME'] + (30 * 86400));
            $oldclass = $current_user->vipuntil != null ? $current_user->oldclass : $current_user->class;
            DB::run(
                'UPDATE users SET class = ?, oldclass = ?, vipuntil = ? WHERE id = ?',
                [3, $oldclass, $vipuntil, $current_user->id]
            );
            break;
        case 'other':
        default:
            break;
    }

    redirect('bonus/view', __('SUCCESS'), 'success');
}

public static function insertOrUpdate(): never
{
    $id    = input_int('id');
    $title = input_string('title');
    $descr = input_string('descr');
    $cost  = input_int('cost');
    $value = input_string('value');
    $type  = input_string('type');

    if ($title === '' || $descr === '' || $type === '' || $cost < 1) {
        redirect('bonus/view', __('MISSING_FORM_DATA'), 'warning');
    }

    if ($id < 1) {
        DB::run(
            'INSERT INTO bonus (title, descr, cost, value, type) VALUES (?,?,?,?,?)',
            [$title, $descr, $cost, $value, $type]
        );
    } else {
        DB::run(
            'UPDATE bonus SET title = ?, descr = ?, cost = ?, value = ?, type = ? WHERE id = ?',
            [$title, $descr, $cost, $value, $type, $id]
        );
    }

    redirect('bonus/view', __('SUCCESS'), 'success');
}

public static function deleteone(int $id): never
{
    DB::run('DELETE FROM bonus WHERE id = ?', [$id]);
    redirect('bonus/view', __('SUCCESS'), 'success');
}
}