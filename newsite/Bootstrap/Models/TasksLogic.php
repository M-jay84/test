<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\Cache;
use Bootstrap\Libs\DB;
use Bootstrap\Libs\Torrents\Cleanup; // adjust if class path differs

class TasksLogic
{
    public static function updateTask(string $task): void
    {
        DB::run(
            'UPDATE tasks SET last_time = ? WHERE task = ?',
            [CONFIG['TT_TIME'] ?? time(), $task]
        );
    }

    public static function purgeCache(): never
    {
        $cache = new Cache();
        foreach ([
            'sql_cache/latestuploadsblock',
            'sql_cache/mostactivetorrents_block',
            'sql_cache/newestmember_block',
            'sql_cache/usersonline_block',
        ] as $key) {
            $cache->Delete($key);
        }
        redirect('admin/admincp', __('PURGE_COMPLETED'), 'success');
    }

    public static function forceCleanup(): never
    {
        self::updateTask('cleanup');
        if (class_exists(Cleanup::class)) {
            Cleanup::run();
        } elseif (class_exists(Cleanup::class)) {
            Cleanup::run();
        }
        redirect('admin/admincp', __('FORCE_CLEAN_COMPLETED'), 'success');
    }
}