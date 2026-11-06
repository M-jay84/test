<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;
use Bootstrap\Models\LogsLogic;

/*
* Bans Table Data
*/
class BansLogic
{
public static function delete($current_user): never
{
    $ids = multiIds('ids');
    if ($ids === '' || $ids === '0') {
        redirect('ban/admin', __('NOTHING_SELECTED'), 'warning');
    }

    $rows = DB::run("SELECT id, first, last FROM bans WHERE id IN ($ids)")->fetchAll();
    foreach ($rows as $row) {
        DB::run('DELETE FROM bans WHERE id = ?', [$row['id']]);
        LogsLogic::write(
            "Ban ({$row['first']} - {$row['last']}) was removed by {$current_user->id} ({$current_user->username})"
        );
    }

    redirect('ban/admin', __('ACTION_COMPLETE'), 'success');
}

public static function insert($current_user): never
{
    $first   = input_string('first');
    $last    = input_string('last');
    $comment = input_string('comment');
    $type    = input_string('type');

    if ($first === '' || $type === '' || $comment === '') {
        redirect('ban/admin/' . ($type ?: 'email'), __('MISSING_FORM_DATA'), 'warning');
    }

    if (!in_array($type, ['email', 'ip', 'domain'], true)) {
        redirect('ban/admin', __('MISSING_FORM_DATA'), 'warning');
    }

    DB::run(
        'INSERT INTO bans (added, addedby, first, last, comment, type) VALUES (?,?,?,?,?,?)',
        [CONFIG['TT_DATE'], $current_user->id, $first, $last, $comment, $type]
    );

    LogsLogic::write(sprintf(__('BAN_ADDED'), $first, $current_user->username));
    redirect('ban/admin/' . $type, __('SUCCESS'), 'success');
}

public static function getAll(string $type): array
{
    if (!in_array($type, ['email', 'ip', 'domain'], true)) {
        $type = 'email';
    }

    $count = (int) DB::run(
        'SELECT COUNT(id) FROM bans WHERE type = ?',
        [$type]
    )->fetchColumn();

    [$pager, $limit] = pagination(40, $count, url('ban/admin/' . $type));

    $stmt = DB::run(
        "SELECT bans.id, bans.added, bans.addedby, bans.first, bans.last, bans.comment, bans.type, users.username
         FROM bans
         LEFT JOIN users ON bans.addedby = users.id
         WHERE bans.type = ?
         ORDER BY bans.added DESC $limit",
        [$type]
    )->fetchAll();

    return ['bans' => $stmt, 'pager' => $pager];
}

    // Check Email account/invite
    public static function checkEmail($email)
    {
        $maildomain = (substr($email, strpos($email, "@") + 1));
        $bans_query = "SELECT count(*) FROM `bans` WHERE first = ?";
        $a = DB::run($bans_query, [$email])->fetchColumn();
        if ($a != 0) {
            $message = sprintf(LANG["EMAIL_ADDRESS_BANNED_S"], $email);
        }
        $bans_query = "SELECT count(*) FROM `bans` WHERE first LIKE ?";
        $a = DB::run($bans_query, ['%$maildomain%'])->fetchColumn();
        if ($a != 0) {
            //$message = sprintf(LANG["EMAIL_ADDRESS_BANNED_S"], $email);
        }
        // check if email addy is already in use
        $users_query = "SELECT count(id) FROM `users` WHERE email=?";
        $a = DB::run($users_query, [$email])->fetchColumn();
        if ($a != 0) {
            $message = sprintf(LANG["EMAIL_ADDRESS_INUSE_S"], $email);
        }
        return $message;
    }

}