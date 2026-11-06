<?php
declare(strict_types=1);

/**
 * URL: /torrents/download/{torrent_id}/{key?}
 * Streams a .torrent — no theme output.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\DownloadLogic;
use Bootstrap\Libs\Torrents\Bencode; // adjust if Bencode lives elsewhere

$current_user = Auth::user();
$torrentId = (int) param(0, 0);
$key       = (string) param(1, '');

if ($torrentId < 1) {
    redirect('browse', __('MISSING_ID'), 'warning');
}

// Guests need a passkey in the URL when members-only
if (!Auth::check() && $key === '' && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$passkey = $key !== '' ? $key : (string) ($current_user->passkey ?? '');

$data = DownloadLogic::torrent($torrentId, $passkey);
$file = $data['file'] ?? '';
$name = $data['name'] ?? 'download.torrent';

if ($file === '' || !is_file($file)) {
    redirect('browse', __('NO_FILE', (string) $torrentId), 'warning');
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

if (($data['external'] ?? '') !== 'yes') {
    // Local: inject passkey into announce
    $dict = Bencode::decode((string) file_get_contents($file));
    $dict['announce'] = rtrim(URLROOT, '/') . '/announce.php?passkey=' . rawurlencode($passkey);
    unset($dict['announce-list']);
    $payload = Bencode::encode($dict);

    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
    header('Content-Type: application/x-bittorrent');
    echo $payload;
    exit;
}

header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
header('Content-Length: ' . (string) filesize($file));
header('Content-Type: application/x-bittorrent');
readfile($file);
exit;