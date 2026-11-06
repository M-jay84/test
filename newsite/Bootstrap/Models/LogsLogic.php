<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

class LogsLogic
{
    public static function write(string $text): void
    {
        DB::run(
            'INSERT INTO log (added, txt) VALUES (?, ?)',
            [CONFIG['TT_DATE'], $text]
        );
    }

    public static function getAll(): array
    {
        $search = trim((string) (post('search') ?? get('search', '')));
        $params = [];
        $where  = '';

        if ($search !== '') {
            $where  = 'WHERE txt LIKE ?';
            $params[] = $search . '%';
        }

        $count = (int) DB::run(
            "SELECT COUNT(*) FROM log $where",
            $params
        )->fetchColumn();

        [$pager, $limit] = pagination(25, $count, url('log/view?'));

        // $limit is trusted from pagination helper (e.g. "LIMIT 0,25")
        $logs = DB::run(
            "SELECT id, added, txt FROM log $where ORDER BY id DESC $limit",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['logs' => $logs, 'pager' => $pager, 'search' => $search];
    }

    public static function delete(): never
    {
        $ids = multiIds('ids');
        if ($ids === '' || $ids === []) {
            redirect('log/view', __('NOTHING_SELECTED'), 'warning');
        }

        // multiIds should return CSV of ints; if array, implode
        if (is_array($ids)) {
            $ids = implode(',', array_map('intval', $ids));
        }

        DB::run("DELETE FROM log WHERE id IN ($ids)");
        redirect('log/view', __('DEL_COMPLETE'), 'success');
    }
}