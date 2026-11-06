<?php
declare(strict_types=1);

/**
 * URL: /torrents/download/{torrent_id}/{key?}
 * Streams .torrent — no theme.
 */

use Bootstrap\Libs\Auth;
use Bootstrap\Models\DownloadLogic;

$current_user = Auth::user();
$torrentId = (int) param(0, 0);
$key       = (string) param(1, '');

if ($torrentId < 1) {
    redirect('torrents/browse', __('MISSING_ID'), 'warning');
}

if (!Auth::check() && $key === '' && !empty(CONFIG['MEMBERSONLY'])) {
    redirect('authorise/login', __('NO_PERMISSION'), 'warning');
}

$passkey = $key !== '' ? $key : (string) ($current_user->passkey ?? '');
$data    = DownloadLogic::torrent($torrentId, $passkey);

$file = (string) ($data['file'] ?? '');
$name = (string) ($data['name'] ?? 'download.torrent');

if ($file === '' || !is_file($file)) {
    redirect('torrents/browse', __('NO_FILE', (string) $torrentId), 'warning');
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

$safeName = str_replace(['"', "\r", "\n"], '', $name);

// Prefer DownloadLogic returning ready 'payload' for local torrents
if (isset($data['payload']) && is_string($data['payload'])) {
    header('Content-Disposition: attachment; filename="' . $safeName . '"');
    header('Content-Type: application/x-bittorrent');
    echo $data['payload'];
    exit;
}

header('Content-Disposition: attachment; filename="' . $safeName . '"');
header('Content-Length: ' . (string) filesize($file));
header('Content-Type: application/x-bittorrent');
readfile($file);
exit;