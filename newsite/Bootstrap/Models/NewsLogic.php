<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\Auth;
use Bootstrap\Libs\DB;
use PDO;

class NewsLogic
{
    public static function getAll(): array
    {
        return DB::run(
            'SELECT * FROM news ORDER BY added DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insertOrUpdate(): never
    {
        $body  = input_string('body');
        $title = input_string('title');
        $id    = input_int('id', 0);
        $uid   = (int) Auth::id();

        if ($body === '' || $title === '') {
            redirect('news/view', __('MISSING_FORM_DATA'), 'warning');
        }

        if ($id < 1) {
            DB::run(
                'INSERT INTO news (userid, added, body, title) VALUES (?, ?, ?, ?)',
                [$uid, CONFIG['TT_DATE'], $body, $title]
            );
        } else {
            DB::run(
                'UPDATE news SET body = ?, title = ? WHERE id = ?',
                [$body, $title, $id]
            );
        }

        redirect('news/view', __('ACTION_COMPLETE'), 'success');
    }

    public static function delete(int $id, int $userid): never
    {
        if ($id < 1) {
            redirect('news/view', __('MISSING_ID'), 'warning');
        }

        DB::run('DELETE FROM news WHERE id = ?', [$id]);
        CommentsLogic::delete($id, $userid, 'news');
        redirect('news/view', __('DEL_COMPLETE'), 'success');
    }
}