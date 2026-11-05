<?php
// Hide Errors
error_reporting(0);

// Get Settings
require '../Bootstrap/paths.php';
define('ANNOUNCEINTERVAL', 600);
define('MEMBERSONLY', true);
define('DEBUG', false);
define('TT_DATE', date('Y-m-d H:i:s'));

// Debug Function
function mydebug($msg)
{
    if (DEBUG) {
        $fh = fopen(LOG_PATH . '/announce_error.txt', 'a+');
        fwrite($fh, "[Log] " . date("d.m.Y H:i:s") . " - $msg\n");
        fclose($fh);
    }
}

// PDO Function
function pdo($pdo, $sql, $args = NULL)
{
    try {
        if (!$args) {
            return $pdo->query($sql);
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    } catch (\Exception $e) {
        die(response('Database Error'));
    }
}

// Response Fuction
function response($peer_array, $seeders = 0, $leechers = 0)
{
    if (is_string($peer_array)) {
        $error = 'd14:failure reason' . strlen($peer_array) . ':' . $peer_array . 'e';
        mydebug($error);
        return $error;
    }
    $peer_list = '';
    foreach ($peer_array as $peer) {
        $pid = '';
        if (!isset($_GET['no_peer_id'])) {
            $real_id = hex2bin($peer['peer_id']);
            $pid = '7:peer id' . strlen($real_id) . ':' . $real_id;
        }
        $peer_list .= 'd2:ip' . strlen($peer['ip']) . ':' . $peer['ip'] . $pid . '4:porti' . $peer['port'] . 'ee';
    }
    $response = 'd8:intervali' . ANNOUNCEINTERVAL . 'e12:min intervali30e8:completei' . $seeders . 'e10:incompletei' . $leechers . 'e5:peersl' . $peer_list . 'ee';
    mydebug($response);

    return $response;
}

// Set Database Connection
$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opt);
} catch (\Exception $e) {
    die(response('The Database Details Are Incorrect'));
}

// Block Browsers
$agent = $_SERVER["HTTP_USER_AGENT"];
if (preg_match("/^Mozilla|^Opera|^Links|^Lynx/i", $agent)) {
    die(response('What Are you doing ???'));
}

// Block Unwanted Clients
$stmt = pdo($pdo, "SELECT code FROM clients")->fetchAll();
$users_agent = substr($_GET['peer_id'], 0, 8);
foreach ($stmt as $bannedclient) {
    if (@strpos($users_agent, $bannedclient['code']) !== false) {
        die(response('Client is Banned'));
    }
}

// Client Data
$client = [
    'info_hash' => strlen($_GET['info_hash']) == 20 ? bin2hex($_GET['info_hash']) : die(response("Wrong Length " . strlen($_GET['info_hash']) . " - $_GET[info_hash]")),
    'peer_id' => strlen($_GET['peer_id']) == 20 ? bin2hex($_GET['peer_id']) : die(response("Wrong Length " . strlen($_GET['peer_id']) . " - $_GET[peer_id]")),
    'ip' => $_GET['ip'] ?? $_SERVER["REMOTE_ADDR"],
    'port' => (!ctype_digit($_GET['port']) || $_GET['port'] < 1 || $_GET['port'] > 65535) ? $_GET['port'] : die(response("Invalid client port $_GET[port]")),
    'uploaded' => (!is_numeric($_GET['uploaded']) || $_GET['uploaded'] < 0) ? $_GET['uploaded'] : die(response("Wierd Number - $_GET[uploaded]")),
    'downloaded' => (!is_numeric($_GET['downloaded']) || $_GET['downloaded'] < 0) ? $_GET['downloaded'] : die(response("Wierd Number - $_GET[downloaded]")),
    'left' => (!is_numeric($_GET['left']) || $_GET['left'] < 0) ? $_GET['left'] : die(response("Wierd Number - $_GET[left]")),
    'event' => strtolower($_GET['event']) ?? '',
    'passkey' => (strlen($_GET['passkey']) != 32 && MEMBERSONLY) ? $_GET['passkey'] : die(response("Invalid passkey (" . strlen($_GET['passkey']) . " - $_GET[passkey])")),
    'seeder' => ($_GET['left'] == 0) ? "yes" : "no"
];

// Get User
$user = array();
$user['id'] = 0;
if (MEMBERSONLY) {
    $stmt = pdo($pdo, "SELECT u.id, u.class, u.uploaded, u.downloaded, u.enabled, u.status, u.ip, u.passkey,  u.role_id, g.maxslots, g.group_role_id FROM users u INNER JOIN `groups` g ON u.class = g.group_id WHERE u.passkey=? LIMIT 1", [$client['passkey']])->fetch(PDO::FETCH_ASSOC);
    foreach ($stmt as $key => $value) {
        $user[$key] = $value;
    }
    $role = $user['group_role_id'] >= $user['role_id'] ? $user['group_role_id'] : $user['role_id'];
    $permission = pdo($pdo, "SELECT can_download FROM `permissions` WHERE `permission_id`=?",  [$role])->fetch(PDO::FETCH_ASSOC);
    foreach ($permission as $key => $value) {
        $user[$key] = $value;
    }
    // Check User
    if (!$user) {
        die(response("Cannot locate a user with that passkey!"));
    } elseif ($user["can_download"] == "no") {
        die(response("You do not have permission to download."));
    } elseif (!$user["passkey"] == $client['passkey']) {
        die(response("Can NOT find user passkey."));
    } elseif (!$user["enabled"] == 'yes') {
        die(response("not enabled."));
    } elseif (!$user["status"] == 'confirmed') {
        die(response("status not confirmed."));
    }
}

// Get Torrent
$torrent = pdo($pdo,  "SELECT id, info_hash, banned, freeleech, seeders + leechers AS numpeers, UNIX_TIMESTAMP(added) AS ts, seeders, leechers, times_completed FROM torrents WHERE info_hash=?", [$client['info_hash']], 'error getting torrent')->fetch(PDO::FETCH_ASSOC);
// Check Torrent
if (!$torrent) {
    die(response("Torrent not found on this hash = $client[info_hash]"));
} elseif ($torrent["banned"] == 'yes') {
    die(response("Torrent has been banned"));
}

// Get Peer
$peer = pdo($pdo, "SELECT seeder, UNIX_TIMESTAMP(last_action) AS ez, peer_id, ip, port, uploaded, downloaded, userid, passkey FROM peers WHERE torrent = ? AND peer_id = ? AND passkey = ?", [$torrent['id'], $client['peer_id'], $client['passkey']])->fetch(PDO::FETCH_ASSOC);

if (!$peer) {
    // New Peer
    if (MEMBERSONLY) {
        $slot = pdo($pdo, "SELECT DISTINCT torrent FROM peers WHERE userid =? AND seeder=?", [$user['id'], 'no'])->rowCount();
        if ($slot >= $user['maxslots']) {
            die(response("Maximum Slot exceeded! You may only download $slot torrent at a time."));
        }
        pdo($pdo, "INSERT INTO `snatched` (`uid`, `tid`, `start_time`, `upload_time`, `completed`) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `upload_time` = ?", [$user['id'], $torrent['id'], strtotime(TT_DATE), strtotime(TT_DATE), $client['seeder'], strtotime(TT_DATE)]);
    }
    pdo($pdo, "INSERT INTO peers (connectable, torrent, peer_id, ip, passkey, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, client)VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE last_action = ?", ['yes', $torrent['id'], $client['peer_id'], $client['ip'], $client['passkey'], $client['port'], $client['uploaded'], $client['downloaded'], $client['left'], TT_DATE, TT_DATE, $client['seeder'], $user['id'], $agent, TT_DATE]);
} else {
    // Update Peer
    $elapsed = ($peer['seeder'] == 'yes') ? ANNOUNCEINTERVAL - floor(($peer['ez'] - time()) / 60) : 0;
    $upthis = max(0, $client['uploaded'] - $peer["uploaded"]);
    $downthis = max(0, $client['downloaded'] - $peer["downloaded"]);
    if (($upthis > 0 || $downthis > 0 || $elapsed > 0) && (is_int($user['id']) && $user['id'] > 0)) {
        if ($torrent["freeleech"] == 1) {
            pdo($pdo, "UPDATE users SET uploaded = uploaded + ? WHERE id=?", [$upthis, $userid]);
            pdo($pdo,  "UPDATE `snatched` SET `upload` = `upload` + '$upthis', `upload_time` = ?, `last_time` = `last_time` + '$elapsed', completed = ? WHERE `tid` = ? AND `uid` = ?", [strtotime(TT_DATE), $client['seeder'], $torrent['id'], $user['id']]);
        } else {
            pdo($pdo, "UPDATE users SET uploaded = uploaded + ?, downloaded = downloaded + ? WHERE id=?", [$upthis, $downthis, $user['id']]);
            pdo($pdo, "UPDATE `snatched` SET `upload` = `upload` + '$upthis', `download` = `download` + '$downthis', `upload_time` = ?, `last_time` = `last_time` + '$elapsed', completed = ? WHERE `tid` = ? AND `uid` = ?", [strtotime(TT_DATE), $client['seeder'], $torrent['id'], $user['id']]);
        }
    }
    pdo($pdo, "UPDATE peers SET ip = ?, passkey = ?, port = ?, uploaded = ?, downloaded = ?, to_go = ?, last_action = ?, client = ?, seeder = ? WHERE torrent = ? AND peer_id = ?", [$client['ip'], $client['passkey'], $client['port'], $client['uploaded'], $client['downloaded'], $client['left'], TT_DATE, $agent, $client['seeder'], $torrent['id'], $client['peer_id']]);
}

// Event Completed
if ($client['event'] == "completed") {
    if (MEMBERSONLY) {
        pdo($pdo, "UPDATE `snatched`  SET `completed` = ? WHERE `tid` = ? AND `uid` = ?", [1, $torrent['id'], $user['id']]);
    }
    $torrent['times_completed']++;
}

// Event Stopped
if ($client['event'] == "stopped") {
    pdo($pdo, "DELETE FROM peers  WHERE  torrent = ? AND peer_id = ?", [$torrent['id'], $client['peer_id']]);
}

// Get Peers For Response
$all_peers = pdo($pdo, "SELECT peer_id, ip, port, seeder FROM peers  WHERE torrent = ? ORDER BY RAND() LIMIT 50", [$torrent['id']])->fetchAll();
$response_array = array();
$seeders = 0;
$leechers = 0;
foreach ($all_peers as $peer) {
    if ($peer['seeder'] == 'no') {
        $leechers++;
    } else {
        $seeders++;
    }
    $response_array[] = array('ip' => $peer['ip'], 'port' => $peer['port'], 'peer_id' => $peer['peer_id']);
}

// Update Torrent
pdo($pdo, "UPDATE torrents SET last_action = ?, leechers = ?, seeders = ?, times_completed = ?, visible = ? WHERE id=?", [TT_DATE, $leechers, $seeders, $torrent['times_completed'], 'yes', $torrent['id']]);

// Show Response
die(response($response_array, $seeders, $leechers));