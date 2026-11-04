<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Validate;

/*
* Clients Table Data
*/
class ClientsLogic
{
    // Admin Add Client
    public static function insert()
    {
        $name = Input1('type'); // todo type ???
        $peer_id = Input1('peer_id');
        $peer = hex2bin($_POST['peer_id']);
        $code = substr($peer, 0, 8);
        $banned = 'yes';
        $clients_query = "INSERT INTO `clients` (agent_name,hits,ins_date,code,peer_id) VALUES (?,?,?,?,?)";
        DB::run($clients_query,
        [$name, $banned, CONFIG['TT_DATE'], $code, $peer_id]);

        message('adminclient', LANG["SUCCESS"]);

    }

    // Admin Get All
    public static function select($type)
    {
        $array = array(); 
        if ($type == 'clients') {
            // Get Client Data
            $clients_query = "SELECT hits, agent_name, code, agent_id, peer_id FROM `clients`";
            $clients = DB::run($clients_query);
            foreach ($clients as $client) {
                $array[] = ['id' => $client['agent_id'], 'type' => $client['agent_name'], 'peer_id' => $client['peer_id'], 'banned' => $client['hits'], 'connected' => 'no'];
            }
        } elseif ($type == 'peers') {
            // Get Peer Client Data
            $peers_query = "SELECT DISTINCT client FROM `peers`";
            $peers = DB::run($peers_query)->fetchAll();
            foreach ($peers as $peer) {
                $peers_query = "SELECT peer_id FROM `peers` WHERE client = ?";
                $peerid = DB::run($peers_query, [$peer['client']])->fetchColumn();
                $array[] = ['id' => 0, 'type' => $peer['client'], 'peer_id' => $peerid, 'banned' => 0, 'connected' => 'yes'];
            }
        }

        return $array;
    }

    // Admin Delete Client
    public static function delete($id)
    {
        $clients_query = "DELETE FROM `clients` WHERE agent_id = ?";
        DB::run($clients_query, [$id]);
        message('adminclient', LANG["SUCCESS"]);
    }

}