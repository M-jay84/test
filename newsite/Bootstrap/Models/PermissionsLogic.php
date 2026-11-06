<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

class PermissionsLogic
{
    public static function getAll(): array
    {
        return DB::run(
            'SELECT * FROM permissions ORDER BY permission_id'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(int $id): never
    {
        if ($id === 1) {
            redirect('permissions/view', __('YOU_CANT_DEL_THIS_GRP'), 'warning');
        }
        if ($id < 1) {
            redirect('permissions/view', __('MISSING_ID'), 'warning');
        }

        DB::run('DELETE FROM permissions WHERE permission_id = ?', [$id]);
        redirect('permissions/view', __('DEL_COMPLETE'), 'success');
    }

    public static function insertOrUpdate(): never
    {
        $groupId = input_int('permission_id', 0);
        $name    = input_string('permission_name');

        $fields = [
            'view_torrents', 'edit_torrents', 'delete_torrents',
            'view_users', 'edit_users', 'delete_users',
            'view_news', 'edit_news', 'delete_news',
            'view_forum', 'edit_forum', 'delete_forum',
            'can_upload', 'can_download',
        ];

        $vals = [];
        foreach ($fields as $f) {
            $v = input_string($f);
            $vals[] = ($v === 'yes') ? 'yes' : 'no';
        }

        if ($name === '') {
            redirect('permissions/view', __('MISSING_FORM_DATA'), 'warning');
        }

        if ($groupId < 1) {
            DB::run(
                'INSERT INTO permissions (
                    permission_name, view_torrents, edit_torrents, delete_torrents,
                    view_users, edit_users, delete_users,
                    view_news, edit_news, delete_news,
                    view_forum, edit_forum, delete_forum,
                    can_upload, can_download
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array_merge([$name], $vals)
            );
        } else {
            DB::run(
                'UPDATE permissions SET
                    permission_name = ?,
                    view_torrents = ?, edit_torrents = ?, delete_torrents = ?,
                    view_users = ?, edit_users = ?, delete_users = ?,
                    view_news = ?, edit_news = ?, delete_news = ?,
                    view_forum = ?, edit_forum = ?, delete_forum = ?,
                    can_upload = ?, can_download = ?
                 WHERE permission_id = ?',
                array_merge([$name], $vals, [$groupId])
            );
        }

        redirect('permissions/view', __('ACTION_COMPLETE'), 'success');
    }
}