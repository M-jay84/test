<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

class ToptenLogic
{
    public static function getAll(int $limit = 10): array
    {
        $limit = max(1, min(100, $limit));

        $userSpeed = 'id AS userid, username, added, uploaded, downloaded,
            uploaded / GREATEST(1, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS upspeed,
            downloaded / GREATEST(1, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS downspeed';

        return [
            'active' => [
                'title' => __('TOP_MOST_ACTIVE'),
                'rows'  => DB::run(
                    "SELECT t.*, (t.size * t.times_completed + COALESCE(SUM(p.downloaded),0)) AS data
                     FROM torrents AS t
                     LEFT JOIN peers AS p ON t.id = p.torrent AND p.seeder = 'no'
                     GROUP BY t.id
                     ORDER BY seeders + leechers DESC, seeders DESC, added ASC
                     LIMIT {$limit}"
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'snatch' => [
                'title' => __('TOP_MOST_SNATCHED'),
                'rows'  => DB::run(
                    "SELECT t.*, (t.size * t.times_completed + COALESCE(SUM(p.downloaded),0)) AS data
                     FROM torrents AS t
                     LEFT JOIN peers AS p ON t.id = p.torrent AND p.seeder = 'no'
                     GROUP BY t.id
                     ORDER BY times_completed DESC
                     LIMIT {$limit}"
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'country' => [
                'title' => __('TOP_COUNTRIES'),
                'rows'  => DB::run(
                    "SELECT name, flagpic, COUNT(users.country) AS num
                     FROM countries
                     LEFT JOIN users ON users.country = countries.id
                     GROUP BY countries.id, name, flagpic
                     ORDER BY num DESC
                     LIMIT {$limit}"
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'uploaders' => [
                'title' => __('TOP_UPLOADERS'),
                'rows'  => DB::run(
                    "SELECT {$userSpeed} FROM users WHERE enabled = ? ORDER BY uploaded DESC LIMIT {$limit}",
                    ['yes']
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'downloaders' => [
                'title' => __('TOP_DOWNLOADERS'),
                'rows'  => DB::run(
                    "SELECT {$userSpeed} FROM users WHERE enabled = ? ORDER BY downloaded DESC LIMIT {$limit}",
                    ['yes']
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'fastest' => [
                'title' => __('TOP_FASTEST'),
                'rows'  => DB::run(
                    "SELECT {$userSpeed} FROM users WHERE enabled = ? ORDER BY downspeed DESC LIMIT {$limit}",
                    ['yes']
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'bestsharer' => [
                'title' => __('TOP_BEST_SHARERS'),
                'rows'  => DB::run(
                    "SELECT {$userSpeed} FROM users
                     WHERE enabled = ? AND downloaded > 1073741824
                     ORDER BY uploaded / downloaded DESC LIMIT {$limit}",
                    ['yes']
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
            'worstshare' => [
                'title' => __('TOP_WORST_SHARERS'),
                'rows'  => DB::run(
                    "SELECT {$userSpeed} FROM users
                     WHERE enabled = ? AND downloaded > 1073741824
                     ORDER BY uploaded / downloaded ASC, downloaded DESC LIMIT {$limit}",
                    ['yes']
                )->fetchAll(PDO::FETCH_ASSOC),
            ],
        ];
    }
}