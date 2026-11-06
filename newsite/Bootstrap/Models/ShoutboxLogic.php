<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Auth;
use Bootstrap\Models\LogsLogic;
use PDO;

class ShoutboxLogic
{
    public static function shouts(int $limit = 20, int $staff = 0): array
    {
        $limit = max(1, min(200, $limit));

        return DB::run(
            "SELECT s.msgid, s.userid, s.date, s.user, s.message, s.staff,
                    u.avatar
             FROM shoutbox s
             LEFT JOIN users u ON u.id = s.userid
             WHERE s.staff = ?
             ORDER BY s.msgid DESC
             LIMIT {$limit}",
            [$staff]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add(object $user, int $staff = 0): never
    {
        if (($user->shoutboxpos ?? '') === 'yes' || empty($user->id)) {
            redirect('home', __('NO_PERMISSION'), 'warning');
        }

        $message = input_string('message');
        if ($message === '') {
            redirect('home', __('MISSING_FORM_DATA'), 'warning');
        }

        $dup = (int) DB::run(
            'SELECT COUNT(*) FROM shoutbox
             WHERE message = ? AND user = ?
               AND UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(date) < ?',
            [$message, $user->username, CONFIG['TT_DATE'], 30]
        )->fetchColumn();

        if ($dup === 0) {
            DB::run(
                'INSERT INTO shoutbox (userid, date, user, message, staff) VALUES (?, ?, ?, ?, ?)',
                [(int) $user->id, CONFIG['TT_DATE'], $user->username, $message, $staff]
            );
        }

        redirect('home', __('SUCCESS'), 'success');
    }

    public static function edit(object $user): never
    {
        $ownerId = input_int('user');
        $msgid   = input_int('id');
        $message = input_string('text');

        $isStaff = Auth::isStaff() || (($user->edit_users ?? '') === 'yes');
        if (!$isStaff && (int) $user->id !== $ownerId) {
            redirect('home', __('NO_PERMISSION'), 'warning');
        }
        if ($msgid < 1 || $message === '') {
            redirect('home', __('MISSING_FORM_DATA'), 'warning');
        }

        DB::run('UPDATE shoutbox SET message = ? WHERE msgid = ?', [$message, $msgid]);
        redirect('home', __('UPDATE_COMPLETE'), 'success');
    }

    public static function delete(int $id, object $user, int $sure = 0): never
    {
        if ($id < 1) {
            redirect('home', __('NOTHING_FOUND'), 'warning');
        }
        if ($sure !== 1) {
            // confirmation via message() or flash + link — prefer redirect with flash if you have a confirm UI
            message(
                __('SANITY_CHECK_DELETE', url('shoutbox/delete/' . $id . '/1')),
                'home'
            );
        }

        $owner = (string) DB::run(
            'SELECT user FROM shoutbox WHERE msgid = ?',
            [$id]
        )->fetchColumn();

        $isStaff = ($user->edit_users ?? '') === 'yes' || Auth::isStaff();
        if (!$isStaff && (string) $user->username !== $owner) {
            redirect('home', __('NO_PERMISSION'), 'warning');
        }

        DB::run('DELETE FROM shoutbox WHERE msgid = ?', [$id]);
        LogsLogic::write('Shout deleted by ' . $user->username);
        redirect('home', __('ACTION_COMPLETE'), 'success');
    }

    public static function truncate(string $sure = ''): never
    {
        if ($sure !== 'sure') {
            message(
                __('CONFIRM_CLEAR_SHOUT', url('task/clearshout/sure')),
                'admin/admincp'
            );
        }

        $user = \Bootstrap\Libs\Auth::user();
        DB::run('TRUNCATE TABLE shoutbox');
        LogsLogic::write('Shoutbox cleared by ' . ($user->username ?? 'staff'));
        DB::run(
            'INSERT INTO shoutbox (userid, date, user, message) VALUES (?, ?, ?, ?)',
            [0, CONFIG['TT_DATE'], 'System', __('SHOUTBOX_CLEARED_MESSAGE')]
        );
        redirect('admin/admincp', __('SHOUTBOX_CLEARED'), 'success');
    }
}