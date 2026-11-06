<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use PDO;

class PeersLogic
{
    public static function getPeers(string $type, int $id, object $current_user, string $paginationPath = ''): array
    {
        $user = [];
        if (in_array($type, ['seeding', 'leeching'], true) && $id > 0) {
            $user = DB::run(
                'SELECT id, username, privacy FROM users WHERE id = ?',
                [$id]
            )->fetch(PDO::FETCH_ASSOC) ?: [];

            if (
                ($current_user->view_users ?? '') !== 'yes'
                && (int) $current_user->id !== $id
                && (int) ($current_user->class ?? 0) < (int) (CONFIG['_UPLOADER'] ?? 0)
            ) {
                redirect('home', __('NO_USER_VIEW'), 'warning');
            }
        }

        $title  = __('PEERS');
        $where  = '1=0';
        $values = [];

        if ($type === 'torrent') {
            if (
                ($current_user->torrents ?? '') === 'no'
                && (int) $current_user->id !== $id
                && (int) ($current_user->class ?? 0) < (int) (CONFIG['_UPLOADER'] ?? 0)
            ) {
                redirect('home', __('NO_TORRENT_VIEW'), 'warning');
            }
            $where  = 'peers.torrent = ?';
            $values = [$id];
            $name   = (string) DB::run('SELECT name FROM torrents WHERE id = ?', [$id])->fetchColumn();
            $title  = __('TORRENT_DETAILS_FOR') . ' ' . $name;
        } elseif ($type === 'admin') {
            $where  = 'torrents.id > ?';
            $values = [0];
            $title  = __('PEERS');
        } elseif ($type === 'seeding') {
            $where  = 'peers.userid = ? AND peers.seeder = ?';
            $values = [$id, 'yes'];
            $title  = __('USER_DETAILS_FOR') . ' ' . coloredname($id);
        } elseif ($type === 'leeching') {
            $where  = 'peers.userid = ? AND peers.seeder = ?';
            $values = [$id, 'no'];
            $title  = __('USER_DETAILS_FOR') . ' ' . coloredname($id);
        }

        $count = (int) DB::run(
            "SELECT COUNT(*) FROM peers
             LEFT JOIN torrents ON peers.torrent = torrents.id
             LEFT JOIN categories ON torrents.category = categories.id
             WHERE $where",
            $values
        )->fetchColumn();

        $pageUrl = $paginationPath !== ''
            ? url($paginationPath)
            : url("peers/view/{$type}/{$id}?");

        [$pager, $limit] = pagination(25, $count, $pageUrl);

        $stmt = DB::run(
            "SELECT peers.id AS pid, peers.torrent, peers.peer_id, peers.ip, peers.port,
                    peers.uploaded, peers.downloaded, peers.to_go, peers.seeder, peers.started,
                    peers.last_action, peers.connectable, peers.client, peers.userid, peers.passkey,
                    torrents.info_hash, torrents.name, torrents.size, torrents.visible,
                    torrents.owner, torrents.freeleech, torrents.vip, torrents.seeders, torrents.leechers,
                    torrents.category, categories.name AS cat_name, categories.parent_cat AS cat_parent
             FROM peers
             LEFT JOIN torrents ON peers.torrent = torrents.id
             LEFT JOIN categories ON torrents.category = categories.id
             WHERE $where
             ORDER BY peers.last_action DESC
             $limit",
            $values
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'stmt'  => $stmt,
            'pager' => $pager,
            'count' => $count,
            'title' => $title,
            'user'  => $user,
        ];
    }
}