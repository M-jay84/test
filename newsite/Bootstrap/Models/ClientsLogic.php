<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;

/*
* Clients Table Data
*/
class ClientsLogic
{
 public static function insert(): never
{
    $name    = input_string('type');
    $peer_id = input_string('peer_id');

    if ($peer_id === '') {
        redirect('client/view/client', __('MISSING_FORM_DATA'), 'warning');
    }

    $peer = @hex2bin($peer_id) ?: $peer_id;
    $code = substr($peer, 0, 8);

    DB::run(
        'INSERT INTO clients (agent_name, hits, ins_date, code, peer_id) VALUES (?,?,?,?,?)',
        [$name, 'yes', CONFIG['TT_DATE'], $code, $peer_id]
    );

    redirect('client/view/client', __('SUCCESS'), 'success');
}

public static function select(string $type): array
{
    $array = [];

    if ($type === 'client' || $type === 'clients') {
        $clients = DB::run('SELECT hits, agent_name, code, agent_id, peer_id FROM clients')->fetchAll();
        foreach ($clients as $client) {
            $array[] = [
                'id'        => (int) $client['agent_id'],
                'type'      => $client['agent_name'],
                'peer_id'   => $client['peer_id'],
                'banned'    => $client['hits'],
                'connected' => 'no',
            ];
        }
    } else {
        $peers = DB::run('SELECT DISTINCT client FROM peers')->fetchAll();
        foreach ($peers as $peer) {
            $peerid = DB::run(
                'SELECT peer_id FROM peers WHERE client = ? LIMIT 1',
                [$peer['client']]
            )->fetchColumn();
            $array[] = [
                'id'        => 0,
                'type'      => $peer['client'],
                'peer_id'   => $peerid,
                'banned'    => 'no',
                'connected' => 'yes',
            ];
        }
    }

    return $array;
}

public static function delete(int $id): never
{
    DB::run('DELETE FROM clients WHERE agent_id = ?', [$id]);
    redirect('client/view/client', __('SUCCESS'), 'success');
}

}